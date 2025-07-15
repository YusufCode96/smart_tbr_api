<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ProfilesController extends Controller
{
    public function list(Request $request)
    {
        try {
            $query = Profile::with(['pekerjaan', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);

            if ($request->search) {
                $query->where('nama_lengkap', 'ILIKE', "%{$request->search}%");
            }

            $profiles = $query->get();
            foreach ($profiles as $profile) {
                $photo = DB::table('global_uploads')
                    ->where('transaction_code', 'REGISTER_PHOTO')
                    ->where('uploader_id', $profile->id)
                    ->where('is_deleted', false)
                    ->orderByDesc('upload_time')
                    ->first();

                if ($photo && Storage::disk('public')->exists(str_replace('storage/', '', $photo->file_path))) {
                    $filePath = str_replace('storage/', '', $photo->file_path);
                    $fileContent = Storage::disk('public')->get($filePath);
                    $photo->file_base64 = 'data:' . $photo->file_type . ';base64,' . base64_encode($fileContent);
                }

                $profile->photo = $photo;
            }

            return response()->json(['status' => 200, 'message' => 'Daftar profile berhasil diambil', 'data' => $profiles]);
        } catch (Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil daftar profile', 'data' => $e->getMessage()], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $profile = Profile::with(['pekerjaan', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])->find($request->id);
            if (!$profile) return response()->json(['status' => 404, 'message' => 'Profile tidak ditemukan', 'data' => null], 404);

            $photo = DB::table('global_uploads')
                ->where('transaction_code', 'REGISTER_PHOTO')
                ->where('uploader_id', $profile->id)
                ->where('is_deleted', false)
                ->orderByDesc('upload_time')
                ->first();

            if ($photo && Storage::disk('public')->exists(str_replace('storage/', '', $photo->file_path))) {
                $filePath = str_replace('storage/', '', $photo->file_path);
                $fileContent = Storage::disk('public')->get($filePath);
                $photo->file_base64 = 'data:' . $photo->file_type . ';base64,' . base64_encode($fileContent);
            }

            $profile->photo = $photo;

            return response()->json(['status' => 200, 'message' => 'Detail profile berhasil diambil', 'data' => $profile]);
        } catch (Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil detail profile', 'data' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'pekerjaan_id' => 'required|exists:pekerjaan_master,id',
                'alamat_rumah' => 'nullable|string',
                'provinsi_id' => 'nullable|exists:provinsi_master,id',
                'kabupaten_id' => 'nullable|exists:kabupaten_master,id',
                'kecamatan_id' => 'nullable|exists:kecamatan_master,id',
                'kelurahan_id' => 'nullable|exists:kelurahan_master,id',
                'file_upload.file_base64' => 'required|string',
                'file_upload.file_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 422, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $profile = Profile::create($data);

            // Upload file
            $base64 = $data['file_upload']['file_base64'];
            $originalFileName = $data['file_upload']['file_name'];
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

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Profile berhasil ditambahkan', 'data' => $profile]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Gagal menambahkan profile', 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $profile = Profile::find($request->id);
            if (!$profile) return response()->json(['status' => 404, 'message' => 'Profile tidak ditemukan', 'data' => null], 404);

            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'sometimes|required|string|max:255',
                'tanggal_lahir' => 'sometimes|required|date',
                'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
                'pekerjaan_id' => 'sometimes|required|exists:pekerjaan_master,id',
                'alamat_rumah' => 'nullable|string',
                'provinsi_id' => 'nullable|exists:provinsi_master,id',
                'kabupaten_id' => 'nullable|exists:kabupaten_master,id',
                'kecamatan_id' => 'nullable|exists:kecamatan_master,id',
                'kelurahan_id' => 'nullable|exists:kelurahan_master,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 422, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            $profile->update($validator->validated());

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Profile berhasil diperbarui', 'data' => $profile]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Gagal memperbarui profile', 'data' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $profile = Profile::find($request->id);
            if (!$profile) return response()->json(['status' => 404, 'message' => 'Profile tidak ditemukan', 'data' => null], 404);

            // Tandai file terkait sebagai terhapus
            DB::table('global_uploads')
                ->where('transaction_code', 'REGISTER_PHOTO')
                ->where('uploader_id', $profile->id)
                ->update(['is_deleted' => true]);

            $profile->delete();
            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Profile berhasil dihapus']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Gagal menghapus profile', 'data' => $e->getMessage()], 500);
        }
    }
}
