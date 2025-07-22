<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;

class AuthController extends Controller
{
    // Login & generate token
   
        public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 401,
                'message' => 'gagal Login',
                'data' => null
            ], 401);
        }
        $user = Auth::user()->load('role');
        return response()->json([
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user 
        ]);
    }

    // Get user from token
    public function me()
    {
        return response()->json(Auth::user());
    }

    // Logout (invalidate token)
    public function logout()
    {
        Auth::logout();
         return response()->json([
        'status' => 200,
        'message' => 'Anda Berhasil logout',
        'data' => null ], 200);
    }


public function register(Request $request)
{
    DB::beginTransaction();

    try {
        // Validasi user
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            // Profile sebagai nested array
            'profile.nama_lengkap'  => 'required|string|max:255',
            'profile.tanggal_lahir' => 'required|date',
            'profile.jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'profile.pekerjaan_id'  => 'required|exists:pekerjaan_master,id',
            'profile.alamat_rumah'  => 'nullable|string',
            'profile.provinsi_id'   => 'nullable|exists:provinsi_master,id',
            'profile.kabupaten_id'  => 'nullable|exists:kabupaten_master,id',
            'profile.kecamatan_id'  => 'nullable|exists:kecamatan_master,id',
            'profile.kelurahan_id'  => 'nullable|exists:kelurahan_master,id',

            // Upload file (opsional)
            'file_upload.file_base64' => 'nullable|string',
            'file_upload.file_name' => 'required_with:file_upload.file_base64|string',
            'file_upload.category' => 'nullable|string',
            'file_upload.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simpan data profile
        $profileData = $request->input('profile');
        $profile = Profile::create($profileData);

        // Simpan data user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_id' => $profile->id,
            'role_id' => 4,//pasien
            'is_active' => true
        ]);

        // Proses upload jika ada
        if ($request->filled('file_upload.file_base64')) {
            $base64 = $request->input('file_upload.file_base64');
            $originalFileName = $request->input('file_upload.file_name');

            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            $fileType = null;

            // Ambil MIME dari base64
            if (preg_match('/^data:(.*?);base64,/', $base64, $matches)) {
                $fileType = $matches[1];
                $base64 = preg_replace('/^data:.*?;base64,/', '', $base64);
            }

            $base64 = str_replace(' ', '+', $base64);
            $binaryData = base64_decode($base64);

            $fileNameToStore = Str::uuid() . '.' . $extension;
            $filePath = 'uploads/' . $fileNameToStore;

            Storage::disk('public')->put($filePath, $binaryData);

            // Simpan metadata upload
            DB::table('global_uploads')->insert([
                'transaction_code' => 'REGISTER_PHOTO',
                'file_name' => $originalFileName,
                'file_path' => 'storage/' . $filePath,
                'file_type' => $fileType,
                'file_size' => strlen($binaryData),
                'uploader_id' => $profile->id,
                'upload_time' => Carbon::now(),
                'category' => $request->input('file_upload.category', 'TRANSAKSI'),
                'description' => $request->input('file_upload.description', 'Profile user'),
                'is_deleted' => false
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => 201,
            'message' => 'User dan Profile berhasil dibuat',
            'data' => [
                'user' => $user,
                'profile' => $profile
            ]
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Terjadi kesalahan saat pendaftaran',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateRegister(Request $request)
{
    DB::beginTransaction();

    try {
        // Ambil data user & profile
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);
        $profileId = $request->input('profile.id');
        $profile = Profile::findOrFail($profileId);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',

            // Validasi profile
            'profile.id' => 'required|exists:profiles,id',
            'profile.nama_lengkap'  => 'required|string|max:255',
            'profile.tanggal_lahir' => 'required|date',
            'profile.jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'profile.pekerjaan_id'  => 'required|exists:pekerjaan_master,id',
            'profile.alamat_rumah'  => 'nullable|string',
            'profile.provinsi_id'   => 'nullable|exists:provinsi_master,id',
            'profile.kabupaten_id'  => 'nullable|exists:kabupaten_master,id',
            'profile.kecamatan_id'  => 'nullable|exists:kecamatan_master,id',
            'profile.kelurahan_id'  => 'nullable|exists:kelurahan_master,id',

            // Upload file (opsional)
            'file_upload.file_base64' => 'nullable|string',
            'file_upload.file_name' => 'required_with:file_upload.file_base64|string',
            'file_upload.id' => 'nullable|integer|exists:global_uploads,id',
            'file_upload.category' => 'nullable|string',
            'file_upload.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update Profile
        $profileData = $request->input('profile');
        $profile->update($profileData);

        // Update User
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Update/Insert file upload jika ada base64
        if ($request->filled('file_upload.file_base64')) {
            $base64 = $request->input('file_upload.file_base64');
            $originalFileName = $request->input('file_upload.file_name');
            $fileId = $request->input('file_upload.id');
            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);

            $fileType = null;
            if (preg_match('/^data:(.*?);base64,/', $base64, $matches)) {
                $fileType = $matches[1];
                $base64 = preg_replace('/^data:.*?;base64,/', '', $base64);
            }

            $base64 = str_replace(' ', '+', $base64);
            $binaryData = base64_decode($base64);

            $fileNameToStore = Str::uuid() . '.' . $extension;
            $filePath = 'uploads/' . $fileNameToStore;
            Storage::disk('public')->put($filePath, $binaryData);

            if ($fileId) {
                // Update metadata file lama
                DB::table('global_uploads')->where('id', $fileId)->update([
                    'file_name' => $originalFileName,
                    'file_path' => 'storage/' . $filePath,
                    'file_type' => $fileType,
                    'file_size' => strlen($binaryData),
                    'category' => $request->input('file_upload.category', 'TRANSAKSI'),
                    'description' => $request->input('file_upload.description', 'Update profile user'),
                    'upload_time' => Carbon::now(),
                    'is_deleted' => false,
                ]);
            } else {
                // Insert metadata baru
                DB::table('global_uploads')->insert([
                    'transaction_code' => 'REGISTER_PHOTO',
                    'file_name' => $originalFileName,
                    'file_path' => 'storage/' . $filePath,
                    'file_type' => $fileType,
                    'file_size' => strlen($binaryData),
                    'uploader_id' => $profile->id,
                    'upload_time' => Carbon::now(),
                    'category' => $request->input('file_upload.category', 'TRANSAKSI'),
                    'description' => $request->input('file_upload.description', 'Profile user'),
                    'is_deleted' => false
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'User dan Profile berhasil diperbarui',
            'data' => [
                'user' => $user,
                'profile' => $profile
            ]
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Terjadi kesalahan saat update data',
            'error' => $e->getMessage()
        ], 500);
    }
}


      public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(['token' => $newToken]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    public function requestOtp(Request $request)
{
    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Email tidak ditemukan',
                'data' => null
            ], 404);
        }

        $otp = rand(100000, 999999);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'created_at' => Carbon::now()
        ]);

        Mail::raw("Kode OTP Anda untuk reset password adalah: $otp", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('OTP Reset Password - Smart TB-R');
        });

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'OTP berhasil dikirim ke email',
            'data' => null
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengirim OTP',
            'data' => $e->getMessage()
        ], 500);
    }
}

public function checkOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp'   => 'required|digits:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'message' => 'Validasi gagal',
            'data' => $validator->errors()
        ], 422);
    }

    $latestOtp = DB::table('password_resets')
        ->where('email', $request->email)
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$latestOtp || $latestOtp->otp !== $request->otp) {
        return response()->json([
            'status'  => 400,
            'message' => 'OTP salah atau tidak ditemukan',
            'data'    => null
        ], 400);
    }

    if (Carbon::now()->diffInMinutes($latestOtp->created_at) > 10) {
        return response()->json([
            'status'  => 400,
            'message' => 'OTP telah kadaluarsa',
            'data'    => null
        ], 400);
    }

    return response()->json([
        'status'  => 200,
        'message' => 'OTP valid',
        'data'    => null
    ]);
}


public function resetPassword(Request $request)
{
    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            DB::rollBack();
            return response()->json([
                'status'  => 404,
                'message' => 'User tidak ditemukan',
                'data'    => null
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Password berhasil direset',
            'data'    => $user
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 500,
            'message' => 'Gagal reset password',
            'data'    => $e->getMessage()
        ], 500);
    }
}



}
