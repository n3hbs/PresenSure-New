<?php

declare(strict_types=1);

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Period;
use App\Models\Program;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    $adminRole = Role::where('role_name', 'administrator')->first();
    $studentRole = Role::where('role_name', 'student')->first();

    $this->admin = User::create([
        'user_id' => 'ADMIN-TEST-001',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->admin->user_id,
        'role_id' => $adminRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->student = User::create([
        'user_id' => 'STUDENT-TEST-001',
        'first_name' => 'Student',
        'last_name' => 'User',
        'sex' => 'female',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->student->user_id,
        'role_id' => $studentRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->schoolYear = SchoolYear::create([
        'school_year_start' => now()->startOfYear()->toDateString(),
        'school_year_end' => now()->addYear()->endOfYear()->toDateString(),
    ]);
});

test('unauthenticated user cannot access semesters list', function () {
    $response = $this->getJson('/api/v1/semesters');

    $response->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $response = $this->getJson('/api/v1/semesters');
    $response->assertStatus(403);

    $storeResponse = $this->postJson('/api/v1/semesters', [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);
    $storeResponse->assertStatus(403);
});

test('admin can fetch semesters list with active period (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->subMonth()->toDateString(),
        'semester_end' => now()->addMonths(3)->toDateString(),
    ]);

    Period::create([
        'semester_id' => $semester->semester_id,
        'name' => 'prelim',
        'period_start' => now()->subDays(5)->toDateString(),
        'period_end' => now()->addDays(20)->toDateString(),
    ]);

    $response = $this->getJson('/api/v1/semesters');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'semester_id',
                    'term',
                    'is_active',
                    'active_period' => [
                        'period_id',
                        'name',
                        'period_start',
                        'period_end',
                    ],
                    'periods',
                    'school_year',
                ],
            ],
        ]);

    expect($response->json('data.0.active_period.name'))->toBe('prelim');
});

test('admin can create semester with periods in stepper (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $start = now()->toDateString();
    $end = now()->addMonths(4)->toDateString();

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => $start,
        'semester_end' => $end,
        'remarks' => 'Opening semester with prelim and midterm configured',
        'periods' => [
            [
                'name' => 'prelim',
                'period_start' => $start,
                'period_end' => now()->addMonth()->toDateString(),
            ],
            [
                'name' => 'midterm',
                'period_start' => now()->addMonth()->toDateString(),
                'period_end' => now()->addMonths(2)->toDateString(),
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/semesters', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.term', 'First Semester');

    $this->assertDatabaseHas('semesters', [
        'term' => 'First Semester',
    ]);

    $this->assertDatabaseHas('periods', [
        'name' => 'prelim',
    ]);
    $this->assertDatabaseHas('periods', [
        'name' => 'midterm',
    ]);
});

test('validation fails (422) when end date is before start date', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->addMonths(4)->toDateString(),
        'semester_end' => now()->addMonths(1)->toDateString(),
    ];

    $response = $this->postJson('/api/v1/semesters', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['semester_end']]]);
});

test('validation fails (422) when semester end date is already in the past', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->subMonths(6)->toDateString(),
        'semester_end' => now()->subMonths(2)->toDateString(), // Already finished
    ];

    $response = $this->postJson('/api/v1/semesters', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['semester_end']]]);
});

test('validation fails (422) for duplicate semester term in same school year', function () {
    Sanctum::actingAs($this->admin);

    Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(3)->toDateString(),
    ]);

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester', // Duplicate in same school year
        'semester_start' => now()->addMonths(4)->toDateString(),
        'semester_end' => now()->addMonths(7)->toDateString(),
    ];

    $response = $this->postJson('/api/v1/semesters', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['term']]]);
});

test('validation fails (422) for overlapping semester dates in same school year', function () {
    Sanctum::actingAs($this->admin);

    Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(3)->toDateString(),
    ]);

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->addMonths(2)->toDateString(), // Overlaps with First Semester
        'semester_end' => now()->addMonths(6)->toDateString(),
    ];

    $response = $this->postJson('/api/v1/semesters', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['semester_start']]]);
});

test('active semester is determined by date alignment with current date', function () {
    Sanctum::actingAs($this->admin);

    $activeByDate = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->subMonth()->toDateString(),
        'semester_end' => now()->addMonths(3)->toDateString(), // Contains today!
    ]);

    $futureSemester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->addMonths(4)->toDateString(),
        'semester_end' => now()->addMonths(8)->toDateString(),
    ]);

    $response = $this->getJson('/api/semester/active');

    $response->assertStatus(200)
        ->assertJsonPath('data.semester_id', $activeByDate->semester_id)
        ->assertJsonPath('data.term', 'First Semester')
        ->assertJsonPath('data.is_active', true);

    expect($futureSemester->is_active)->toBeFalse();
});

test('semester cannot be deleted when it has dependent records', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);

    $department = Department::create([
        'department_name' => 'College of Computer Studies',
        'department_code' => 'CCS',
    ]);

    $program = Program::create([
        'department_id' => $department->department_id,
        'program_name' => 'BS in Information Technology',
        'program_code' => 'BSIT',
    ]);

    Student::create([
        'user_id' => $this->student->user_id,
        'semester_id' => $semester->semester_id,
        'program_id' => $program->program_id,
        'year' => 'First Year',
        'block' => 'A',
        'status' => 'Active',
    ]);

    $response = $this->deleteJson("/api/v1/semesters/{$semester->semester_id}");

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['semester']]]);

    $this->assertDatabaseHas('semesters', [
        'semester_id' => $semester->semester_id,
        'deleted_at' => null,
    ]);
});

test('admin can delete semester without dependencies (204 No Content)', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Summer',
        'semester_start' => now()->addMonths(5)->toDateString(),
        'semester_end' => now()->addMonths(7)->toDateString(),
    ]);

    $response = $this->deleteJson("/api/v1/semesters/{$semester->semester_id}");

    $response->assertStatus(204);

    $this->assertSoftDeleted('semesters', [
        'semester_id' => $semester->semester_id,
    ]);
});

test('admin can update semester with synchronized periods (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);

    Period::create([
        'semester_id' => $semester->semester_id,
        'name' => 'prelim',
        'period_start' => now()->toDateString(),
        'period_end' => now()->addDays(20)->toDateString(),
    ]);

    $newStart = now()->toDateString();
    $newEnd = now()->addMonths(5)->toDateString();

    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => $newStart,
        'semester_end' => $newEnd,
        'remarks' => 'Extended semester and added midterm',
        'periods' => [
            [
                'name' => 'prelim',
                'period_start' => $newStart,
                'period_end' => now()->addDays(25)->toDateString(),
            ],
            [
                'name' => 'midterm',
                'period_start' => now()->addDays(26)->toDateString(),
                'period_end' => now()->addDays(55)->toDateString(),
            ],
        ],
    ];

    $response = $this->putJson("/api/v1/semesters/{$semester->semester_id}", $payload);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.remarks', 'Extended semester and added midterm');

    $this->assertDatabaseHas('periods', [
        'semester_id' => $semester->semester_id,
        'name' => 'midterm',
    ]);
});

test('validation fails (422) when updating semester by removing period with attendance sessions', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);

    $period = Period::create([
        'semester_id' => $semester->semester_id,
        'name' => 'prelim',
        'period_start' => now()->toDateString(),
        'period_end' => now()->addDays(20)->toDateString(),
    ]);

    // Create schedule, instructor, and attendance session linked to this period
    $courseId = DB::table('courses')->insertGetId([
        'subject_code' => 'IT101',
        'name' => 'Intro to IT',
    ], 'course_id');

    $courseBlockId = DB::table('course_blocks')->insertGetId([
        'course_id' => $courseId,
        'semester_id' => $semester->semester_id,
        'block_code' => 'BSIT-1A',
    ], 'course_block_id');

    $buildingId = DB::table('buildings')->insertGetId([
        'code' => 'MAIN',
        'name' => 'Main Building',
    ], 'building_id');

    $roomId = DB::table('rooms')->insertGetId([
        'building_id' => $buildingId,
        'name' => 'Room 101',
        'floor_no' => 1,
        'capacity' => 40,
        'status' => 'Active',
    ], 'room_id');

    $scheduleId = DB::table('schedules')->insertGetId([
        'course_block_id' => $courseBlockId,
        'room_id' => $roomId,
        'semester_id' => $semester->semester_id,
        'block_code' => 'BSIT-1A',
        'schedule_type' => 'lecture',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ], 'schedule_id');

    $instructorUser = User::create([
        'user_id' => 'INST-TEST-999',
        'first_name' => 'Prof',
        'last_name' => 'Oak',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);

    DB::table('attendance_sessions')->insert([
        'session_code' => 'SESS-001',
        'schedule_id' => $scheduleId,
        'period_id' => $period->period_id,
        'instructor_id' => $instructorUser->user_id,
        'verification_mode' => 'ble_face',
        'requires_periodic_verification' => false,
        'status' => 'ended',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Try to update semester with empty periods (removing prelim)
    $payload = [
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
        'periods' => [],
    ];

    $response = $this->putJson("/api/v1/semesters/{$semester->semester_id}", $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['periods']]]);
});

test('authenticated user can view semester edit web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/semesters/edit');

    $response->assertStatus(200);
});

test('admin can fetch archived semesters list (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);

    $semester->delete(); // Soft delete

    $response = $this->getJson('/api/v1/semesters/archives');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => ['semester_id', 'term', 'school_year', 'periods'],
            ],
        ]);

    expect($response->json('data.0.semester_id'))->toBe($semester->semester_id);
});

test('admin can restore archived semester with periods preserved (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
    ]);

    $period = Period::create([
        'semester_id' => $semester->semester_id,
        'name' => 'prelim',
        'period_start' => now()->toDateString(),
        'period_end' => now()->addDays(20)->toDateString(),
    ]);

    $semester->delete(); // Soft delete

    expect(Semester::find($semester->semester_id))->toBeNull();
    expect(Semester::withTrashed()->find($semester->semester_id))->not->toBeNull();

    $response = $this->postJson("/api/v1/semesters/{$semester->semester_id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.semester_id', $semester->semester_id);

    // Active semester query finds it again
    expect(Semester::find($semester->semester_id))->not->toBeNull();
    // Periods are still attached
    expect($semester->periods()->count())->toBe(1);
});

test('authenticated user can view semester archives web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/semesters/archives');

    $response->assertStatus(200);
});

test('authenticated user can view semester details web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/semesters/semester-details');

    $response->assertStatus(200);
});
