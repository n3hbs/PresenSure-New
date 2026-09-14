<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}


    public function signIn(AuthRequest $request)
    {
        $result = $this->authService->signIn($request->validated());
        if (!$result) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        return response()->json([
            'message' => 'Successfully login',
            'data' => new AuthResource($result)
        ]);
    }
}
