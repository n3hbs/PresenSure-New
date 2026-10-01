<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserCourseBlock;
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
        'user_id' => 'ADMIN-COURSE-001',
        'first_name' => 'Admin',
        'last_name' => 'Course',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->admin->user_id,
        'role_id' => $adminRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->student = User::create([
        'user_id' => 'STUDENT-COURSE-001',
        'first_name' => 'Student',
        'last_name' => 'Course',
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

    $this->activeSemester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->subMonth()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
        'is_active' => true,
    ]);

    $this->inactiveSemester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'Second Semester',
        'semester_start' => now()->addMonths(5)->toDateString(),
        'semester_end' => now()->addMonths(9)->toDateString(),
        'is_active' => false,
    ]);
});

test('unauthenticated user cannot access courses management', function () {
    $response = $this->getJson('/api/v1/courses');

    $response->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $response = $this->getJson('/api/v1/courses');
    $response->assertStatus(403);

    $createResponse = $this->postJson('/api/v1/courses', [
        'subject_code' => 'CS101',
        'name' => 'Intro to Computer Science',
    ]);
    $createResponse->assertStatus(403);
});

test('admin can fetch courses list (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    Course::create([
        'subject_code' => 'CS101',
        'name' => 'Intro to Computer Science',
        'description' => 'Fundamental concepts of CS.',
    ]);

    $response = $this->getJson('/api/v1/courses');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'course_id',
                    'subject_code',
                    'name',
                    'description',
                    'course_blocks_count',
                ],
            ],
        ]);
});

test('admin can create course with optional blocks (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'subject_code' => 'IT102',
        'name' => 'Data Structures and Algorithms',
        'description' => 'Study of core data structures.',
        'course_blocks' => [
            [
                'block_code' => 'BSIT-2A',
                'semester_id' => $this->activeSemester->semester_id,
            ],
            [
                'block_code' => 'BSIT-2B',
                'semester_id' => $this->activeSemester->semester_id,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/courses', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.subject_code', 'IT102')
        ->assertJsonPath('data.name', 'Data Structures and Algorithms')
        ->assertJsonPath('data.description', 'Study of core data structures.');

    $course = Course::where('subject_code', 'IT102')->first();
    expect($course)->not->toBeNull();
    expect($course->courseBlocks()->count())->toBe(2);
});

test('validation fails (422) for duplicate subject code', function () {
    Sanctum::actingAs($this->admin);

    Course::create([
        'subject_code' => 'DUP101',
        'name' => 'Original Course',
    ]);

    $response = $this->postJson('/api/v1/courses', [
        'subject_code' => 'DUP101',
        'name' => 'Duplicate Course',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['subject_code']]]);
});

test('admin can fetch course details with created course blocks in active semester (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'CS301',
        'name' => 'Operating Systems',
        'description' => 'Processes, memory, and file systems.',
    ]);

    // Block in active semester
    $activeBlock = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-3A',
    ]);

    // Block in inactive semester
    CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->inactiveSemester->semester_id,
        'block_code' => 'BSCS-3B-FUTURE',
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->course_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.course_id', $course->course_id)
        ->assertJsonPath('data.subject_code', 'CS301')
        ->assertJsonPath('data.active_semester.semester_id', $this->activeSemester->semester_id)
        ->assertJsonPath('data.course_blocks_count', 2)
        ->assertJsonPath('data.active_semester_blocks_count', 1)
        ->assertJsonCount(1, 'data.course_blocks')
        ->assertJsonPath('data.course_blocks.0.course_block_id', $activeBlock->course_block_id)
        ->assertJsonPath('data.course_blocks.0.block_code', 'BSCS-3A');
});

test('course details returns empty course blocks when no semester is active (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $this->activeSemester->update(['is_active' => false]);

    $course = Course::create([
        'subject_code' => 'MATH101',
        'name' => 'College Algebra',
    ]);

    CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->inactiveSemester->semester_id,
        'block_code' => 'MATH-1A',
    ]);

    $response = $this->getJson("/api/v1/courses/{$course->course_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.active_semester', null)
        ->assertJsonPath('data.active_semester_blocks_count', 0)
        ->assertJsonCount(0, 'data.course_blocks')
        ->assertJsonPath('data.course_blocks_count', 1);
});

test('admin can update course details and synchronize blocks (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'CS201',
        'name' => 'Discrete Math',
    ]);

    $block = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-2A',
    ]);

    $payload = [
        'subject_code' => 'CS201-ADV',
        'name' => 'Discrete Structures & Logic',
        'description' => 'Updated syllabus description.',
        'course_blocks' => [
            [
                'course_block_id' => $block->course_block_id,
                'block_code' => 'BSCS-2A-MODIFIED',
                'semester_id' => $this->activeSemester->semester_id,
            ],
            [
                'block_code' => 'BSCS-2B-NEW',
                'semester_id' => $this->activeSemester->semester_id,
            ],
        ],
    ];

    $response = $this->putJson("/api/v1/courses/{$course->course_id}", $payload);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.subject_code', 'CS201-ADV')
        ->assertJsonPath('data.name', 'Discrete Structures & Logic');

    $block->refresh();
    expect($block->block_code)->toBe('BSCS-2A-MODIFIED');
    expect($course->courseBlocks()->count())->toBe(2);
});

test('course cannot be deleted when it has dependent class schedules or enrolled students (422)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'PHYS101',
        'name' => 'Physics I',
    ]);

    $block = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'ENG-1A',
    ]);

    $building = Building::create([
        'code' => 'SCI-01',
        'name' => 'Science Hall',
    ]);

    $room = Room::create([
        'building_id' => $building->building_id,
        'name' => 'Lab 101',
        'floor_no' => 1,
        'status' => 'Active',
    ]);

    Schedule::create([
        'course_block_id' => $block->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'ENG-1A',
        'schedule_type' => 'laboratory',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    $response = $this->deleteJson("/api/v1/courses/{$course->course_id}");

    $response->assertStatus(422)
        ->assertJsonPath('success', false);

    expect(Course::find($course->course_id))->not->toBeNull();
});

test('admin can delete course without dependencies (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'FREE101',
        'name' => 'Elective Course',
    ]);

    $response = $this->deleteJson("/api/v1/courses/{$course->course_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    expect(Course::find($course->course_id))->toBeNull();
    expect(Course::withTrashed()->find($course->course_id))->not->toBeNull();
});

test('admin can fetch archived courses list (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'ARCH101',
        'name' => 'Archived Course',
    ]);
    $course->delete();

    $response = $this->getJson('/api/v1/courses/archives');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.course_id', $course->course_id);
});

test('admin can restore archived course (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'REST101',
        'name' => 'Restore Course',
    ]);
    $course->delete();

    expect(Course::find($course->course_id))->toBeNull();

    $response = $this->postJson("/api/v1/courses/{$course->course_id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.course_id', $course->course_id);

    expect(Course::find($course->course_id))->not->toBeNull();
});

test('authenticated user can view course web pages via SPA fallback (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $course = Course::create([
        'subject_code' => 'WEB101',
        'name' => 'Web App Development',
    ]);

    $pages = [
        '/courses',
        '/courses/create',
        "/courses/edit?course_id={$course->course_id}",
        "/courses/course-details?course_id={$course->course_id}",
        '/courses/archives',
    ];

    foreach ($pages as $url) {
        $response = $this->get($url);
        $response->assertStatus(200);
    }
});

test('legacy endpoints continue to work as expected (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    // 1. POST /api/course
    $courseRes = $this->postJson('/api/course', [
        'subject_code' => 'LEG101',
        'name' => 'Legacy Course',
    ]);
    $courseRes->assertStatus(201);

    $course = Course::where('subject_code', 'LEG101')->first();
    expect($course)->not->toBeNull();

    // 2. POST /api/course-block
    $blockRes = $this->postJson('/api/course-block', [
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'LEG-1A',
    ]);
    $blockRes->assertStatus(201);

    $block = CourseBlock::where('block_code', 'LEG-1A')->first();
    expect($block)->not->toBeNull();

    // 3. POST /api/course-block/assign-users
    $assignRes = $this->postJson('/api/course-block/assign-users', [
        'course_block_id' => $block->course_block_id,
        'user_ids' => [$this->student->user_id],
    ]);
    $assignRes->assertStatus(201);

    expect(UserCourseBlock::where('user_id', $this->student->user_id)->count())->toBe(1);
});
