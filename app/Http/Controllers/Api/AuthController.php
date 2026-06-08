<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}


    public function signIn(AuthRequest $request)
    {
        $user = $this->authService->signIn($request->validated());

        return response()->json([
            'message' => "successfully login",
            'data' => $user,
        ]);
    }
}
