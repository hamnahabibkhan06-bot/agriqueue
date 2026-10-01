<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $r)
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (!Auth::attempt($cred, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
        }
        if (Auth::user()->registration_status === 'suspended') {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account is suspended. Contact the administrator.']);
        }
        $r->session()->regenerate();
        ActivityLog::record('login');
        return redirect()->route('dashboard');
    }

    public function register(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'location' => 'required|string|max:150',
            'identification' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);
        // Public sign-up always creates a farmer. Staff, inspectors and admins are created by an administrator.
        $user = User::create($d + ['role' => 'farmer', 'registration_status' => 'verified']);
        Auth::login($user);
        $r->session()->regenerate();
        ActivityLog::record('register', $user->email);
        return redirect()->route('dashboard');
    }

    public function logout(Request $r)
    {
        ActivityLog::record('logout');
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }
}
