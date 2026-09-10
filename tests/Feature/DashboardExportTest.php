<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardExportTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): void
    {
        \App\Models\DemographicData::insert([
            ['nik' => '1', 'nama' => 'A', 'jenis_kelamin' => 'L', 'usia' => 35, 'status_perkawinan' => 'Kawin', 'pendidikan' => 'SMA', 'pekerjaan' => 'Petani', 'desa' => 'Paok', 'dusun' => 'Dusun Satu', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 4, 'pendapatan_per_bulan' => 3000000, 'kepemilikan_rumah' => 'Milik Sendiri', 'penerima_pkh' => 'Ya', 'latitude' => -8.63, 'longitude' => 116.47, 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '2', 'nama' => 'B', 'jenis_kelamin' => 'P', 'usia' => 40, 'status_perkawinan' => 'Belum Kawin', 'pendidikan' => 'SD', 'pekerjaan' => 'IRT', 'desa' => 'Paok', 'dusun' => 'Dusun Dua', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 5, 'pendapatan_per_bulan' => 1500000, 'kepemilikan_rumah' => 'Sewa', 'penerima_pkh' => 'Tidak', 'latitude' => -8.64, 'longitude' => 116.48, 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '3', 'nama' => 'C', 'jenis_kelamin' => 'L', 'usia' => 50, 'status_perkawinan' => 'Cerai', 'pendidikan' => 'Sarjana', 'pekerjaan' => 'Wiraswasta', 'desa' => 'Paok', 'dusun' => 'Dusun Tiga', 'kecamatan' => 'Masbagik', 'kabupaten' => 'Lombok Timur', 'jumlah_anggota_keluarga' => 3, 'pendapatan_per_bulan' => 5000000, 'kepemilikan_rumah' => 'Milik Sendiri', 'penerima_pkh' => 'Ya', 'latitude' => -8.65, 'longitude' => 116.49, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_export_csv(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.export', ['format' => 'csv'] + request()->query()));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ringkasan-profil-klaster.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_export_excel(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.export', ['format' => 'excel'] + request()->query()));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ringkasan-profil-klaster.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_export_pdf(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.export', ['format' => 'pdf'] + request()->query()));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ringkasan-profil-klaster.pdf', $response->headers->get('Content-Disposition'));
    }
}
