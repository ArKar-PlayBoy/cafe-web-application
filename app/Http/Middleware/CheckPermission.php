<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $user = auth('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access this area.');
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
