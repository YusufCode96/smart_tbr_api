<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelurahanMaster extends Model
{
    protected $table = 'kelurahan_master';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'kecamatan_id', 'name'];

    public function kecamatan()
    {
        return $this->belongsTo(KecamatanMaster::class, 'kecamatan_id', 'id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kelurahan_id');
    }
}
