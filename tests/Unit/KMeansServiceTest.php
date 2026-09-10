<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class KMeansServiceTest extends TestCase
{
    /**
     * Test K-Means clustering and sorting by average pendapatan_per_bulan.
     */
    public function test_kmeans_clustering_and_sorting(): void
    {
        $service = new \App\Services\KMeansService();

        // 4 distinct income groups -> 4 clusters sorted by income.
        $data = [
            ['nama' => 'A1', 'pendapatan_per_bulan' => 5000000, 'usia' => 30, 'jumlah_anggota_keluarga' => 4, 'jenis_kelamin' => 'L'],
            ['nama' => 'A2', 'pendapatan_per_bulan' => 4800000, 'usia' => 31, 'jumlah_anggota_keluarga' => 3, 'jenis_kelamin' => 'P'],
            ['nama' => 'B1', 'pendapatan_per_bulan' => 3000000, 'usia' => 35, 'jumlah_anggota_keluarga' => 5, 'jenis_kelamin' => 'L'],
            ['nama' => 'B2', 'pendapatan_per_bulan' => 2800000, 'usia' => 36, 'jumlah_anggota_keluarga' => 4, 'jenis_kelamin' => 'P'],
            ['nama' => 'C1', 'pendapatan_per_bulan' => 1500000, 'usia' => 40, 'jumlah_anggota_keluarga' => 6, 'jenis_kelamin' => 'L'],
            ['nama' => 'C2', 'pendapatan_per_bulan' => 1300000, 'usia' => 42, 'jumlah_anggota_keluarga' => 7, 'jenis_kelamin' => 'P'],
            ['nama' => 'D1', 'pendapatan_per_bulan' => 500000, 'usia' => 50, 'jumlah_anggota_keluarga' => 3, 'jenis_kelamin' => 'L'],
            ['nama' => 'D2', 'pendapatan_per_bulan' => 400000, 'usia' => 55, 'jumlah_anggota_keluarga' => 2, 'jenis_kelamin' => 'P'],
        ];

        // Use only the income feature so the clustering is purely income-driven,
        // which makes the cluster-sorting-by-income assertion deterministic.
        $features = ['pendapatan_per_bulan'];

        $result = $service->cluster($data, $features, 4, 100);

        $this->assertCount(8, $result['data']);
        $this->assertCount(4, $result['stats']);

        foreach ($result['data'] as $item) {
            $this->assertGreaterThanOrEqual(1, $item['cluster']);
            $this->assertLessThanOrEqual(4, $item['cluster']);
        }

        // Priority: Cluster 1 = Prioritas Tinggi (most in need) through the last
        // cluster (Prioritas Rendah / most prosperous). With only the income feature,
        // the LOWEST-income cluster is the most in need => Cluster 1.
        $avgIncomes = [
            1 => $result['stats'][1]['avg_pendapatan'],
            2 => $result['stats'][2]['avg_pendapatan'],
            3 => $result['stats'][3]['avg_pendapatan'],
            4 => $result['stats'][4]['avg_pendapatan'],
        ];

        // Income should increase from Cluster 1 up to Cluster 4.
        $this->assertLessThan($avgIncomes[2], $avgIncomes[1]);
        $this->assertLessThan($avgIncomes[3], $avgIncomes[2]);
        $this->assertLessThan($avgIncomes[4], $avgIncomes[3]);

        // A1 (highest income, most prosperous) -> last cluster (4).
        $a1 = collect($result['data'])->firstWhere('nama', 'A1');
        $this->assertEquals(4, $a1['cluster']);

        // D1 (lowest income, most in need) -> Cluster 1 (Prioritas Tinggi).
        $d1 = collect($result['data'])->firstWhere('nama', 'D1');
        $this->assertEquals(1, $d1['cluster']);
    }

    /**
     * Test that the service clusters correctly when categorical features are used.
     */
    public function test_kmeans_handles_categorical_features(): void
    {
        $service = new \App\Services\KMeansService();

        $data = [
            ['nama' => 'R1', 'pendapatan_per_bulan' => 900000, 'usia' => 40, 'pendidikan' => 'SD', 'pekerjaan' => 'Tidak Bekerja', 'jenis_kelamin' => 'P', 'status_perkawinan' => 'Belum Kawin', 'kepemilikan_rumah' => 'Menumpang', 'jumlah_anggota_keluarga' => 5],
            ['nama' => 'R2', 'pendapatan_per_bulan' => 850000, 'usia' => 45, 'pendidikan' => 'Tidak Sekolah', 'pekerjaan' => 'Buruh', 'jenis_kelamin' => 'P', 'status_perkawinan' => 'Cerai', 'kepemilikan_rumah' => 'Sewa', 'jumlah_anggota_keluarga' => 6],
            ['nama' => 'S1', 'pendapatan_per_bulan' => 4000000, 'usia' => 30, 'pendidikan' => 'Sarjana', 'pekerjaan' => 'Wiraswasta', 'jenis_kelamin' => 'L', 'status_perkawinan' => 'Kawin', 'kepemilikan_rumah' => 'Milik Sendiri', 'jumlah_anggota_keluarga' => 3],
            ['nama' => 'S2', 'pendapatan_per_bulan' => 4500000, 'usia' => 32, 'pendidikan' => 'Sarjana', 'pekerjaan' => 'PNS', 'jenis_kelamin' => 'L', 'status_perkawinan' => 'Kawin', 'kepemilikan_rumah' => 'Milik Sendiri', 'jumlah_anggota_keluarga' => 2],
        ];

        $features = ['pendapatan_per_bulan', 'pendidikan', 'pekerjaan', 'jenis_kelamin', 'usia', 'status_perkawinan', 'kepemilikan_rumah', 'jumlah_anggota_keluarga'];

        $result = $service->cluster($data, $features, 2, 50);

        $this->assertCount(4, $result['data']);
        $this->assertCount(2, $result['stats']);

        foreach ($result['data'] as $item) {
            $this->assertNotNull($item['cluster']);
            $this->assertGreaterThanOrEqual(1, $item['cluster']);
            $this->assertLessThanOrEqual(2, $item['cluster']);
        }

        // The two high-income records should land together in the highest cluster.
        $s1 = collect($result['data'])->firstWhere('nama', 'S1');
        $s2 = collect($result['data'])->firstWhere('nama', 'S2');
        $r1 = collect($result['data'])->firstWhere('nama', 'R1');
        $this->assertEquals($s1['cluster'], $s2['cluster']);
        $this->assertNotEquals($s1['cluster'], $r1['cluster']);
    }

    /**
     * Test that gender feature works (L/P categories).
     */
    public function test_kmeans_handles_gender_feature(): void
    {
        $service = new \App\Services\KMeansService();

        $data = [
            ['nama' => 'P1', 'pendapatan_per_bulan' => 2000000, 'usia' => 30, 'jenis_kelamin' => 'L', 'jumlah_anggota_keluarga' => 4],
            ['nama' => 'P2', 'pendapatan_per_bulan' => 1500000, 'usia' => 32, 'jenis_kelamin' => 'P', 'jumlah_anggota_keluarga' => 3],
            ['nama' => 'P3', 'pendapatan_per_bulan' => 3000000, 'usia' => 28, 'jenis_kelamin' => 'L', 'jumlah_anggota_keluarga' => 5],
            ['nama' => 'P4', 'pendapatan_per_bulan' => 1000000, 'usia' => 45, 'jenis_kelamin' => 'P', 'jumlah_anggota_keluarga' => 6],
        ];

        $features = ['pendapatan_per_bulan', 'usia', 'jenis_kelamin'];

        $result = $service->cluster($data, $features, 2, 50);

        $this->assertCount(4, $result['data']);
        $this->assertCount(2, $result['stats']);

        foreach ($result['data'] as $item) {
            $this->assertNotNull($item['cluster']);
        }
    }

    /**
     * Test that per-feature weights are resolved and respected.
     */
    public function test_kmeans_respects_feature_weights(): void
    {
        $service = new \App\Services\KMeansService();

        // Two clearly separated income groups.
        $data = [
            ['nama' => 'Rich1', 'pendapatan_per_bulan' => 5000000, 'usia' => 30],
            ['nama' => 'Rich2', 'pendapatan_per_bulan' => 4800000, 'usia' => 31],
            ['nama' => 'Poor1', 'pendapatan_per_bulan' => 400000, 'usia' => 50],
            ['nama' => 'Poor2', 'pendapatan_per_bulan' => 350000, 'usia' => 52],
        ];

        $features = ['pendapatan_per_bulan'];

        // No weights given -> defaults apply (all 1.0 here), still 2 clusters.
        $defaults = $service->cluster($data, $features, 2, 50);
        $this->assertCount(2, $defaults['stats']);

        // Explicit weights must be accepted (income boosted) without error.
        $weighted = $service->cluster($data, $features, 2, 50, ['pendapatan_per_bulan' => 2.5]);
        $this->assertCount(2, $weighted['stats']);

        // The rich and poor should land in different clusters.
        $rich = collect($weighted['data'])->firstWhere('nama', 'Rich1');
        $poor = collect($weighted['data'])->firstWhere('nama', 'Poor1');
        $this->assertNotEquals($rich['cluster'], $poor['cluster']);
    }

    /**
     * Test that invalid/empty weights fall back to defaults without error.
     */
    public function test_kmeans_falls_back_on_bad_weights(): void
    {
        $service = new \App\Services\KMeansService();

        $data = [
            ['nama' => 'A', 'pendapatan_per_bulan' => 3000000, 'usia' => 30],
            ['nama' => 'B', 'pendapatan_per_bulan' => 2000000, 'usia' => 40],
            ['nama' => 'C', 'pendapatan_per_bulan' => 1000000, 'usia' => 50],
        ];

        $result = $service->cluster($data, ['pendapatan_per_bulan', 'usia'], 2, 50, [
            'pendapatan_per_bulan' => 'not-a-number',
            'usia' => '',
        ]);

        $this->assertCount(3, $result['data']);
        $this->assertCount(2, $result['stats']);
    }
}
