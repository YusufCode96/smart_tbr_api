<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PekerjaanMaster extends Model
{
    protected $table = 'pekerjaan_master';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['nama', 'is_active'];
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'pekerjaan_id');
    }
}
