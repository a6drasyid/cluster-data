<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemographicData extends Model
{
    protected $table = 'demographic_data';

    protected $fillable = [
        'nik',
        'nama',
        'jenis_kelamin',
        'usia',
        'status_perkawinan',
        'pendidikan',
        'pekerjaan',
        'desa',
        'dusun',
        'kecamatan',
        'kabupaten',
        'jumlah_anggota_keluarga',
        'pendapatan_per_bulan',
        'kepemilikan_rumah',
        'penerima_pkh',
        'latitude',
        'longitude',
        'cluster',
    ];
}
