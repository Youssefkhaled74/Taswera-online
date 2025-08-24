<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $allowedTokens = [
            'branch-abc-secret-token' => 1, // Branch 1
            'branch-xyz-secret-token' => 2, // Branch 2
        ];

        $token = $request->bearerToken();

        if (! $token || ! isset($allowedTokens[$token])) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->merge([
            'branch_id' => $allowedTokens[$token],
        ]);

        return $next($request);
    }
}
