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
    $instructorRole = Role::where('role_name', 'instructor')->first();

    $this->admin = User::create([
        'user_id' => 'ADMIN-CB-001',
        'first_name' => 'Admin',
        'last_name' => 'Block',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->admin->user_id,
        'role_id' => $adminRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->student = User::create([
        'user_id' => 'STUDENT-CB-001',
        'first_name' => 'Student',
        'last_name' => 'Block',
        'sex' => 'female',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->student->user_id,
        'role_id' => $studentRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->instructor = User::create([
        'user_id' => 'INST-CB-001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->instructor->user_id,
        'role_id' => $instructorRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->schoolYear = SchoolYear::create([
        'school_year_start' => now()->startOfYear()->toDateString(),
        'school_year_end' => now()->addYear()->endOfYear()->toDateString(),
    ]);

    $this->semester = Semester::create([
        'school_year_id' => $this->schoolYear->school_year_id,
        'term' => 'First Semester',
        'semester_start' => now()->subMonth()->toDateString(),
        'semester_end' => now()->addMonths(4)->toDateString(),
        'is_active' => true,
    ]);

    $this->course = Course::create([
        'subject_code' => 'CS101',
        'name' => 'Introduction to Computing',
    ]);

    $this->courseBlock = CourseBlock::create([
        'course_id' => $this->course->course_id,
        'semester_id' => $this->semester->semester_id,
        'block_code' => 'CS101-A',
    ]);
});

test('unauthenticated user cannot access course block endpoints', function () {
    $this->putJson("/api/course-block/{$this->courseBlock->course_block_id}", ['block_code' => 'CS101-B'])
        ->assertStatus(401);

    $this->deleteJson("/api/course-block/{$this->courseBlock->course_block_id}")
        ->assertStatus(401);

    $this->getJson('/api/course-blocks/archives')
        ->assertStatus(401);

    $this->postJson("/api/course-block/{$this->courseBlock->course_block_id}/restore")
        ->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $this->putJson("/api/course-block/{$this->courseBlock->course_block_id}", ['block_code' => 'CS101-B'])
        ->assertStatus(403);

    $this->deleteJson("/api/course-block/{$this->courseBlock->course_block_id}")
        ->assertStatus(403);

    $this->getJson('/api/course-blocks/archives')
        ->assertStatus(403);

    $this->postJson("/api/course-block/{$this->courseBlock->course_block_id}/restore")
        ->assertStatus(403);
});

test('admin can update block code to a valid unique name via singular and plural routes', function () {
    Sanctum::actingAs($this->admin);

    // Singular PUT /api/course-block/{id}
    $response = $this->putJson("/api/course-block/{$this->courseBlock->course_block_id}", [
        'block_code' => 'CS101-B',
        'instructor_id' => $this->instructor->user_id,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Course block updated successfully.',
            'data' => [
                'course_block_id' => $this->courseBlock->course_block_id,
                'block_code' => 'CS101-B',
                'instructor_id' => $this->instructor->user_id,
            ],
        ]);

    $this->assertDatabaseHas('course_blocks', [
        'course_block_id' => $this->courseBlock->course_block_id,
        'block_code' => 'CS101-B',
        'instructor_id' => $this->instructor->user_id,
    ]);

    // Check user_course_blocks sync
    $this->assertDatabaseHas('user_course_blocks', [
        'course_block_id' => $this->courseBlock->course_block_id,
        'user_id' => $this->instructor->user_id,
    ]);

    // Plural PUT /api/course-blocks/{id}
    $responsePlural = $this->putJson("/api/course-blocks/{$this->courseBlock->course_block_id}", [
        'block_code' => 'CS101-C',
    ]);

    $responsePlural->assertStatus(200)
        ->assertJson([
            'message' => 'Course block updated successfully.',
            'data' => [
                'course_block_id' => $this->courseBlock->course_block_id,
                'block_code' => 'CS101-C',
            ],
        ]);
});

test('validation fails (422) when editing block code to duplicate name in the same semester', function () {
    Sanctum::actingAs($this->admin);

    // Create a second block
    $secondBlock = CourseBlock::create([
        'course_id' => $this->course->course_id,
        'semester_id' => $this->semester->semester_id,
        'block_code' => 'CS101-B',
    ]);

    // Attempt to rename secondBlock to CS101-A
    $response = $this->putJson("/api/course-block/{$secondBlock->course_block_id}", [
        'block_code' => 'CS101-A',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.errors.block_code.0', 'The block code is already in use by another section in this semester.');
});

test('validation fails (422) when instructor does not exist', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->putJson("/api/course-block/{$this->courseBlock->course_block_id}", [
        'block_code' => 'CS101-A',
        'instructor_id' => 'NON-EXISTENT-ID',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.errors.instructor_id.0', 'The selected instructor does not exist.');
});

test('admin can archive block with 0 schedules and 0 users (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->deleteJson("/api/course-block/{$this->courseBlock->course_block_id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => "Course block '{$this->courseBlock->block_code}' has been archived successfully.",
        ]);

    $this->assertSoftDeleted('course_blocks', [
        'course_block_id' => $this->courseBlock->course_block_id,
    ]);
});

test('cannot archive block with active class schedules (422 Unprocessable Entity)', function () {
    Sanctum::actingAs($this->admin);

    $building = Building::create(['code' => 'TH', 'name' => 'Tech Hall']);
    $room = Room::create(['building_id' => $building->building_id, 'name' => 'Room 301', 'floor_no' => 3]);

    Schedule::create([
        'course_block_id' => $this->courseBlock->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->semester->semester_id,
        'block_code' => $this->courseBlock->block_code,
        'schedule_type' => 'lecture',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    $response = $this->deleteJson("/api/course-block/{$this->courseBlock->course_block_id}");

    $response->assertStatus(422)
        ->assertJson([
            'message' => "Cannot archive course block '{$this->courseBlock->block_code}': it currently contains linked active data (1 class schedule(s)). Please unassign all users and delete class schedules before archiving this block.",
            'errors' => [
                'block' => ['Course block has linked active records: 1 class schedule(s).'],
            ],
        ]);

    $this->assertNotSoftDeleted('course_blocks', [
        'course_block_id' => $this->courseBlock->course_block_id,
    ]);
});

test('cannot archive block with active assigned users (422 Unprocessable Entity)', function () {
    Sanctum::actingAs($this->admin);

    UserCourseBlock::create([
        'user_id' => $this->student->user_id,
        'course_block_id' => $this->courseBlock->course_block_id,
        'assigned_at' => now(),
    ]);

    $response = $this->deleteJson("/api/course-blocks/{$this->courseBlock->course_block_id}");

    $response->assertStatus(422)
        ->assertJson([
            'message' => "Cannot archive course block '{$this->courseBlock->block_code}': it currently contains linked active data (1 assigned user(s) or student(s)). Please unassign all users and delete class schedules before archiving this block.",
            'errors' => [
                'block' => ['Course block has linked active records: 1 assigned user(s) or student(s).'],
            ],
        ]);

    $this->assertNotSoftDeleted('course_blocks', [
        'course_block_id' => $this->courseBlock->course_block_id,
    ]);
});

test('admin can view archives for a specific course (/courses/{course}/block-archives)', function () {
    Sanctum::actingAs($this->admin);

    // Soft delete this block
    $this->courseBlock->delete();

    // Create a second course with an archived block
    $secondCourse = Course::create(['subject_code' => 'CS102', 'name' => 'Data Structures']);
    $secondBlock = CourseBlock::create([
        'course_id' => $secondCourse->course_id,
        'semester_id' => $this->semester->semester_id,
        'block_code' => 'CS102-A',
    ]);
    $secondBlock->delete();

    $response = $this->getJson("/api/courses/{$this->course->course_id}/block-archives");

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['block_code'])->toBe('CS101-A');
    expect($data[0]['course_id'])->toBe($this->course->course_id);
});

test('admin can view archives for all courses (/course-blocks/archives) with search filter', function () {
    Sanctum::actingAs($this->admin);

    $this->courseBlock->delete();

    $secondCourse = Course::create(['subject_code' => 'MATH101', 'name' => 'Calculus']);
    $secondBlock = CourseBlock::create([
        'course_id' => $secondCourse->course_id,
        'semester_id' => $this->semester->semester_id,
        'block_code' => 'MATH101-A',
    ]);
    $secondBlock->delete();

    // Fetch all archives
    $allResponse = $this->getJson('/api/course-blocks/archives');
    $allResponse->assertStatus(200);
    expect($allResponse->json('data'))->toHaveCount(2);

    // Search by block_code
    $searchResponse = $this->getJson('/api/course-blocks/archives?search=MATH');
    $searchResponse->assertStatus(200);
    $searchData = $searchResponse->json('data');
    expect($searchData)->toHaveCount(1);
    expect($searchData[0]['block_code'])->toBe('MATH101-A');
});

test('admin can restore an archived block (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $this->courseBlock->delete();
    expect($this->courseBlock->fresh()->trashed())->toBeTrue();

    // Singular restore
    $response = $this->postJson("/api/course-block/{$this->courseBlock->course_block_id}/restore");

    $response->assertStatus(200)
        ->assertJson([
            'message' => "Course block '{$this->courseBlock->block_code}' has been restored successfully.",
            'data' => [
                'course_block_id' => $this->courseBlock->course_block_id,
                'block_code' => 'CS101-A',
            ],
        ]);

    expect($this->courseBlock->fresh()->trashed())->toBeFalse();

    // Archive and plural restore
    $this->courseBlock->delete();
    $pluralResponse = $this->postJson("/api/course-blocks/{$this->courseBlock->course_block_id}/restore");
    $pluralResponse->assertStatus(200);
    expect($this->courseBlock->fresh()->trashed())->toBeFalse();
});

test('cannot restore course block when parent course is archived (422)', function () {
    Sanctum::actingAs($this->admin);

    $this->courseBlock->delete();
    $this->course->delete();

    $response = $this->postJson("/api/course-block/{$this->courseBlock->course_block_id}/restore");

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Cannot restore this course block because its parent course is archived. Please restore the course first.',
        ]);

    expect($this->courseBlock->fresh()->trashed())->toBeTrue();
});
