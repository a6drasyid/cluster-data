<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_clustered_data(): void
    {
        $user = User::factory()->create();

        \App\Models\DemographicData::insert([
            ['nik' => '1', 'nama' => 'A', 'jenis_kelamin' => 'L', 'usia' => 35, 'status_perkawinan' => 'Kawin', 'pendidikan' => 'SMA', 'pekerjaan' => 'Petani', 'desa' => 'Paok', 'dusun' => 'Dusun Satu', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 4, 'pendapatan_per_bulan' => 3000000, 'kepemilikan_rumah' => 'Milik Sendiri', 'penerima_pkh' => 'Ya', 'latitude' => -8.63, 'longitude' => 116.47, 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '2', 'nama' => 'B', 'jenis_kelamin' => 'P', 'usia' => 40, 'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SD', 'pekerjaan' => 'IRT', 'desa' => 'Paok', 'dusun' => 'Dusun Dua', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 5, 'pendapatan_per_bulan' => 1500000, 'kepemilikan_rumah' => 'Sewa', 'penerima_pkh' => 'Tidak', 'latitude' => -8.64, 'longitude' => 116.48, 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '3', 'nama' => 'C', 'jenis_kelamin' => 'L', 'usia' => 50, 'status_perkawinan' => 'Cerai', 'pendidikan' => 'Sarjana', 'pekerjaan' => 'Wiraswasta', 'desa' => 'Paok', 'dusun' => 'Dusun Tiga', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 3, 'pendapatan_per_bulan' => 5000000, 'kepemilikan_rumah' => 'Milik Sendiri', 'penerima_pkh' => 'Ya', 'latitude' => -8.65, 'longitude' => 116.49, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
    }
}
