<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KabupatenMaster extends Model
{
    protected $table = 'kabupaten_master';
    protected $primaryKey = 'id';
    public $incrementing = false; // id = character(4)
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'provinsi_id', 'name'];

    public function provinsi()
    {
        return $this->belongsTo(ProvinsiMaster::class, 'provinsi_id', 'id');
    }

    public function kecamatan()
    {
        return $this->hasMany(KecamatanMaster::class, 'kabupaten_id', 'id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kabupaten_id');
    }
}