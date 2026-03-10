<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access this area.');
        }

        $userRole = $user->role;

        if (!$userRole) {
            abort(403, 'User does not have a role assigned.');
        }

        // Super admin can access everything
        if ($userRole->is_super_admin) {
            return $next($request);
        }

        // Check if user's role is in the allowed roles
        if (!in_array($userRole->slug, $roles)) {
            abort(403, 'You do not have the required role to access this area.');
        }

        return $next($request);
    }
}
