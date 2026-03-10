<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Gunakan validator agar jika gagal, return-nya JSON 422, bukan redirect back
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->remember)) {
            $request->session()->regenerate();

            return response()->json([
                'status' => true,
                'message' => 'Login Berhasil',
                // 'token' => ... (jika butuh)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Email atau Password salah.',
        ], 401); // 401 = Unauthorized
    }
}
