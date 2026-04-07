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
        $guard = match (true) {
            $request->is('admin/*') => 'admin',
            $request->is('staff/*') => 'staff',
            default => 'web',
        };

        $user = Auth::guard($guard)->user();

        if (! $user) {
            $loginRoute = match ($guard) {
                'admin' => 'admin.login',
                'staff' => 'staff.login',
                default => 'login',
            };

            return redirect()->route($loginRoute)
                ->with('error', 'Please login to access this area.');
        }

        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have the required permission.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'You do not have the required permission.');
        }

        return $next($request);
    }
}
