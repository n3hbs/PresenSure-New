<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Semester;
use App\Repositories\StudentRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentBulkImport implements ToCollection
{
    public array $toEnroll = [];

    public array $alreadyEnrolled = [];

    public array $invalid = [];

    protected ?Semester $activeSemester;

    protected StudentRepository $studentRepository;

    protected Collection $programs;

    protected ?int $headerRowIndex = null;

    protected array $headerMap = [];

    public function __construct(Semester $activeSemester, StudentRepository $studentRepository)
    {
        $this->activeSemester = $activeSemester;
        $this->studentRepository = $studentRepository;
        $this->programs = Program::all()->keyBy(function ($item) {
            return strtoupper(trim($item->program_code));
        });
    }

    public function collection(Collection $rows)
    {
        $this->toEnroll = [];
        $this->alreadyEnrolled = [];
        $this->invalid = [];
        $this->headerRowIndex = null;
        $this->headerMap = [];

        if ($rows->isEmpty()) {
            return;
        }

        // 1. Scan for header row
        foreach ($rows as $index => $row) {
            $rowArray = $row->toArray();

            $normalized = array_map(function ($cell) {
                if ($cell === null) {
                    return '';
                }
                $clean = str_replace(['.', '-', ' '], '_', Str::lower(trim((string) $cell)));

                return preg_replace('/_+/', '_', $clean);
            }, $rowArray);

            $idIndex = null;
            $nameIndex = null;

            foreach ($normalized as $pos => $val) {
                if (in_array($val, ['student_no', 'student_id', 'id_number', 'id', 'user_id'], true)) {
                    $idIndex = $pos;
                }
                if (in_array($val, ['full_name', 'name', 'fullname', 'student_name'], true)) {
                    $nameIndex = $pos;
                }
            }

            if ($idIndex !== null && $nameIndex !== null) {
                $this->headerRowIndex = $index;

                foreach ($normalized as $pos => $val) {
                    if (empty($val)) {
                        continue;
                    }

                    if (in_array($val, ['student_no', 'student_id', 'id_number', 'id', 'user_id'], true) && ! isset($this->headerMap['student_no'])) {
                        $this->headerMap['student_no'] = $pos;
                    } elseif (in_array($val, ['full_name', 'name', 'fullname', 'student_name'], true) && ! isset($this->headerMap['full_name'])) {
                        $this->headerMap['full_name'] = $pos;
                    } elseif (in_array($val, ['gender', 'sex'], true) && ! isset($this->headerMap['gender'])) {
                        $this->headerMap['gender'] = $pos;
                    } elseif (in_array($val, ['program', 'course', 'program_code'], true) && ! isset($this->headerMap['program'])) {
                        $this->headerMap['program'] = $pos;
                    } elseif (in_array($val, ['year_level', 'year', 'level'], true) && ! isset($this->headerMap['year_level'])) {
                        $this->headerMap['year_level'] = $pos;
                    } elseif (in_array($val, ['block', 'section'], true) && ! isset($this->headerMap['block'])) {
                        $this->headerMap['block'] = $pos;
                    }
                }
                break;
            }
        }

        if ($this->headerRowIndex === null) {
            throw new \Exception('Invalid spreadsheet format: could not locate header columns "Student No" and "Full Name".');
        }

        // 2. Process data rows
        $dataRows = $rows->slice($this->headerRowIndex + 1);

        foreach ($dataRows as $rowIndex => $row) {
            $studentNo = $this->getValue($row, 'student_no');
            $fullName = $this->getValue($row, 'full_name');
            $gender = $this->getValue($row, 'gender');
            $programCode = $this->getValue($row, 'program');
            $yearLevel = $this->getValue($row, 'year_level');
            $block = $this->getValue($row, 'block');

            // Skip completely empty rows
            if (empty($studentNo) && empty($fullName) && empty($programCode)) {
                continue;
            }

            $actualRowNumber = $rowIndex + 1;

            // Validate mandatory fields
            if (empty($studentNo) || empty($fullName)) {
                $this->invalid[] = [
                    'row' => $actualRowNumber,
                    'user_id' => $studentNo ?: 'N/A',
                    'full_name' => $fullName ?: 'N/A',
                    'reason' => 'Missing student ID or full name.',
                ];

                continue;
            }

            // Parse Name (Lastname, Firstname Middle Initial Suffix)
            $parsedName = $this->parseFullName($fullName);

            // Normalize gender
            $sex = 'Male';
            $genderLower = strtolower($gender);
            if (str_starts_with($genderLower, 'f')) {
                $sex = 'Female';
            } elseif (str_starts_with($genderLower, 'm')) {
                $sex = 'Male';
            }

            // Normalize year level
            $normalizedYear = $this->normalizeYearLevel($yearLevel);

            // Normalize block
            $normalizedBlock = trim(strtoupper($block ?: 'A'));

            // Resolve program
            $programKey = strtoupper(trim($programCode));
            $matchedProgram = $this->programs->get($programKey);

            if (! $matchedProgram) {
                // If only one program exists in system, or match partial
                foreach ($this->programs as $key => $prog) {
                    if (str_contains($programKey, $key) || str_contains($key, $programKey)) {
                        $matchedProgram = $prog;
                        break;
                    }
                }
            }

            if (! $matchedProgram) {
                $this->invalid[] = [
                    'row' => $actualRowNumber,
                    'user_id' => $studentNo,
                    'full_name' => $fullName,
                    'reason' => "Program '{$programCode}' is not recognized in the system.",
                ];

                continue;
            }

            $studentData = [
                'row' => $actualRowNumber,
                'user_id' => $studentNo,
                'first_name' => $parsedName['first_name'],
                'middle_initial' => $parsedName['middle_initial'],
                'last_name' => $parsedName['last_name'],
                'suffix' => $parsedName['suffix'],
                'full_name' => $parsedName['formatted_full_name'],
                'sex' => $sex,
                'program_id' => $matchedProgram->program_id,
                'program_code' => $matchedProgram->program_code,
                'program_name' => $matchedProgram->program_name,
                'year' => $normalizedYear,
                'block' => $normalizedBlock,
            ];

            // Check if student is already enrolled in active semester
            $isAlreadyEnrolled = $this->studentRepository->isEnrolled($studentNo, $this->activeSemester->semester_id);

            if ($isAlreadyEnrolled) {
                $this->alreadyEnrolled[] = $studentData;
            } else {
                $this->toEnroll[] = $studentData;
            }
        }
    }

    protected function getValue(array|\ArrayAccess $row, string $key): string
    {
        if (! isset($this->headerMap[$key])) {
            return '';
        }
        $index = $this->headerMap[$key];

        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    protected function parseFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        $lastName = '';
        $firstName = '';
        $middleInitial = null;
        $suffix = null;

        $suffixes = ['JR.', 'JR', 'SR.', 'SR', 'II', 'III', 'IV', 'V'];

        if (str_contains($fullName, ',')) {
            [$last, $rest] = explode(',', $fullName, 2);
            $lastName = trim($last);
            $names = preg_split('/\s+/', trim($rest), -1, PREG_SPLIT_NO_EMPTY);

            // Check suffix at end
            if (! empty($names)) {
                $lastTokenUpper = strtoupper(end($names));
                if (in_array($lastTokenUpper, $suffixes, true)) {
                    $suffix = array_pop($names);
                }
            }

            // Check middle initial at end
            if (! empty($names)) {
                $lastToken = end($names);
                if (preg_match('/^[a-zA-Z]\.?$/', $lastToken)) {
                    $middleInitial = strtoupper(substr($lastToken, 0, 1));
                    array_pop($names);
                }
            }

            $firstName = implode(' ', $names);
        } else {
            // Fallback: First M. Last
            $names = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
            if (count($names) > 1) {
                // Check suffix
                $lastTokenUpper = strtoupper(end($names));
                if (in_array($lastTokenUpper, $suffixes, true)) {
                    $suffix = array_pop($names);
                }

                $lastName = array_pop($names);

                // Check middle initial
                if (! empty($names)) {
                    $lastToken = end($names);
                    if (preg_match('/^[a-zA-Z]\.?$/', $lastToken)) {
                        $middleInitial = strtoupper(substr($lastToken, 0, 1));
                        array_pop($names);
                    }
                }

                $firstName = implode(' ', $names);
            } else {
                $firstName = $fullName;
                $lastName = '';
            }
        }

        $formatted = trim($lastName.($firstName ? ", {$firstName}" : '').($middleInitial ? " {$middleInitial}." : '').($suffix ? " {$suffix}" : ''));

        return [
            'first_name' => $firstName ?: $fullName,
            'last_name' => $lastName ?: $fullName,
            'middle_initial' => $middleInitial ?: null,
            'suffix' => $suffix,
            'formatted_full_name' => $formatted ?: $fullName,
        ];
    }

    protected function normalizeYearLevel(string $year): string
    {
        $yearClean = strtolower(trim($year));

        if (str_contains($yearClean, '1') || str_contains($yearClean, 'first')) {
            return 'First Year';
        }
        if (str_contains($yearClean, '2') || str_contains($yearClean, 'second')) {
            return 'Second Year';
        }
        if (str_contains($yearClean, '3') || str_contains($yearClean, 'third')) {
            return 'Third Year';
        }
        if (str_contains($yearClean, '4') || str_contains($yearClean, 'fourth')) {
            return 'Fourth Year';
        }

        return $year ?: 'First Year';
    }

    public function getData(): array
    {
        return [
            'to_enroll' => $this->toEnroll,
            'already_enrolled' => $this->alreadyEnrolled,
            'invalid' => $this->invalid,
            'summary' => [
                'to_enroll_count' => count($this->toEnroll),
                'already_enrolled_count' => count($this->alreadyEnrolled),
                'invalid_count' => count($this->invalid),
                'total_rows' => count($this->toEnroll) + count($this->alreadyEnrolled) + count($this->invalid),
            ],
        ];
    }
}
