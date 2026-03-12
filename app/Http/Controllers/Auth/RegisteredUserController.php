<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:cdc,hod,faculty,observer'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['exists:departments,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => User::STATUS_PENDING,
        ]);

        if (!empty($request->department_ids)) {
            $user->departments()->sync($request->department_ids);
        }

        event(new Registered($user));

        // Don't auto-login pending users
        // Auth::login($user);

        return redirect(route('login', absolute: false))
            ->with('success', 'Registration successful! Your account is pending admin approval.');
    }
}
