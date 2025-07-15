<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\KecamatanMaster;
use Exception;
use Illuminate\Support\Facades\DB;

class KecamatanMasterController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KecamatanMaster::create($request->only(['kabupaten_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kecamatan berhasil ditambahkan','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menambahkan kecamatan','data'=>$e->getMessage()],500);
        }
    }

    public function list(Request $request)
{
    try {
        $q = KecamatanMaster::with('kabupaten');

        if ($request->kabupaten_id) {
            $q->where('kabupaten_id', $request->kabupaten_id);
        }

        if ($request->search) {
            $search = strtoupper($request->search);
            $q->where(function ($query) use ($search) {
                $query->where(DB::raw('UPPER(nama)'), 'LIKE', '%' . $search . '%');
            });
        }

        $data = $q->get();

        return response()->json([
            'status' => 200,
            'message' => 'Daftar kecamatan berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil daftar kecamatan',
            'data' => $e->getMessage()
        ], 500);
    }
}


    public function detail(Request $request)
    {
        try {
            $k = KecamatanMaster::find($request->id);
            if (!$k) {
                return response()->json(['status'=>404,'message'=>'Kecamatan tidak ditemukan','data'=>null],404);
            }
            return response()->json(['status'=>200,'message'=>'Detail kecamatan berhasil diambil','data'=>$k]);
        } catch (Exception $e) {
            return response()->json(['status'=>500,'message'=>'Gagal mengambil detail kecamatan','data'=>$e->getMessage()],500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KecamatanMaster::find($request->id);
            if (!$k) {
                return response()->json(['status'=>404,'message'=>'Kecamatan tidak ditemukan','data'=>null],404);
            }
            $k->update($request->only(['kabupaten_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kecamatan berhasil diperbarui','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal memperbarui kecamatan','data'=>$e->getMessage()],500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KecamatanMaster::find($request->id);
            if (!$k) {
                DB::rollBack();
                return response()->json(['status'=>404,'message'=>'Kecamatan tidak ditemukan','data'=>null],404);
            }
            $k->delete();
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kecamatan berhasil dihapus','data'=>null]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menghapus kecamatan','data'=>$e->getMessage()],500);
        }
    }
}