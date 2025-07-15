<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisMenu extends Model
{
    protected $table = 'jenis_menu';

    protected $fillable = [
        'id',
        'kategori',
        'is_aktif',
        'created_at',
        'updated_at',
        'urutan' // pastikan field ini memang ada
    ];

    public $timestamps = true;
}
