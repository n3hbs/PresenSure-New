<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\UpdateRolePermissionsRequest;
use App\Http\Resources\RoleWithPermissionsResource;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index()
    {
        $roles = $this->roleService->getAllRolesWithPermissions();

        return $this->successResponse(
            RoleWithPermissionsResource::collection($roles),
            'Roles retrieved successfully.',
            200
        );
    }

    public function permissions()
    {
        $grouped = $this->roleService->getAllPermissionsGrouped();

        return $this->successResponse(
            $grouped,
            'Permissions retrieved successfully.',
            200
        );
    }

    public function updateRolePermissions(UpdateRolePermissionsRequest $request, int $role_id)
    {
        $validated = $request->validated();
        $role = $this->roleService->syncRolePermissions($role_id, $validated['permission_ids']);

        return $this->successResponse(
            new RoleWithPermissionsResource($role),
            'Role permissions updated successfully.',
            200
        );
    }
}
