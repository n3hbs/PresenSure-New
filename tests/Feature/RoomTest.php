<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\SchoolYear;
use App\Models\Semester;
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
        'user_id' => 'ADMIN-ROOM-001',
        'first_name' => 'Admin',
        'last_name' => 'Facility',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->admin->user_id,
        'role_id' => $adminRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->student = User::create([
        'user_id' => 'STUDENT-ROOM-001',
        'first_name' => 'Student',
        'last_name' => 'Facility',
        'sex' => 'female',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->student->user_id,
        'role_id' => $studentRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->building = Building::create([
        'code' => 'TEST-BLDG',
        'name' => 'Test Facility Building',
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
});

test('unauthenticated user cannot access rooms management', function () {
    $response = $this->getJson('/api/rooms');

    $response->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $response = $this->getJson('/api/rooms/archives');
    $response->assertStatus(403);

    $createResponse = $this->postJson('/api/rooms', [
        'building_id' => $this->building->building_id,
        'name' => 'Room 101',
        'floor_no' => 1,
    ]);
    $createResponse->assertStatus(403);
});

test('admin can fetch rooms list with optional filters (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Room 101',
        'floor_no' => 1,
        'capacity' => 30,
        'status' => 'Active',
    ]);

    Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Room 201',
        'floor_no' => 2,
        'capacity' => 45,
        'status' => 'Inactive',
    ]);

    $responseAll = $this->getJson('/api/rooms');
    $responseAll->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');

    // Filter by floor_no
    $responseFloor = $this->getJson('/api/rooms?floor_no=2');
    $responseFloor->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Room 201');

    // Filter by status
    $responseStatus = $this->getJson('/api/rooms?status=Inactive');
    $responseStatus->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Room 201');
});

test('admin can store room with valid building (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'building_id' => $this->building->building_id,
        'name' => 'Computer Lab 1',
        'floor_no' => 3,
        'capacity' => 50,
        'status' => 'Active',
    ];

    $response = $this->postJson('/api/rooms', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Computer Lab 1')
        ->assertJsonPath('data.floor_no', 3)
        ->assertJsonPath('data.capacity', 50)
        ->assertJsonPath('data.status', 'Active');

    $this->assertDatabaseHas('rooms', [
        'building_id' => $this->building->building_id,
        'name' => 'Computer Lab 1',
        'floor_no' => 3,
    ]);
});

test('store room validates building existence and non-negative floor (422 Unprocessable)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/rooms', [
        'building_id' => 999999,
        'name' => '',
        'floor_no' => -2,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('admin can view room details with building and assigned schedules (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Lecture Room 401',
        'floor_no' => 4,
        'capacity' => 60,
        'status' => 'Active',
    ]);

    $course = Course::create([
        'subject_code' => 'MATH101',
        'name' => 'Calculus I',
    ]);

    $block = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-1A',
    ]);

    $schedule = Schedule::create([
        'course_block_id' => $block->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-1A',
        'schedule_type' => 'lecture',
        'start_time' => '13:00:00',
        'end_time' => '15:00:00',
    ]);

    ScheduleDay::create([
        'schedule_id' => $schedule->schedule_id,
        'day' => 'tuesday',
        'assigned_at' => now(),
    ]);

    $response = $this->getJson("/api/rooms/{$room->room_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.room_id', $room->room_id)
        ->assertJsonPath('data.name', 'Lecture Room 401')
        ->assertJsonPath('data.building.code', 'TEST-BLDG')
        ->assertJsonPath('data.schedules.0.block_code', 'BSCS-1A')
        ->assertJsonPath('data.schedules.0.start_time', '13:00:00')
        ->assertJsonPath('data.schedules.0.days.0', 'tuesday')
        ->assertJsonPath('data.schedules.0.course.course_code', 'MATH101');
});

test('admin can update room details (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Old Room Name',
        'floor_no' => 1,
        'capacity' => 20,
        'status' => 'Active',
    ]);

    $response = $this->putJson("/api/rooms/{$room->room_id}", [
        'name' => 'Renovated Room Name',
        'capacity' => 35,
        'status' => 'Inactive',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Renovated Room Name')
        ->assertJsonPath('data.capacity', 35)
        ->assertJsonPath('data.status', 'Inactive');

    $this->assertDatabaseHas('rooms', [
        'room_id' => $room->room_id,
        'name' => 'Renovated Room Name',
        'capacity' => 35,
        'status' => 'Inactive',
    ]);
});

test('admin can soft-delete an unused room (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Empty Room',
        'floor_no' => 1,
    ]);

    $response = $this->deleteJson("/api/rooms/{$room->room_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Room archived successfully.');

    $this->assertSoftDeleted('rooms', ['room_id' => $room->room_id]);
});

test('admin cannot delete room with assigned class schedules (422 Unprocessable)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Busy Room',
        'floor_no' => 1,
    ]);

    $course = Course::create([
        'subject_code' => 'CS102',
        'name' => 'Data Structures',
    ]);

    $block = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-1B',
    ]);

    Schedule::create([
        'course_block_id' => $block->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-1B',
        'schedule_type' => 'laboratory',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->deleteJson("/api/rooms/{$room->room_id}");

    $response->assertStatus(422)
        ->assertJsonPath('success', false);

    $this->assertNotSoftDeleted('rooms', ['room_id' => $room->room_id]);
});

test('admin can fetch archived rooms (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Archived Room',
        'floor_no' => 1,
    ]);
    $room->delete();

    $response = $this->getJson('/api/rooms/archives');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['name' => 'Archived Room']);
});

test('admin can restore archived room (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $room = Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Restorable Room',
        'floor_no' => 1,
    ]);
    $room->delete();

    $response = $this->postJson("/api/rooms/{$room->room_id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Restorable Room');

    $this->assertNotSoftDeleted('rooms', ['room_id' => $room->room_id]);
});

test('legacy room creation endpoint works (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/room', [
        'building_id' => $this->building->building_id,
        'name' => 'Legacy Room 501',
        'floor_no' => 5,
        'capacity' => 25,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('rooms', ['name' => 'Legacy Room 501']);
});

test('authenticated user can fetch rooms on public tier 3 endpoint (200 OK)', function () {
    Sanctum::actingAs($this->student);

    Room::create([
        'building_id' => $this->building->building_id,
        'name' => 'Public Room Lookup',
        'floor_no' => 1,
    ]);

    $response = $this->getJson('/api/rooms');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['name' => 'Public Room Lookup']);
});
