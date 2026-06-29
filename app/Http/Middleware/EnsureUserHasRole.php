<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->level, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'errors' => null,
                'meta' => ['status_code' => 403],
            ], 403);
        }

        return $next($request);
    }
}
