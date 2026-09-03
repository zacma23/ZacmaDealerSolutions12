<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Super Admin bypasses role checks for all management portals
        if ($user->role === 'SUPER_ADMIN') {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Robustly parse comma-separated role arguments
        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode(',', (string) $r) as $sub) {
                $trimmed = trim($sub);
                if ($trimmed !== '') {
                    $allowed[] = $trimmed;
                }
            }
        }

        if (in_array($user->role, $allowed, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to this portal.');
    }
}
