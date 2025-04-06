<?php

namespace App\Http\Controllers;
use function view;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginProses(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Redirect sesuai role
            if ($user->role === 'admin') {
                return redirect('dashboard');
            } 

            return redirect('/'); // Default fallback
        }

        return back()->withErrors([
            'email' => 'Email atau password salah!',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
