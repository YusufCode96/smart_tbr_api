<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinsiMaster extends Model
{
    protected $table = 'provinsi_master';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['nama', 'is_active'];
    public function kabupaten()
    {
        return $this->hasMany(KabupatenMaster::class, 'provinsi_id');
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'provinsi_id');
    }
}