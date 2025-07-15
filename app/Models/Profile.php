<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $casts = ['tanggal_lahir' => 'date'];
    protected $fillable = [
        'nama_lengkap', 'tanggal_lahir', 'jenis_kelamin', 'pekerjaan_id',
        'alamat_rumah', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id'
    ];
    public function pekerjaan()
    {
        return $this->belongsTo(PekerjaanMaster::class, 'pekerjaan_id');
    }
    public function provinsi()
    {
        return $this->belongsTo(ProvinsiMaster::class, 'provinsi_id');
    }
    public function kabupaten()
    {
        return $this->belongsTo(KabupatenMaster::class, 'kabupaten_id');
    }
    public function kecamatan()
    {
        return $this->belongsTo(KecamatanMaster::class, 'kecamatan_id');
    }
    public function kelurahan()
    {
        return $this->belongsTo(KelurahanMaster::class, 'kelurahan_id');
    }
}