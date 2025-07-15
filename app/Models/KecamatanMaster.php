<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KecamatanMaster extends Model
{
    protected $table = 'kecamatan_master';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['kabupaten_id', 'nama', 'is_active'];
    public function kabupaten()
    {
        return $this->belongsTo(KabupatenMaster::class, 'kabupaten_id');
    }
    public function kelurahan()
    {
        return $this->hasMany(KelurahanMaster::class, 'kecamatan_id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kecamatan_id');
    }
}