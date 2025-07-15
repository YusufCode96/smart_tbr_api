<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function create(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'profile_id' => 'required|integer',
                'role_id' => 'required|integer',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'role_id' => $request->role_id,
                'is_active' => true
            ]);

            return response()->json([
                'status' => 201,
                'message' => 'User berhasil dibuat',
                'data' => $user
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal membuat user',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
{
    try {
        // Ambil input dari body JSON
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Validasi: kedua tanggal harus diisi
        if (!$startDate || !$endDate) {
            return response()->json([
                'status' => 400,
                'message' => 'start_date dan end_date wajib diisi.',
                'data' => null
            ], 400);
        }

        // Validasi format tanggal (optional tapi disarankan)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return response()->json([
                'status' => 400,
                'message' => 'Format tanggal harus YYYY-MM-DD.',
                'data' => null
            ], 400);
        }

        $query = User::query();

        // Filter berdasarkan nama/email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan range tanggal created_at
        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $users = $query->get();

        return response()->json([
            'status' => 200,
            'message' => 'Daftar user berhasil diambil',
            'data' => $users
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil data user',
            'data' => $e->getMessage()
        ], 500);
    }
}



    public function detail(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer'
            ]);

            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Detail user ditemukan',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer',
                'email' => 'email|unique:users,email,' . $request->id,
                'password' => 'nullable|min:6',
                'profile_id' => 'integer',
                'role_id' => 'integer',
                'is_active' => 'boolean'
            ]);

            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $user->email = $request->email ?? $user->email;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->profile_id = $request->profile_id ?? $user->profile_id;
            $user->role_id = $request->role_id ?? $user->role_id;
            $user->is_active = $request->is_active ?? $user->is_active;
            $user->save();

            return response()->json([
                'status' => 200,
                'message' => 'User berhasil diperbarui',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui user',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer'
            ]);

            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $user->delete();

            return response()->json([
                'status' => 200,
                'message' => 'User berhasil dihapus',
                'data' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus user',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
