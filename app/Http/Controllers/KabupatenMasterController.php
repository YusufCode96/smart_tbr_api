<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\KabupatenMaster;
use Exception;
use Illuminate\Support\Facades\DB;

class KabupatenMasterController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KabupatenMaster::create($request->only(['provinsi_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kabupaten berhasil ditambahkan','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menambahkan kabupaten','data'=>$e->getMessage()],500);
        }
    }

public function list(Request $request)
{
    try {
        $q = KabupatenMaster::with('provinsi');

        if ($request->provinsi_id) {
            $q->where('provinsi_id', $request->provinsi_id);
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
            'message' => 'Daftar kabupaten berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil daftar kabupaten',
            'data' => $e->getMessage()
        ], 500);
    }
}



    public function detail(Request $request)
    {
        try {
            $k = KabupatenMaster::find($request->id);
            if (!$k) {
                return response()->json(['status'=>404,'message'=>'Kabupaten tidak ditemukan','data'=>null],404);
            }
            return response()->json(['status'=>200,'message'=>'Detail kabupaten berhasil diambil','data'=>$k]);
        } catch (Exception $e) {
            return response()->json(['status'=>500,'message'=>'Gagal mengambil detail kabupaten','data'=>$e->getMessage()],500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KabupatenMaster::find($request->id);
            if (!$k) {
                return response()->json(['status'=>404,'message'=>'Kabupaten tidak ditemukan','data'=>null],404);
            }
            $k->update($request->only(['provinsi_id','nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kabupaten berhasil diperbarui','data'=>$k]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal memperbarui kabupaten','data'=>$e->getMessage()],500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $k = KabupatenMaster::find($request->id);
            if (!$k) {
                DB::rollBack();
                return response()->json(['status'=>404,'message'=>'Kabupaten tidak ditemukan','data'=>null],404);
            }
            $k->delete();
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Kabupaten berhasil dihapus','data'=>null]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menghapus kabupaten','data'=>$e->getMessage()],500);
        }
    }
}