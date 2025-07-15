<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelurahanMaster extends Model
{
    protected $table = 'kelurahan_master';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['kecamatan_id', 'nama', 'is_active'];
    public function kecamatan()
    {
        return $this->belongsTo(KecamatanMaster::class, 'kecamatan_id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kelurahan_id');
    }
}
