<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KecamatanMaster extends Model
{
    protected $table = 'kecamatan_master';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'kabupaten_id', 'name'];

    public function kabupaten()
    {
        return $this->belongsTo(KabupatenMaster::class, 'kabupaten_id', 'id');
    }

    public function kelurahan()
    {
        return $this->hasMany(KelurahanMaster::class, 'kecamatan_id', 'id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'kecamatan_id');
    }
}