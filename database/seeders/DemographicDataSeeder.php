<?php

namespace Database\Seeders;

use App\Models\DemographicData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemographicDataSeeder extends Seeder
{
    /**
     * Run the database seeds with sample data penerima PKH.
     */
    public function run(): void
    {
        DB::table('demographic_data')->truncate();

        $sampleData = [
            [
                'nik' => '4355950783669724', 'nama' => 'Lina Hidayat', 'jenis_kelamin' => 'P', 'usia' => 70,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'Diploma', 'pekerjaan' => 'Petani',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Barat', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 4, 'pendapatan_per_bulan' => 3200000, 'kepemilikan_rumah' => 'Milik Sendiri',
                'penerima_pkh' => 'Ya', 'latitude' => -8.632526, 'longitude' => 116.476502,
            ],
            [
                'nik' => '5315403526933230', 'nama' => 'Yusuf Hidayat', 'jenis_kelamin' => 'L', 'usia' => 38,
                'status_perkawinan' => 'Cerai', 'pendidikan' => 'Diploma', 'pekerjaan' => 'IRT',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Tunjang Selatan', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 1, 'pendapatan_per_bulan' => 3750000, 'kepemilikan_rumah' => 'Sewa',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632111, 'longitude' => 116.464988,
            ],
            [
                'nik' => '7104007624101213', 'nama' => 'Fajar Maulana', 'jenis_kelamin' => 'L', 'usia' => 63,
                'status_perkawinan' => 'Cerai', 'pendidikan' => 'Sarjana', 'pekerjaan' => 'Wiraswasta',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Timur', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 3, 'pendapatan_per_bulan' => 300000, 'kepemilikan_rumah' => 'Menumpang',
                'penerima_pkh' => 'Ya', 'latitude' => -8.632625, 'longitude' => 116.477895,
            ],
            [
                'nik' => '2561617825994984', 'nama' => 'Dewi Pratama', 'jenis_kelamin' => 'L', 'usia' => 24,
                'status_perkawinan' => 'Cerai', 'pendidikan' => 'Diploma', 'pekerjaan' => 'Tidak Bekerja',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Dasan Malang Barat', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 2, 'pendapatan_per_bulan' => 450000, 'kepemilikan_rumah' => 'Milik Sendiri',
                'penerima_pkh' => 'Ya', 'latitude' => -8.630338, 'longitude' => 116.470239,
            ],
            [
                'nik' => '6400556672518519', 'nama' => 'Andi Maulana', 'jenis_kelamin' => 'P', 'usia' => 38,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SMP', 'pekerjaan' => 'Buruh',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Barat', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 7, 'pendapatan_per_bulan' => 4550000, 'kepemilikan_rumah' => 'Sewa',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632526, 'longitude' => 116.476502,
            ],
            [
                'nik' => '3376968763587141', 'nama' => 'Putri Susanto', 'jenis_kelamin' => 'P', 'usia' => 53,
                'status_perkawinan' => 'Cerai', 'pendidikan' => 'SMA', 'pekerjaan' => 'IRT',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Bilasundung Selatan', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 6, 'pendapatan_per_bulan' => 1100000, 'kepemilikan_rumah' => 'Sewa',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.624632, 'longitude' => 116.460635,
            ],
            [
                'nik' => '9774025446949432', 'nama' => 'Ilham Susanto', 'jenis_kelamin' => 'P', 'usia' => 52,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SD', 'pekerjaan' => 'Tidak Bekerja',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Tunjang Utara', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 6, 'pendapatan_per_bulan' => 4900000, 'kepemilikan_rumah' => 'Sewa',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.629078, 'longitude' => 116.444743,
            ],
            [
                'nik' => '4729822803459577', 'nama' => 'Rizky Maulana', 'jenis_kelamin' => 'P', 'usia' => 58,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SD', 'pekerjaan' => 'Wiraswasta',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Barat', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 7, 'pendapatan_per_bulan' => 800000, 'kepemilikan_rumah' => 'Milik Sendiri',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632526, 'longitude' => 116.476502,
            ],
            [
                'nik' => '5517463364747249', 'nama' => 'Rudi Hidayat', 'jenis_kelamin' => 'P', 'usia' => 21,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'Tidak Sekolah', 'pekerjaan' => 'Nelayan',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Timur', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 4, 'pendapatan_per_bulan' => 550000, 'kepemilikan_rumah' => 'Menumpang',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632625, 'longitude' => 116.477895,
            ],
            [
                'nik' => '8392911797601916', 'nama' => 'Yusuf Pratama', 'jenis_kelamin' => 'P', 'usia' => 28,
                'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SD', 'pekerjaan' => 'Nelayan',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Dasan Malang Barat', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 7, 'pendapatan_per_bulan' => 2800000, 'kepemilikan_rumah' => 'Milik Sendiri',
                'penerima_pkh' => 'Ya', 'latitude' => -8.630338, 'longitude' => 116.470239,
            ],
            [
                'nik' => '7105775758858722', 'nama' => 'Rina Sari', 'jenis_kelamin' => 'L', 'usia' => 73,
                'status_perkawinan' => 'Cerai', 'pendidikan' => 'SD', 'pekerjaan' => 'Petani',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Tunjang Selatan', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 2, 'pendapatan_per_bulan' => 3200000, 'kepemilikan_rumah' => 'Milik Sendiri',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632111, 'longitude' => 116.464988,
            ],
            [
                'nik' => '8620539629907912', 'nama' => 'Andi Permata', 'jenis_kelamin' => 'L', 'usia' => 55,
                'status_perkawinan' => 'Kawin', 'pendidikan' => 'SMP', 'pekerjaan' => 'Petani',
                'desa' => 'Paok Motong', 'dusun' => 'Dusun Nenggung Timur', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 7, 'pendapatan_per_bulan' => 3700000, 'kepemilikan_rumah' => 'Menumpang',
                'penerima_pkh' => 'Tidak', 'latitude' => -8.632625, 'longitude' => 116.477895,
            ],
        ];

        foreach ($sampleData as $item) {
            DemographicData::create($item);
        }
    }
}
