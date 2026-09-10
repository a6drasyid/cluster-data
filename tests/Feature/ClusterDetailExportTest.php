<?php

namespace Tests\Feature;

use App\Models\DemographicData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClusterDetailExportTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): void
    {
        // Rich enough variation that multiple clusters form and each has members.
        $rows = [];
        for ($i = 1; $i <= 12; $i++) {
            $rows[] = [
                'nik' => (string) $i,
                'nama' => 'Orang ' . $i,
                'jenis_kelamin' => $i % 2 ? 'L' : 'P',
                'usia' => 25 + $i,
                'status_perkawinan' => $i % 2 ? 'Kawin' : 'Belum Kawin',
                'pendidikan' => ['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'Diploma', 'Sarjana'][$i % 6],
                'pekerjaan' => ['Petani', 'Buruh', 'IRT', 'Wiraswasta', 'Nelayan', 'Tidak Bekerja'][$i % 6],
                'desa' => 'Paok Motong',
                'dusun' => 'Dusun ' . ($i % 3),
                'kecamatan' => 'Masbagik',
                'kabupaten' => 'Lombok Timur',
                'jumlah_anggota_keluarga' => 3 + ($i % 4),
                'pendapatan_per_bulan' => 500000 + $i * 250000,
                'kepemilikan_rumah' => ['Milik Sendiri', 'Sewa', 'Menumpang'][$i % 3],
                'penerima_pkh' => $i % 2 ? 'Ya' : 'Tidak',
                'latitude' => -8.63 + $i * 0.001,
                'longitude' => 116.47 + $i * 0.001,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DemographicData::insert($rows);
    }

    public function test_cluster_detail_page_renders(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        // Cluster 1 must exist in the results.
        $response = $this->actingAs($user)->get(route('dashboard.cluster.detail', ['id' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Data Anggota Klaster');
    }

    public function test_cluster_detail_unknown_cluster_404(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.cluster.detail', ['id' => 99]));
        $response->assertStatus(404);
    }

    public function test_cluster_export_excel(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.cluster.export', ['id' => 1, 'format' => 'excel']));
        $response->assertStatus(200);
        $this->assertStringContainsString('data-anggota-klaster-1.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_cluster_export_pdf(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.cluster.export', ['id' => 1, 'format' => 'pdf']));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('data-anggota-klaster-1.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_cluster_export_csv(): void
    {
        $this->seedData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.cluster.export', ['id' => 1, 'format' => 'csv']));
        $response->assertStatus(200);
        $this->assertStringContainsString('data-anggota-klaster-1.csv', $response->headers->get('Content-Disposition'));
    }
}
