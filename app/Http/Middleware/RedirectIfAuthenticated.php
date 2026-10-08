<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Agar admin hai, toh admin dashboard bhejo
                if ($user && $user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                // Warna user ko home bhejo
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}