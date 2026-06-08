<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;

class AuthService {
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function signIn (array $data){
        $user = $this->authRepository->findCredentials($data);

        if (!$user || !Hash::check($data['password'], $user->password)){
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return ([
            'message' => 'Signed in successfully',
            'token' => $token,
            'user' => $user
        ]);
    }
}