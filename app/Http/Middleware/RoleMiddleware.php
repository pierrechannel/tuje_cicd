<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User; // Import the User model

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Get the authenticated user's ID from the request
        $userId = $request->user() ? $request->user()->id : null; // Get user ID
        $user = $userId ? User::find($userId) : null; // Find the user by ID

        // Debug output (for development only, consider removing in production)
        if ($user) {
            echo 'User: ' . $user->name; // Replace with your desired user output
        } else {
            echo 'User: Not authenticated';
        }

        // Check if the user is authenticated and has one of the required roles
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
