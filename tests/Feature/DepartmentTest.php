<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\Role;
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
        'user_id' => 'ADMIN-DEPT-001',
        'first_name' => 'Admin',
        'last_name' => 'Department',
        'sex' => 'male',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->admin->user_id,
        'role_id' => $adminRole->role_id,
        'assigned_at' => now(),
    ]);

    $this->student = User::create([
        'user_id' => 'STUDENT-DEPT-001',
        'first_name' => 'Student',
        'last_name' => 'Department',
        'sex' => 'female',
        'password' => bcrypt('password'),
    ]);
    UserRole::create([
        'user_id' => $this->student->user_id,
        'role_id' => $studentRole->role_id,
        'assigned_at' => now(),
    ]);
});

test('unauthenticated user cannot access departments management', function () {
    $response = $this->getJson('/api/v1/departments');

    $response->assertStatus(401);
});

test('unauthorized user without permission receives 403 forbidden', function () {
    Sanctum::actingAs($this->student);

    $response = $this->getJson('/api/v1/departments');

    $response->assertStatus(403);
});

test('admin can fetch departments list (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    Department::create([
        'department_code' => 'CCS',
        'department_name' => 'College of Computer Studies',
    ]);

    $response = $this->getJson('/api/v1/departments');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => ['department_id', 'department_code', 'department_name'],
            ],
        ]);
});

test('admin can create department with programs (201 Created)', function () {
    Sanctum::actingAs($this->admin);

    $payload = [
        'department_code' => 'CCS',
        'department_name' => 'College of Computer Studies',
        'description' => 'Excellence in computing education.',
        'programs' => [
            [
                'program_code' => 'BSIT',
                'program_name' => 'Bachelor of Science in Information Technology',
                'program_years' => 4,
            ],
            [
                'program_code' => 'BSCS',
                'program_name' => 'Bachelor of Science in Computer Science',
                'program_years' => 4,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/departments', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.department_code', 'CCS')
        ->assertJsonPath('data.department_name', 'College of Computer Studies')
        ->assertJsonCount(2, 'data.programs');

    $this->assertDatabaseHas('departments', [
        'department_code' => 'CCS',
        'department_name' => 'College of Computer Studies',
    ]);

    $this->assertDatabaseHas('programs', [
        'program_code' => 'BSIT',
        'program_name' => 'Bachelor of Science in Information Technology',
    ]);
});

test('validation fails (422) for duplicate department code or name', function () {
    Sanctum::actingAs($this->admin);

    Department::create([
        'department_code' => 'CCS',
        'department_name' => 'College of Computer Studies',
    ]);

    $payload = [
        'department_code' => 'CCS',
        'department_name' => 'College of Computing',
    ];

    $response = $this->postJson('/api/v1/departments', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['data' => ['errors' => ['department_code']]]);
});

test('admin can fetch department details with programs (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'CTE',
        'department_name' => 'College of Teacher Education',
    ]);

    Program::create([
        'department_id' => $dept->department_id,
        'program_code' => 'BSED',
        'program_name' => 'Bachelor of Secondary Education',
        'program_years' => 4,
    ]);

    $response = $this->getJson("/api/v1/departments/{$dept->department_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.department_code', 'CTE')
        ->assertJsonPath('data.programs.0.program_code', 'BSED');
});

test('admin can update department and programs (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'CBE',
        'department_name' => 'College of Business Education',
    ]);

    $payload = [
        'department_code' => 'CBMA',
        'department_name' => 'College of Business, Management and Accountancy',
        'description' => 'Updated curriculum',
        'programs' => [
            [
                'program_code' => 'BSBA',
                'program_name' => 'Bachelor of Science in Business Administration',
                'program_years' => 4,
            ],
        ],
    ];

    $response = $this->putJson("/api/v1/departments/{$dept->department_id}", $payload);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.department_code', 'CBMA');

    $this->assertDatabaseHas('departments', [
        'department_id' => $dept->department_id,
        'department_code' => 'CBMA',
    ]);
});

test('department cannot be deleted when it has dependent instructors', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'CAS',
        'department_name' => 'College of Arts and Sciences',
    ]);

    Instructor::create([
        'user_id' => $this->admin->user_id,
        'department_id' => $dept->department_id,
        'status' => 'Active',
    ]);

    $response = $this->deleteJson("/api/v1/departments/{$dept->department_id}");

    $response->assertStatus(422)
        ->assertJsonPath('success', false);

    expect(Department::find($dept->department_id))->not->toBeNull();
});

test('admin can delete department without dependencies (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'TEST',
        'department_name' => 'Test Department',
    ]);

    $response = $this->deleteJson("/api/v1/departments/{$dept->department_id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    expect(Department::find($dept->department_id))->toBeNull();
    expect(Department::withTrashed()->find($dept->department_id))->not->toBeNull();
});

test('admin can fetch archived departments list (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'ARCH',
        'department_name' => 'Archived Department',
    ]);
    $dept->delete();

    $response = $this->getJson('/api/v1/departments/archives');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.department_id', $dept->department_id);
});

test('admin can restore archived department (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'REST',
        'department_name' => 'Restore Department',
    ]);
    $dept->delete();

    expect(Department::find($dept->department_id))->toBeNull();

    $response = $this->postJson("/api/v1/departments/{$dept->department_id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.department_id', $dept->department_id);

    expect(Department::find($dept->department_id))->not->toBeNull();
});

test('authenticated user can view departments web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/departments');

    $response->assertStatus(200);
});

test('authenticated user can view department create web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/departments/create');

    $response->assertStatus(200);
});

test('authenticated user can view department edit web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'EDIT',
        'department_name' => 'Edit Department',
    ]);

    $response = $this->get("/departments/edit?department_id={$dept->department_id}");

    $response->assertStatus(200);
});

test('authenticated user can view department details web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $dept = Department::create([
        'department_code' => 'DET',
        'department_name' => 'Details Department',
    ]);

    $response = $this->get("/departments/department-details?department_id={$dept->department_id}");

    $response->assertStatus(200);
});

test('authenticated user can view department archives web page (200 OK)', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->get('/departments/archives');

    $response->assertStatus(200);
});
