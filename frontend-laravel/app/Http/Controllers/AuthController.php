<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('auth_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showSignUp()
    {
        return view('auth.signup');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $apiUrl = env('API_BASE_URL', 'http://localhost:3000/api');

        try {
            $response = Http::post($apiUrl . '/login', [
                'email' => $request->email,
                'password' => $request->password
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Email atau password salah.');
            }

            $data = $response->json();

            session([
                'auth_token' => $data['token'],
                'auth_user' => $data['user']
            ]);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return back()->with('error', 'Tidak dapat terhubung ke backend.');
        }
    }

    public function logout()
    {
        session()->forget(['auth_token', 'auth_user']);

        return redirect()->route('login');
    }
}