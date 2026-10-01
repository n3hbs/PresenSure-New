<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{
    /**
     * Get all active courses with counts.
     *
     * @return Collection<int, Course>
     */
    public function getAll(): Collection
    {
        $activeSemester = Semester::where('is_active', true)->first();

        $query = Course::withCount('courseBlocks');

        if ($activeSemester) {
            $query->withCount([
                'courseBlocks as active_semester_blocks_count' => function ($q) use ($activeSemester) {
                    $q->where('semester_id', $activeSemester->semester_id);
                },
            ]);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get all archived (soft-deleted) courses.
     *
     * @return Collection<int, Course>
     */
    public function getArchived(): Collection
    {
        return Course::onlyTrashed()
            ->withCount('courseBlocks')
            ->orderByDesc('deleted_at')
            ->get();
    }

    /**
     * Find a course by ID with optional active semester course blocks.
     */
    public function findById(int $id, bool $withActiveSemesterBlocks = true): ?Course
    {
        $course = Course::withCount('courseBlocks')
            ->where('course_id', $id)
            ->first();

        if (! $course) {
            return null;
        }

        if ($withActiveSemesterBlocks) {
            $activeSemester = Semester::with('schoolYear')
                ->where('is_active', true)
                ->first();

            $course->active_semester = $activeSemester;

            if ($activeSemester) {
                $blocks = CourseBlock::where('course_id', $course->course_id)
                    ->where('semester_id', $activeSemester->semester_id)
                    ->withCount([
                        'userCourseBlocks as students_count',
                        'schedules as schedules_count',
                    ])
                    ->with('semester')
                    ->orderBy('block_code')
                    ->get();

                $course->setRelation('courseBlocks', $blocks);
            } else {
                $course->setRelation('courseBlocks', new Collection);
            }
        }

        return $course;
    }

    /**
     * Create a new course with optional course blocks.
     */
    public function create(array $data): Course
    {
        $blocksData = $data['course_blocks'] ?? [];
        unset($data['course_blocks']);

        $course = Course::create($data);

        if (! empty($blocksData) && is_array($blocksData)) {
            $activeSemester = Semester::where('is_active', true)->first();

            foreach ($blocksData as $block) {
                $semesterId = $block['semester_id'] ?? $activeSemester?->semester_id;
                if ($semesterId && ! empty($block['block_code'])) {
                    CourseBlock::firstOrCreate([
                        'course_id' => $course->course_id,
                        'semester_id' => (int) $semesterId,
                        'block_code' => strtoupper(trim((string) $block['block_code'])),
                    ]);
                }
            }
        }

        return $this->findById((int) $course->course_id);
    }

    /**
     * Update an existing course and its course blocks.
     */
    public function update(int $id, array $data): Course
    {
        $course = Course::findOrFail($id);

        $blocksData = $data['course_blocks'] ?? null;
        unset($data['course_blocks']);

        $course->update($data);

        if ($blocksData !== null && is_array($blocksData)) {
            $activeSemester = Semester::where('is_active', true)->first();

            foreach ($blocksData as $block) {
                $semesterId = $block['semester_id'] ?? $activeSemester?->semester_id;
                if ($semesterId && ! empty($block['block_code'])) {
                    if (! empty($block['course_block_id'])) {
                        $existing = CourseBlock::where('course_id', $course->course_id)
                            ->where('course_block_id', $block['course_block_id'])
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'block_code' => strtoupper(trim((string) $block['block_code'])),
                                'semester_id' => (int) $semesterId,
                            ]);
                        }
                    } else {
                        CourseBlock::firstOrCreate([
                            'course_id' => $course->course_id,
                            'semester_id' => (int) $semesterId,
                            'block_code' => strtoupper(trim((string) $block['block_code'])),
                        ]);
                    }
                }
            }
        }

        return $this->findById((int) $course->course_id);
    }

    /**
     * Soft-delete a course.
     */
    public function delete(int $id): bool
    {
        $course = Course::findOrFail($id);

        return (bool) $course->delete();
    }

    /**
     * Restore an archived course.
     */
    public function restore(int $id): Course
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return $this->findById((int) $course->course_id);
    }

    /**
     * Check if a course has dependencies preventing deletion.
     *
     * @return array<int, string>
     */
    public function hasDependencies(int $id): array
    {
        $course = Course::find($id);
        if (! $course) {
            return [];
        }

        $reasons = [];
        $blockIds = CourseBlock::where('course_id', $id)->pluck('course_block_id');

        if ($blockIds->isNotEmpty()) {
            $scheduleCount = Schedule::whereIn('course_block_id', $blockIds)->count();
            if ($scheduleCount > 0) {
                $reasons[] = "Course has {$scheduleCount} scheduled class(es).";
            }

            $userCount = UserCourseBlock::whereIn('course_block_id', $blockIds)->count();
            if ($userCount > 0) {
                $reasons[] = "Course blocks have {$userCount} assigned student(s) or instructor(s).";
            }
        }

        return $reasons;
    }

    /**
     * Create a single course block (legacy compatibility).
     */
    public function createCourseBlock(array $data): CourseBlock
    {
        return CourseBlock::firstOrCreate($data);
    }

    /**
     * Assign a user to a course block (legacy compatibility).
     */
    public function assignUserToCourseBlock(array $data): UserCourseBlock
    {
        return UserCourseBlock::firstOrCreate($data);
    }
}
