<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        if ($user->status !== User::STATUS_ACTIVE) {
            Auth::logout();
            $message = $user->status === User::STATUS_PENDING
                ? 'Your account is pending approval. Please wait for an administrator to approve your account.'
                : 'Your account is not active. Please contact an administrator.';
            return back()->withErrors([
                'email' => $message,
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
