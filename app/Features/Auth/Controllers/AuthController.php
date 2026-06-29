<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Requests\LoginRequest;
use App\Features\Auth\Requests\SignupRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    public function signup(SignupRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $user = User::query()->create([
            'username' => $payload['username'],
            'name' => $payload['name'] ?? $payload['username'],
            'email' => $payload['email'] ?? $payload['username'].'@snack-eclat.local',
            'password' => Hash::make($payload['password']),
            'level' => $payload['level'] ?? 'admin',
        ]);

        return $this->successResponse($this->issueToken($user), 'Sign up successful', [], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = User::query()->where('username', $payload['username'])->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            return $this->errorResponse('Username atau password tidak sesuai', 422, [
                'username' => ['Username atau password tidak sesuai'],
            ]);
        }

        return $this->successResponse($this->issueToken($user), 'Login successful');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($this->userPayload($request->user()), 'Authenticated user loaded');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->forceFill(['api_token_hash' => null])->save();

        return $this->successResponse(null, 'Logout successful');
    }

    private function issueToken(User $user): array
    {
        $token = Str::random(64);

        $user->forceFill([
            'api_token_hash' => hash('sha256', $token),
        ])->save();

        return [
            'token' => $token,
            'user' => $this->userPayload($user),
        ];
    }

    private function userPayload(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $user->level,
        ];
    }
}
