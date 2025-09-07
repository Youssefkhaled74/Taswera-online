<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        $branch = Branch::where('token', $token)
            ->where('is_active', true)
            ->first();

        if (! $branch) {
            return response()->json(['message' => 'Unauthorized: Invalid or inactive token'], 401);
        }

        // inject branch_id into the request
        $request->merge([
            'branch_id' => $branch->id,
        ]);

        return $next($request);
    }
}
