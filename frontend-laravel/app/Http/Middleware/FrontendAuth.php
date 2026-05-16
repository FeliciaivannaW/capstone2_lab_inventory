<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('auth_token')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}