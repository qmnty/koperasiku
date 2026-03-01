<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user berdasarkan email
        $user = DB::table('users')->where('email', $request->email)->first();

        // Verifikasi password (menggunakan Hash::check)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Kredensial yang Anda berikan salah.'
            ], 401);
        }

        if (Auth::attempt($credentials, $request->remember)) {
        
            // Regenerasi session untuk keamanan (mencegah session fixation)
            $request->session()->regenerate();

            // Untuk Inertia, JANGAN return JSON, tapi redirect ke dashboard/home
            return redirect()->intended('/'); 
        }

        // return response()->json([
        //     'message' => 'Login berhasil',
        //     'user' => [
        //         'name' => $user->name,
        //     ],
        //     // 'token' => $token 
        // ]);
    }
}
