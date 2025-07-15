<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama',
        'url',
        'icon',
        'urutan',
        'jenis_menu_id',
        'role_id',
        'is_aktif',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
}
