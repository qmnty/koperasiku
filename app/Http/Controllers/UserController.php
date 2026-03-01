<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Mengambil user kecuali password untuk keamanan
        $users = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            // 'role'     => 'required|in:admin,manager,staff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 2. Insert ke Database
            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password), // Enkripsi password
                // 'role'       => $request->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'User berhasil ditambahkan',
                'id'      => $userId
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Proteksi: Jangan biarkan admin menghapus dirinya sendiri
            if (auth()->check() && auth()->id() == $id) {
                return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri!'], 403);
            }

            $user = DB::table('users')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            DB::table('users')->where('id', $id)->delete();

            return response()->json(['message' => 'User berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }
}
