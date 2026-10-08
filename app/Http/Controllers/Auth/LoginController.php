<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;

class LoginController extends Controller
{
    /**
     * Show USER login form (normal /login)
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle USER login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            UserActivity::create([
                'user_id' => $user->id,
                'action' => 'login',
                'details' => 'User logged in successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Agar admin ne user login form se login kiya, toh admin dashboard bhejo
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, Admin!');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show ADMIN login form (/admin/login)
     */
    public function showAdminLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.admin-login');
    }

    /**
     * Handle ADMIN login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Agar admin nahi hai, toh logout karke wapas bhejo
            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You are not authorized to access admin panel.',
                ])->onlyInput('email');
            }

            $user->update(['last_login_at' => now()]);

            UserActivity::create([
                'user_id' => $user->id,
                'action' => 'admin_login',
                'details' => 'Admin logged in successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, Admin!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'details' => 'User logged out',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}