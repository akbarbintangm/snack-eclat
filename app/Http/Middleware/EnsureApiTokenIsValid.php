<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => null,
                'meta' => ['status_code' => 401],
            ], 401);
        }

        $user = User::query()
            ->where('api_token_hash', hash('sha256', $token))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication token',
                'errors' => null,
                'meta' => ['status_code' => 401],
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
