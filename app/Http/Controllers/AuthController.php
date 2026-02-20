<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => Password::defaults()
        ]);

        $remember = $request->remember === 'on';

        if(Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout Success');
    }

    public function register(Request $request)
    {
        $information = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:App\Models\User|email',
            'password' => Password::defaults(),
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'address' => 'required',
            'province' => 'required',
            'phone' => 'required'
        ]);

        $user = User::create($information);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration Success!');
    }
}
