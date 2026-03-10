<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('staff')->check()) {
            return redirect()->route('staff.login');
        }

        $user = Auth::guard('staff')->user();

        if (! $user || (! $user->isStaff() && ! $user->isAdmin())) {
            Auth::guard('staff')->logout();

            return redirect()->route('staff.login')->with('error', 'Unauthorized access.');
        }

        // Kick out banned staff immediately
        if ($user->isBanned()) {
            Auth::guard('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('staff.login')->with('error', 'Your account has been suspended.');
        }

        // Set user for Gate so @can directives work properly
        Auth::setUser($user);

        return $next($request);
    }
}
