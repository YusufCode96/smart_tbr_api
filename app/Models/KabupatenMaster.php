<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KabupatenMaster extends Model
{
    protected $table = 'kabupaten_master';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['provinsi_id', 'nama', 'is_active'];
    public function provinsi()
    {
        return $this->belongsTo(ProvinsiMaster::class, 'provinsi_id');
    }
    public function kecamatan()
    {
        return $this->hasMany(KecamatanMaster::class, 'kabupaten_id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kabupaten_id');
    }
}