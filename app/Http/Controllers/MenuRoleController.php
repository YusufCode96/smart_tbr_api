<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMenu;
use App\Models\Menu;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuRoleController extends Controller
{
    /**
     * List menus grouped by kategori, filtered by role_id
     */
    public function listByRole(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validasi gagal',
                'data'    => $validator->errors()
            ], 422);
        }

        $roleId = $request->input('role_id');

        // Ambil kategori aktif
        $jenisList = JenisMenu::where('is_aktif', true)
            ->get(['id', 'kategori']);

        // Ambil menu sesuai role dari pivot role_menus
        $menus = Menu::select('menu.id', 'menu.nama', 'menu.url', 'menu.icon', 'menu.urutan', 'menu.jenis_menu_id')
            ->join('role_menus', 'role_menus.menu_id', '=', 'menu.id')
            ->where('role_menus.role_id', $roleId)
            ->where('menu.is_aktif', true)
            ->orderBy('menu.urutan')
            ->get();

        // Grouping by kategori
        $grouped = [];
        foreach ($jenisList as $jenis) {
            $grouped[$jenis->kategori] = $menus
                ->where('jenis_menu_id', $jenis->id)
                ->values()
                ->map(fn($m) => [
                    'id'     => $m->id,
                    'nama'   => $m->nama,
                    'url'    => $m->url,
                    'icon'   => $m->icon,
                    'urutan' => $m->urutan,
                ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Daftar menu berhasil diambil',
            'data'    => $grouped
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 500,
            'message' => 'Gagal mengambil daftar menu',
            'data'    => $e->getMessage()
        ], 500);
    }
}

}
