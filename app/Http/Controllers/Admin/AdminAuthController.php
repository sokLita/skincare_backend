<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin() { return view('admin.login'); }

    public function login(Request $request) {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($creds) && Auth::user()->is_admin) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }
        Auth::logout();
        return back()->withErrors(['email' => 'Invalid admin credentials.']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('admin.login');
    }
}