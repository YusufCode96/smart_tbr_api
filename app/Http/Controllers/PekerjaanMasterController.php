<?php

// File: app/Http/Controllers/PekerjaanMasterController.php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PekerjaanMaster;
use Exception;
use Illuminate\Support\Facades\DB;

class PekerjaanMasterController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = PekerjaanMaster::create($request->only(['nama', 'is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Pekerjaan berhasil ditambahkan','data'=>$p]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menambahkan pekerjaan','data'=>$e->getMessage()],500);
        }
    }

    public function list(Request $request)
{
    try {
        $search = strtoupper($request->search); // ubah input ke uppercase
        $data = PekerjaanMaster::where(DB::raw('UPPER(nama)'), 'LIKE', '%' . $search . '%')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Daftar pekerjaan berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil daftar pekerjaan',
            'data' => $e->getMessage()
        ], 500);
    }
}

    public function detail(Request $request)
    {
        try {
            $p = PekerjaanMaster::find($request->id);
            if (!$p) {
                return response()->json(['status'=>404,'message'=>'Pekerjaan tidak ditemukan','data'=>null],404);
            }
            return response()->json(['status'=>200,'message'=>'Detail pekerjaan berhasil diambil','data'=>$p]);
        } catch (Exception $e) {
            return response()->json(['status'=>500,'message'=>'Gagal mengambil detail pekerjaan','data'=>$e->getMessage()],500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = PekerjaanMaster::find($request->id);
            if (!$p) {
                return response()->json(['status'=>404,'message'=>'Pekerjaan tidak ditemukan','data'=>null],404);
            }
            $p->update($request->only(['nama','is_active']));
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Pekerjaan berhasil diperbarui','data'=>$p]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal memperbarui pekerjaan','data'=>$e->getMessage()],500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $p = PekerjaanMaster::find($request->id);
            if (!$p) {
                DB::rollBack();
                return response()->json(['status'=>404,'message'=>'Pekerjaan tidak ditemukan','data'=>null],404);
            }
            $p->delete();
            DB::commit();
            return response()->json(['status'=>200,'message'=>'Pekerjaan berhasil dihapus','data'=>null]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>500,'message'=>'Gagal menghapus pekerjaan','data'=>$e->getMessage()],500);
        }
    }
}