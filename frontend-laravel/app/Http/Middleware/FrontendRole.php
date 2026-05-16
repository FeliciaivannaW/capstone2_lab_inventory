<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = session('auth_user');

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user['role'], $roles)) {
            abort(403, 'Akses ditolak untuk role ini.');
        }

        return $next($request);
    }
}