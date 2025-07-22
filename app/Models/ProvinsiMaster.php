<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinsiMaster extends Model
{
    protected $table = 'provinsi_master';
    protected $primaryKey = 'id';
    public $incrementing = false; // karena id = character(2)
    protected $keyType = 'string';
    public $timestamps = false; // tabel kamu tidak pakai timestamps

    protected $fillable = ['id', 'name'];

    public function kabupaten()
    {
        return $this->hasMany(KabupatenMaster::class, 'province_id', 'id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'provinsi_id');
    }
}