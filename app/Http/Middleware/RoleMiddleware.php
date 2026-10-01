<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /** Usage: ->middleware(RoleMiddleware::class.':admin,staff') */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Your role does not have access to this page.');
        }
        if ($user->registration_status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account is suspended. Contact the administrator.']);
        }
        return $next($request);
    }
}
