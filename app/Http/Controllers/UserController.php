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
        $userId = $request->id;
        $isEdit = !empty($userId);

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . ($userId ?? 'NULL'),
            'password' => $isEdit ? 'nullable|string|min:6' : 'required|string|min:6',
            'role'     => 'required|in:admin,manager,staff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Siapkan data dasar
            $data = [
                'name'       => $request->name,
                'email'      => $request->email,
                'role'       => $request->role,
                'updated_at' => now(),
            ];

            // Jika password diisi (wajib di Create / opsional di Edit), tambahkan ke array
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($isEdit) {
                // PROSES EDIT
                DB::table('users')->where('id', $userId)->update($data);
                $message = 'User berhasil diperbarui';
                $finalId = $userId;
            } else {
                // PROSES CREATE
                $data['created_at'] = now();
                $finalId = DB::table('users')->insertGetId($data);
                $message = 'User berhasil ditambahkan';
            }

            return response()->json([
                'message' => $message,
                'id'      => $finalId
            ], $isEdit ? 200 : 201);

        } catch (\Exception $e) {
            // Tip: $e->getMessage() lebih aman untuk log daripada mengirim seluruh object exception
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
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
