<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Determine which guard to use based on the route prefix
        $guard = null;
        if ($request->is('admin/*')) {
            $guard = 'admin';
        } elseif ($request->is('staff/*')) {
            $guard = 'staff';
        } else {
            $guard = 'web'; // default to web guard for customer routes
        }

        /** @var User|null $user */
        $user = Auth::guard($guard)->user();

        if (! $user || ! $user->hasPermission($permission)) {
            // Redirect to appropriate login based on guard
            $loginRoute = match ($guard) {
                'admin' => 'admin.login',
                'staff' => 'staff.login',
                default => 'login',
            };

            return redirect()->route($loginRoute)
                ->with('error', 'Please login to access this area.');
        }

        return $next($request);
    }
}
