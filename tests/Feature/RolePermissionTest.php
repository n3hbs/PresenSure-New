<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use App\Repositories\RoleRepository;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $instructor;

    private User $student;

    private Role $adminRole;

    private Role $instructorRole;

    private Role $studentRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->adminRole = Role::where('role_name', 'administrator')->first();
        $this->instructorRole = Role::where('role_name', 'instructor')->first();
        $this->studentRole = Role::where('role_name', 'student')->first();

        // Create Admin User
        $this->admin = User::create([
            'user_id' => 'ADMIN-001',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'sex' => 'male',
            'password' => bcrypt('password'),
        ]);
        UserRole::create(['user_id' => $this->admin->user_id, 'role_id' => $this->adminRole->role_id, 'assigned_at' => now()]);

        // Create Instructor User
        $this->instructor = User::create([
            'user_id' => 'INST-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'sex' => 'female',
            'password' => bcrypt('password'),
        ]);
        UserRole::create(['user_id' => $this->instructor->user_id, 'role_id' => $this->instructorRole->role_id, 'assigned_at' => now()]);
        UserProfile::create(['user_id' => $this->instructor->user_id, 'imagelink' => 'https://example.com/avatar.jpg']);

        // Create Student User
        $this->student = User::create([
            'user_id' => 'STUD-001',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'sex' => 'male',
            'password' => bcrypt('password'),
        ]);
        UserRole::create(['user_id' => $this->student->user_id, 'role_id' => $this->studentRole->role_id, 'assigned_at' => now()]);
    }

    public function test_has_role_enforces_strict_matching_and_does_not_universal_bypass(): void
    {
        $this->assertTrue($this->admin->hasRole('administrator'));
        $this->assertFalse($this->admin->hasRole('student'));
        $this->assertFalse($this->admin->hasRole('instructor'));

        $this->assertTrue($this->instructor->hasRole('instructor'));
        $this->assertFalse($this->instructor->hasRole('administrator'));

        $this->assertTrue($this->student->hasRole('student'));
        $this->assertFalse($this->student->hasRole('instructor'));
    }

    public function test_has_permission_grants_universal_bypass_to_system_admin(): void
    {
        $this->assertTrue($this->admin->hasPermission('students.view'));
        $this->assertTrue($this->admin->hasPermission('students.create'));
        $this->assertTrue($this->admin->hasPermission('roles.manage'));
        $this->assertTrue($this->admin->hasPermission('non_existent.permission'));
    }

    public function test_has_permission_checks_role_permissions_for_non_admins(): void
    {
        $this->assertTrue($this->instructor->hasPermission('students.view'));
        $this->assertTrue($this->instructor->hasPermission('attendance.sessions.manage'));
        $this->assertFalse($this->instructor->hasPermission('roles.manage'));

        $this->assertTrue($this->student->hasPermission('attendance.records.view'));
        $this->assertFalse($this->student->hasPermission('students.create'));
    }

    public function test_sync_role_permissions_safeguard_protects_admin_role_from_losing_roles_manage(): void
    {
        $repository = app(RoleRepository::class);

        // Attempt to sync admin role with only a student permission
        $studentPerm = Permission::where('permission_name', 'attendance.records.view')->first();
        $updatedRole = $repository->syncRolePermissions($this->adminRole->role_id, [$studentPerm->permission_id]);

        $assignedPermNames = $updatedRole->permissions->pluck('permission_name')->toArray();

        $this->assertContains('roles.manage', $assignedPermNames);
        $this->assertContains('roles.view', $assignedPermNames);
        $this->assertContains('attendance.records.view', $assignedPermNames);
    }

    public function test_tier1_routes_require_administrator_role(): void
    {
        Sanctum::actingAs($this->instructor);

        $response = $this->getJson('/api/roles');
        $response->assertStatus(403);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/roles');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'role_id',
                    'role_name',
                    'is_system_admin',
                    'description',
                    'user_role_count',
                    'permissions',
                ],
            ],
        ]);
    }

    public function test_permissions_endpoint_returns_grouped_modules_with_module_name(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/permissions');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('students', $data);
        $this->assertArrayHasKey('instructors', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('academic', $data);
    }

    public function test_user_permissions_endpoint_includes_profile_picture(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/users/{$this->instructor->user_id}/permissions");
        $response->assertStatus(200);

        $this->assertEquals('https://example.com/avatar.jpg', $response->json('data.profile_picture'));
        $this->assertEquals($this->instructor->user_id, $response->json('data.user_id'));
    }

    public function test_user_search_endpoint_returns_results_with_profile_picture(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users/search?query=Jane');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('INST-001', $data[0]['user_id']);
        $this->assertEquals('https://example.com/avatar.jpg', $data[0]['profile_picture']);
    }
}
