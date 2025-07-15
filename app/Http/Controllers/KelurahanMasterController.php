<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\KelurahanMaster;
use Exception;
use Illuminate\Support\Facades\DB;

class KelurahanMasterController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KelurahanMaster::create($request->only(['kecamatan_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kelurahan berhasil ditambahkan','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menambahkan kelurahan','data'=>$e->getMessage()],500);
        }
    }

    public function list(Request $request)
{
    try {
        $q = KelurahanMaster::with('kecamatan');

        if ($request->kecamatan_id) {
            $q->where('kecamatan_id', $request->kecamatan_id);
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
            'message' => 'Daftar kelurahan berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil daftar kelurahan',
            'data' => $e->getMessage()
        ], 500);
    }
}


    public function detail(Request $request)
    {
        try {
            $k = KelurahanMaster::find($request->id);
            if (!$k) return response()->json(['status'=>404,'message'=>'Kelurahan tidak ditemukan','data'=>null],404);
            return response()->json(['status'=>200,'message'=>'Detail kelurahan berhasil diambil','data'=>$k]);
        } catch (Exception $e) {
            return response()->json(['status'=>500,'message'=>'Gagal mengambil detail kelurahan','data'=>$e->getMessage()],500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KelurahanMaster::find($request->id);
            if (!$k) return response()->json(['status'=>404,'message'=>'Kelurahan tidak ditemukan','data'=>null],404);
            $k->update($request->only(['kecamatan_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kelurahan berhasil diperbarui','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal memperbarui kelurahan','data'=>$e->getMessage()],500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KelurahanMaster::find($request->id);
            if (!$k) return response()->json(['status'=>404,'message'=>'Kelurahan tidak ditemukan','data'=>null],404);
            $k->delete();
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kelurahan berhasil dihapus','data'=>null]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menghapus kelurahan','data'=>$e->getMessage()],500);
        }
    }
}