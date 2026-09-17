<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserPermissionService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected UserPermissionService $userPermissionService
    ) {}

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['roleAssignment.role', 'userProfile']);

        return $this->successResponse(
            new UserResource($user),
            'Authenticated user retrieved successfully.',
            200
        );
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query', ''));
        if (strlen($query) < 1) {
            return $this->successResponse([], 'No search query provided.', 200);
        }

        $users = $this->userPermissionService->searchUsers($query);

        return $this->successResponse(
            UserResource::collection($users),
            'Users found.',
            200
        );
    }

    public function resetPassword(string $user_id)
    {
        $this->userService->resetPassword($user_id);

        return $this->successResponse(
            null,
            'Password has been reset to default (last name) successfully.',
            200
        );
    }
}
