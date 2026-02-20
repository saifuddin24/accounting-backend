<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for Header (Prioritize X-Profile-ID, fallback to X-Company-ID for transition if needed, but strict for now)
        $profileId = $request->header('X-Profile-ID');

        if (!$profileId) {
            // Optional: Return 400 if tenant is mandatory, or just proceed without tenant scope
            // return response()->json(['message' => 'Profile ID header (X-Profile-ID) is required'], 400);
        }

        if ($profileId) {
            // 2. Validate User Access
            $user = $request->user();

            // If user is authenticated, check their access to this profile
            if ($user) {
                // Determine if user has access. We check the 'profile_user' pivot table.
                // Assuming relationships are loaded or we query directly.
                $hasAccess = $user->profiles()->where('profiles.id', $profileId)->exists();

                if (!$hasAccess) {
                    return response()->json(['message' => 'Unauthorized access to this profile'], 403);
                }
            }

            // 3. Set Global Scope Config
            config(['accounting.profile_id' => $profileId]);
        }

        return $next($request);
    }
}
