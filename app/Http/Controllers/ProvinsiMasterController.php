<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ProvinsiMaster;
use Exception;
use Illuminate\Support\Facades\DB;

class ProvinsiMasterController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = ProvinsiMaster::create($request->only(['nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Provinsi berhasil ditambahkan','data'=>$p]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menambahkan provinsi','data'=>$e->getMessage()],500);
        }
    }

    public function list(Request $request)
{
    try {
        $search = strtoupper($request->search); // Ubah input menjadi huruf besar
        $data = ProvinsiMaster::where(DB::raw('UPPER(nama)'), 'LIKE', '%' . $search . '%')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Daftar provinsi berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil daftar provinsi',
            'data' => $e->getMessage()
        ], 500);
    }
}

    public function detail(Request $request)
    {
        try {
            $p = ProvinsiMaster::find($request->id);
            if (!$p) {
                return response()->json(['status'=>404,'message'=>'Provinsi tidak ditemukan','data'=>null],404);
            }
            return response()->json(['status'=>200,'message'=>'Detail provinsi berhasil diambil','data'=>$p]);
        } catch (Exception $e) {
            return response()->json(['status'=>500,'message'=>'Gagal mengambil detail provinsi','data'=>$e->getMessage()],500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = ProvinsiMaster::find($request->id);
            if (!$p) {
                return response()->json(['status'=>404,'message'=>'Provinsi tidak ditemukan','data'=>null],404);
            }
            $p->update($request->only(['nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Provinsi berhasil diperbarui','data'=>$p]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal memperbarui provinsi','data'=>$e->getMessage()],500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = ProvinsiMaster::find($request->id);
            if (!$p) {
                DB::rollBack();
                return response()->json(['status'=>404,'message'=>'Provinsi tidak ditemukan','data'=>null],404);
            }
            $p->delete();
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Provinsi berhasil dihapus','data'=>null]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menghapus provinsi','data'=>$e->getMessage()],500);
        }
    }
}