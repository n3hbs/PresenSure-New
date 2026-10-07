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
        'user_id' => 'ADMIN-BLDG-001',
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
        'user_id' => 'STUDENT-BLDG-001',
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

test('unauthenticated user cannot access buildings management', function () {
    $response = $this->getJson('/api/buildings');

    $response->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $response = $this->getJson('/api/buildings/archives');
    $response->assertStatus(403);

    $createResponse = $this->postJson('/api/buildings', [
        'code' => 'ENG-BLDG',
        'name' => 'Engineering Complex',
    ]);
    $createResponse->assertStatus(403);
});

test('admin can fetch buildings list with room counts (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'ENG-BLDG',
        'name' => 'Engineering Complex',
    ]);

    Room::create([
        'building_id' => $bldg->building_id,
        'name' => 'Lab 101',
        'floor_no' => 1,
        'capacity' => 40,
        'status' => 'Active',
    ]);

    $response = $this->getJson('/api/buildings');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'building_id',
                    'code',
                    'name',
                    'rooms_count',
                ],
            ],
        ])
        ->assertJsonFragment([
            'code' => 'ENG-BLDG',
            'name' => 'Engineering Complex',
            'rooms_count' => 1,
        ]);
});

test('admin can store building with basic data (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'code' => 'CCS-BLDG',
        'name' => 'College of Computer Studies',
    ];

    $response = $this->postJson('/api/buildings', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.code', 'CCS-BLDG')
        ->assertJsonPath('data.name', 'College of Computer Studies');

    $this->assertDatabaseHas('buildings', [
        'code' => 'CCS-BLDG',
        'name' => 'College of Computer Studies',
    ]);
});

test('admin can store building with nested rooms batch (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'code' => 'SCI-BLDG',
        'name' => 'Science Building',
        'rooms' => [
            [
                'name' => 'Chem Lab 201',
                'floor_no' => 2,
                'capacity' => 30,
                'status' => 'Active',
            ],
            [
                'name' => 'Bio Lab 202',
                'floor_no' => 2,
                'capacity' => 35,
                'status' => 'Active',
            ],
        ],
    ];

    $response = $this->postJson('/api/buildings', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.code', 'SCI-BLDG')
        ->assertJsonPath('data.rooms_count', 2);

    $this->assertDatabaseHas('buildings', ['code' => 'SCI-BLDG']);
    $this->assertDatabaseHas('rooms', ['name' => 'Chem Lab 201', 'floor_no' => 2]);
    $this->assertDatabaseHas('rooms', ['name' => 'Bio Lab 202', 'floor_no' => 2]);
});

test('store building validates required fields and unique code (422 Unprocessable)', function () {
    Sanctum::actingAs($this->admin);

    Building::create([
        'code' => 'EXISTING-01',
        'name' => 'Existing Hall',
    ]);

    $response = $this->postJson('/api/buildings', [
        'code' => 'EXISTING-01',
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('admin can view building details with rooms and assigned schedules (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'ENG-BLDG',
        'name' => 'Engineering Hall',
    ]);

    $room = Room::create([
        'building_id' => $bldg->building_id,
        'name' => 'Room 301',
        'floor_no' => 3,
        'capacity' => 50,
        'status' => 'Active',
    ]);

    $course = Course::create([
        'subject_code' => 'CS301',
        'name' => 'Database Systems',
    ]);

    $courseBlock = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-3A',
    ]);

    $schedule = Schedule::create([
        'course_block_id' => $courseBlock->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'BSCS-3A',
        'schedule_type' => 'lecture',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    ScheduleDay::create([
        'schedule_id' => $schedule->schedule_id,
        'day' => 'monday',
        'assigned_at' => now(),
    ]);

    $response = $this->getJson("/api/buildings/{$bldg->building_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.building_id', $bldg->building_id)
        ->assertJsonPath('data.code', 'ENG-BLDG')
        ->assertJsonPath('data.rooms.0.name', 'Room 301')
        ->assertJsonPath('data.rooms.0.floor_no', 3)
        ->assertJsonPath('data.rooms.0.schedules.0.block_code', 'BSCS-3A')
        ->assertJsonPath('data.rooms.0.schedules.0.start_time', '08:00:00')
        ->assertJsonPath('data.rooms.0.schedules.0.days.0', 'monday')
        ->assertJsonPath('data.rooms.0.schedules.0.course.course_code', 'CS301');
});

test('admin can update building details (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'OLD-CODE',
        'name' => 'Old Building Name',
    ]);

    $response = $this->putJson("/api/buildings/{$bldg->building_id}", [
        'code' => 'NEW-CODE',
        'name' => 'Renovated Building Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.code', 'NEW-CODE')
        ->assertJsonPath('data.name', 'Renovated Building Name');

    $this->assertDatabaseHas('buildings', [
        'building_id' => $bldg->building_id,
        'code' => 'NEW-CODE',
        'name' => 'Renovated Building Name',
    ]);
});

test('admin can soft-delete an unused building (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'UNUSED-BLDG',
        'name' => 'Unused Building',
    ]);

    $response = $this->deleteJson("/api/buildings/{$bldg->building_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Building archived successfully.');

    $this->assertSoftDeleted('buildings', ['building_id' => $bldg->building_id]);
});

test('admin cannot delete building whose rooms have class schedules (422 Unprocessable)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'BUSY-BLDG',
        'name' => 'Busy Complex',
    ]);

    $room = Room::create([
        'building_id' => $bldg->building_id,
        'name' => 'Room 101',
        'floor_no' => 1,
        'capacity' => 40,
        'status' => 'Active',
    ]);

    $course = Course::create([
        'subject_code' => 'ENG101',
        'name' => 'English Communication',
    ]);

    $block = CourseBlock::create([
        'course_id' => $course->course_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'ENG-1A',
    ]);

    Schedule::create([
        'course_block_id' => $block->course_block_id,
        'room_id' => $room->room_id,
        'semester_id' => $this->activeSemester->semester_id,
        'block_code' => 'ENG-1A',
        'schedule_type' => 'lecture',
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->deleteJson("/api/buildings/{$bldg->building_id}");

    $response->assertStatus(422)
        ->assertJsonPath('success', false);

    $this->assertNotSoftDeleted('buildings', ['building_id' => $bldg->building_id]);
});

test('admin can fetch archived buildings (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'ARCH-BLDG',
        'name' => 'Archived Building',
    ]);
    $bldg->delete();

    $response = $this->getJson('/api/buildings/archives');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['code' => 'ARCH-BLDG']);
});

test('admin can restore archived building (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $bldg = Building::create([
        'code' => 'RESTORE-BLDG',
        'name' => 'To Be Restored Building',
    ]);
    $bldg->delete();

    $response = $this->postJson("/api/buildings/{$bldg->building_id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.code', 'RESTORE-BLDG');

    $this->assertNotSoftDeleted('buildings', ['building_id' => $bldg->building_id]);
});

test('legacy building creation endpoint works (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/building', [
        'code' => 'LEGACY-BLDG',
        'name' => 'Legacy Building',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('buildings', ['code' => 'LEGACY-BLDG']);
});
