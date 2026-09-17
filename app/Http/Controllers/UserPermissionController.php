<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateUserPermissionsRequest;
use App\Http\Resources\UserPermissionResource;
use App\Services\UserPermissionService;

class UserPermissionController extends Controller
{
    public function __construct(
        protected UserPermissionService $userPermissionService
    ) {}

    /**
     * Get permission details for a specific user.
     */
    public function getUserPermissions(string $user_id)
    {
        $data = $this->userPermissionService->getUserPermissionData($user_id);

        return $this->successResponse(
            new UserPermissionResource($data),
            'User permissions retrieved successfully.',
            200
        );
    }

    /**
     * Update/Sync direct permissions for a specific user.
     */
    public function updateUserPermissions(UpdateUserPermissionsRequest $request, string $user_id)
    {
        $validated = $request->validated();
        $data = $this->userPermissionService->syncUserPermissions($user_id, $validated['permission_ids']);

        return $this->successResponse(
            new UserPermissionResource($data),
            'User permissions updated successfully.',
            200
        );
    }

    /**
     * Reset user permissions to default role inheritance.
     */
    public function resetUserPermissions(string $user_id)
    {
        $data = $this->userPermissionService->resetUserPermissions($user_id);

        return $this->successResponse(
            new UserPermissionResource($data),
            'User permissions reset to role defaults successfully.',
            200
        );
    }
}
