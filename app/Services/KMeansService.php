<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * K-Means clustering service that supports both numeric and categorical features.
 *
 * Categorical features (pendidikan, pekerjaan, jenis_kelamin, status_perkawinan,
 * kepemilikan_rumah) are encoded into numeric values so the algorithm can compute
 * Euclidean distances. All features are then min-max normalized to [0,1].
 */
class KMeansService
{
    /**
     * Mapping of every categorical label we support -> numeric score.
     * Used to convert text columns into numbers for the K-Means distance.
     */
    private const CATEGORY_MAP = [
        'jenis_kelamin' => [
            'P' => 0, 'PEREMPUAN' => 0,
            'L' => 1, 'LAKI-LAKI' => 1, 'LAKILAKI' => 1,
        ],
        'pendidikan' => [
            'TIDAK SEKOLAH' => 0,
            'SD' => 1,
            'SMP' => 2,
            'SMA' => 3, 'SMK' => 3,
            'DIPLOMA' => 4,
            'SARJANA' => 5, 'S1' => 5, 'S2' => 5, 'S3' => 5,
        ],
        'status_perkawinan' => [
            'BELUM KAWIN' => 0,
            'KAWIN' => 1,
            'CERAI' => 2,
            'JANDA' => 2, 'DUDA' => 2,
        ],
        'pekerjaan' => [
            'TIDAK BEKERJA' => 0,
            'IRT' => 1,
            'PELAJAR' => 1, 'MAHASISWA' => 1,
            'PETANI' => 2, 'NELAYAN' => 2, 'BURUH' => 2,
            'WIRASWASTA' => 3, 'PEDAGANG' => 3,
            'PNS' => 4, 'PEGAWAI' => 4, 'KARYAWAN' => 4,
        ],
        'kepemilikan_rumah' => [
            'MENUMPANG' => 0,
            'SEWA' => 1, 'KONTRAK' => 1, 'SEWA/KONTRAK' => 1,
            'MILIK SENDIRI' => 2, 'MILIK ORANG TUA' => 2,
            'LAINNYA' => 1,
        ],
    ];

    /**
     * Default score when a categorical value is unknown.
     */
    private const DEFAULT_CATEGORY_SCORE = 0;

    /**
     * Default weight per feature used when computing distances.
     *
     * Each indicator's normalized (0..1) value is multiplied by its weight before
     * the Euclidean distance / centroids are computed, so a higher weight makes an
     * indicator influence the clusters more, and a lower weight reduces it.
     *
     * Gender gets a smaller default (0.4) because it is a binary variable that can
     * otherwise dominate the distance over the other, more nuanced indicators.
     */
    private const DEFAULT_WEIGHTS = [
        'pendidikan'            => 1.0,
        'pekerjaan'             => 1.0,
        'pendapatan_per_bulan'  => 1.0,
        'jumlah_anggota_keluarga' => 1.0,
        'jenis_kelamin'         => 0.4,
        'usia'                  => 1.0,
        'status_perkawinan'     => 1.0,
        'kepemilikan_rumah'     => 1.0,
    ];

    /**
     * Run K-Means Clustering on a set of demographic records.
     *
     * @param Collection|array $data demographic records
     * @param array $features selected feature keys (e.g. ['pendidikan','pekerjaan','pendapatan_per_bulan'])
     * @param int $k number of clusters
     * @param int $maxIterations max iterations
     * @param array $weights optional per-feature weights (key => weight). Falls back to defaults.
     * @return array { data, stats, iterations }
     */
    public function cluster($data, array $features, int $k = 3, int $maxIterations = 100, array $weights = []): array
    {
        if (empty($data) || count($data) < $k) {
            return [
                'data' => is_array($data) ? $data : $data->toArray(),
                'centroids' => [],
                'stats' => [],
                'iterations' => 0,
            ];
        }

        $records = is_array($data) ? $data : $data->toArray();
        $records = array_values($records);

        // Resolve a weight for every selected feature.
        $resolvedWeights = [];
        foreach ($features as $feature) {
            $resolvedWeights[$feature] = $this->resolveWeight($weights[$feature] ?? null, self::DEFAULT_WEIGHTS[$feature] ?? 1.0);
        }

        // 1. Convert selected features into a normalized (and weighted) matrix.
        $matrix = [];
        $minMax = $this->buildFeatureMatrix($records, $features);

        foreach ($records as $index => $record) {
            $normPoint = [];
            foreach ($features as $feature) {
                $val = $this->featureValue($record, $feature);
                $min = $minMax[$feature]['min'];
                $max = $minMax[$feature]['max'];
                $denom = $max - $min;
                $normalized = $denom == 0 ? 0.5 : ($val - $min) / $denom;
                // Apply the feature weight so important indicators influence the
                // Euclidean distance more while low-weight ones (e.g. gender) matter less.
                $normPoint[$feature] = $normalized * $resolvedWeights[$feature];
            }
            $matrix[$index] = $normPoint;
        }

        // 2. Initialize centroids (K-Means++ farthest-point strategy).
        $centroids = $this->initializeCentroids($matrix, $features, $k);

        // 3. Iterate: assign + recompute until convergence.
        $assignments = [];
        $iterations = 0;
        $converged = false;

        while ($iterations < $maxIterations && !$converged) {
            $newAssignments = [];
            for ($i = 0; $i < $k; $i++) {
                $newAssignments[$i] = [];
            }

            foreach ($matrix as $index => $point) {
                $closest = $this->findClosestCentroid($point, $centroids, $features);
                $newAssignments[$closest][] = $index;
            }

            $newCentroids = [];
            for ($i = 0; $i < $k; $i++) {
                if (empty($newAssignments[$i])) {
                    // Re-seed empty cluster with the farthest point.
                    $farthestIdx = $this->farthestFromExisting($matrix, $newCentroids, $features);
                    $newCentroids[$i] = $matrix[$farthestIdx];
                } else {
                    $newCentroids[$i] = $this->calculateMean($newAssignments[$i], $matrix, $features);
                }
            }

            $diff = 0;
            for ($i = 0; $i < $k; $i++) {
                $diff += $this->euclideanDistance($centroids[$i], $newCentroids[$i], $features);
            }

            $centroids = $newCentroids;
            $assignments = $newAssignments;

            if ($diff < 0.0001) {
                $converged = true;
            }
            $iterations++;
        }

        // 4. Determine cluster priority from ALL selected indicators.
        //
        // We compute a "need score" (0..1) per cluster. A HIGHER need score means the
        // cluster is MORE in need of aid. The cluster with the HIGHEST need becomes
        // Cluster 1 (Prioritas Tinggi) and the lowest-need cluster becomes the last
        // one (Prioritas Rendah). Two directions are used:
        //  - "prosperity" indicators (income, education, job, home ownership): higher
        //    value = more prosperous = LOWER need  => need contribution = (1 - norm).
        //  - "burden" indicators (age, family size): higher value = MORE need
        //    => need contribution = norm.
        // Gender / marital status have no clear economic direction, so they are kept
        // as clustering features but excluded from the priority ordering.
        $prosperityFeatures = ['pendapatan_per_bulan', 'pendidikan', 'pekerjaan', 'kepemilikan_rumah'];

        $needScores = [];
        for ($i = 0; $i < $k; $i++) {
            $assigned = $assignments[$i] ?? [];
            if (empty($assigned)) {
                $needScores[$i] = 0;
                continue;
            }

            // Sum the raw values for every feature across the cluster's members.
            $featureSums = [];
            foreach ($features as $feature) {
                $featureSums[$feature] = 0;
            }
            foreach ($assigned as $idx) {
                foreach ($features as $feature) {
                    $featureSums[$feature] += $this->featureValue($records[$idx], $feature);
                }
            }

            $scoreSum = 0;
            $contrib = 0;
            foreach ($features as $feature) {
                // Skip gender / marital status for the priority ordering.
                if (!in_array($feature, $prosperityFeatures, true) && !in_array($feature, ['usia', 'jumlah_anggota_keluarga'], true)) {
                    continue;
                }
                $avgRaw = $featureSums[$feature] / count($assigned);
                $min = $minMax[$feature]['min'];
                $max = $minMax[$feature]['max'];
                $denom = $max - $min;
                $norm = $denom == 0 ? 0.5 : ($avgRaw - $min) / $denom;
                // Prosperity indicators: high value = low need. Burden: high value = high need.
                $need = in_array($feature, $prosperityFeatures, true) ? (1 - $norm) : $norm;
                $scoreSum += $need;
                $contrib++;
            }
            $needScores[$i] = $contrib > 0 ? $scoreSum / $contrib : 0;
        }

        // Highest need (most in need of aid) => Cluster 1 (Prioritas Tinggi);
        // lowest need (most prosperous) => last cluster (Prioritas Rendah).
        arsort($needScores);
        $clusterMapping = [];
        $newNum = 1;
        foreach ($needScores as $old => $score) {
            $clusterMapping[$old] = $newNum;
            $newNum++;
        }

        // 5. Label records + build cluster stats.
        $clusteredRecords = [];
        $clusterStats = $this->initStats($k);

        foreach ($assignments as $oldIdx => $indices) {
            $newCluster = $clusterMapping[$oldIdx] ?? ($oldIdx + 1);
            foreach ($indices as $idx) {
                $records[$idx]['cluster'] = $newCluster;
                $clusteredRecords[$idx] = $records[$idx];
                $this->accumulateStat($clusterStats[$newCluster], $records[$idx]);
            }
        }

        // 6. Finalize averages / percentages per cluster.
        foreach ($clusterStats as $c => &$stat) {
            if ($stat['count'] > 0) {
                $stat['avg_usia'] = round($stat['sum_usia'] / $stat['count'], 1);
                $stat['avg_pendapatan'] = round($stat['sum_pendapatan'] / $stat['count'], 0);
                $stat['avg_keluarga'] = round($stat['sum_keluarga'] / $stat['count'], 1);
                $totalGender = $stat['sum_jumlah_laki'] + $stat['sum_jumlah_perempuan'];
                $stat['persen_laki'] = $totalGender > 0
                    ? round(($stat['sum_jumlah_laki'] / $totalGender) * 100, 1)
                    : 0;
            }
        }
        unset($stat);

        ksort($clusteredRecords);

        return [
            'data' => array_values($clusteredRecords),
            'stats' => $clusterStats,
            'iterations' => $iterations,
        ];
    }

    /**
     * Resolve a numeric score for a given feature/record.
     */
    private function featureValue(array $record, string $feature): float
    {
        if (isset(self::CATEGORY_MAP[$feature])) {
            return $this->encodeCategory((string)($record[$feature] ?? ''), self::CATEGORY_MAP[$feature]);
        }

        $raw = $record[$feature] ?? 0;
        if (is_numeric($raw)) {
            return (float) $raw;
        }
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)$raw));
        return is_numeric($clean) && $clean !== '' ? (float) $clean : 0.0;
    }

    /**
     * Encode a categorical label into an ordinal numeric score (0..n).
     */
    private function encodeCategory(string $value, array $map): float
    {
        $upper = strtoupper(trim($value));
        if ($upper === '') {
            return self::DEFAULT_CATEGORY_SCORE;
        }
        // Longest-key-first so "TIDAK SEKOLAH" matches before "SD" etc.
        foreach ($map as $label => $score) {
            if ($upper === $label) {
                return (float) $score;
            }
        }
        // Fallback: try substring match (e.g. "DIPLOMA III", "SMA/SMK").
        $bestLen = 0;
        $bestScore = self::DEFAULT_CATEGORY_SCORE;
        foreach ($map as $label => $score) {
            if ($label !== '' && str_contains($upper, $label) && strlen($label) > $bestLen) {
                $bestLen = strlen($label);
                $bestScore = (float) $score;
            }
        }
        return $bestScore;
    }

    /**
     * Build per-feature min/max over the encoded numeric values.
     */
    private function buildFeatureMatrix(array $records, array $features): array
    {
        $minMax = [];
        foreach ($features as $feature) {
            $values = [];
            foreach ($records as $record) {
                $values[] = $this->featureValue($record, $feature);
            }
            if (empty($values)) {
                $minMax[$feature] = ['min' => 0, 'max' => 1];
            } else {
                $minMax[$feature] = [
                    'min' => (float) min($values),
                    'max' => (float) max($values),
                ];
            }
        }
        return $minMax;
    }

    /**
     * Initialize centroids using a farthest-point strategy (K-Means++ variant).
     */
    private function initializeCentroids(array $matrix, array $features, int $k): array
    {
        $centroids = [];
        $keys = array_keys($matrix);
        if (empty($keys)) {
            return [];
        }

        $centroids[] = $matrix[$keys[0]];

        for ($c = 1; $c < $k; $c++) {
            $farthestIdx = null;
            $maxDist = -1.0;
            foreach ($matrix as $index => $point) {
                $minDist = INF;
                foreach ($centroids as $centroid) {
                    $d = $this->euclideanDistance($point, $centroid, $features);
                    if ($d < $minDist) {
                        $minDist = $d;
                    }
                }
                if ($minDist > $maxDist) {
                    $maxDist = $minDist;
                    $farthestIdx = $index;
                }
            }
            $centroids[] = $farthestIdx !== null ? $matrix[$farthestIdx] : $matrix[$keys[0]];
        }

        return $centroids;
    }

    /**
     * Return the index of the point farthest from every current centroid (seed helper).
     */
    private function farthestFromExisting(array $matrix, array $centroids, array $features): int
    {
        $farthestIdx = 0;
        $maxDist = -1.0;
        foreach ($matrix as $index => $point) {
            $minDist = INF;
            foreach ($centroids as $centroid) {
                $d = $this->euclideanDistance($point, $centroid, $features);
                if ($d < $minDist) {
                    $minDist = $d;
                }
            }
            if ($minDist > $maxDist) {
                $maxDist = $minDist;
                $farthestIdx = $index;
            }
        }
        return $farthestIdx;
    }

    /**
     * Find the nearest centroid index for a given point.
     */
    private function findClosestCentroid(array $point, array $centroids, array $features): int
    {
        if (empty($centroids)) {
            return 0;
        }
        $minDist = INF;
        $closestIdx = 0;
        foreach ($centroids as $idx => $centroid) {
            $dist = $this->euclideanDistance($point, $centroid, $features);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestIdx = $idx;
            }
        }
        return $closestIdx;
    }

    /**
     * Euclidean distance between two normalized points.
     */
    private function euclideanDistance(array $p1, array $p2, array $features): float
    {
        $sum = 0;
        foreach ($features as $feature) {
            $diff = ($p1[$feature] ?? 0) - ($p2[$feature] ?? 0);
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Validate and sanitize a feature weight.
     *
     * @param mixed $requested value from the user (or null)
     * @param float $default fallback when not provided / invalid
     */
    /**
     * Return the default per-feature weights (used when the user does not override).
     *
     * @return array<string, float>
     */
    public function defaultWeights(): array
    {
        return self::DEFAULT_WEIGHTS;
    }

    private function resolveWeight($requested, float $default): float
    {
        if ($requested === null || $requested === '') {
            return $default;
        }
        if (!is_numeric($requested)) {
            return $default;
        }
        $w = (float) $requested;
        // Keep weights in a sensible positive range (min 0.1).
        return max(0.1, min(10.0, $w));
    }

    /**
     * Compute the mean of a set of normalized points -> new centroid.
     */
    private function calculateMean(array $indices, array $matrix, array $features): array
    {
        $mean = [];
        if (empty($indices)) {
            foreach ($features as $feature) {
                $mean[$feature] = 0;
            }
            return $mean;
        }
        foreach ($features as $feature) {
            $sum = 0;
            foreach ($indices as $idx) {
                $sum += $matrix[$idx][$feature] ?? 0;
            }
            $mean[$feature] = $sum / count($indices);
        }
        return $mean;
    }

    /**
     * Initialize the per-cluster aggregate stats structure.
     */
    private function initStats(int $k): array
    {
        $stats = [];
        for ($c = 1; $c <= $k; $c++) {
            $stats[$c] = [
                'count' => 0,
                // numeric sums
                'sum_usia' => 0,
                'sum_pendapatan' => 0,
                'sum_keluarga' => 0,
                'avg_usia' => 0,
                'avg_pendapatan' => 0,
                'avg_keluarga' => 0,
                // gender
                'sum_jumlah_laki' => 0,
                'sum_jumlah_perempuan' => 0,
                'persen_laki' => 0,
                // pendidikan buckets
                'edu_tidak_sekolah' => 0,
                'edu_sd' => 0,
                'edu_smp' => 0,
                'edu_sma' => 0,
                'edu_diploma' => 0,
                'edu_sarjana' => 0,
                // pekerjaan buckets
                'kerja_petani' => 0,
                'kerja_buruh' => 0,
                'kerja_wiraswasta' => 0,
                'kerja_pedagang' => 0,
                'kerja_irt' => 0,
                'kerja_nelayan' => 0,
                'kerja_tidak_bekerja' => 0,
                'kerja_lainnya' => 0,
                // status perkawinan buckets
                'kawin' => 0,
                'belum_kawin' => 0,
                'cerai' => 0,
                // kepemilikan rumah buckets
                'home_milik_sendiri' => 0,
                'home_sewa' => 0,
                'home_menumpang' => 0,
                'home_lainnya' => 0,
                // penerima pkh
                'penerima_pkh' => 0,
                'bukan_penerima_pkh' => 0,
                'members' => [],
            ];
        }
        return $stats;
    }

    /**
     * Accumulate one record into a cluster stat bucket.
     */
    private function accumulateStat(array &$stat, array $record): void
    {
        $usia = (float) ($record['usia'] ?? 0);
        $pendapatan = (float) ($record['pendapatan_per_bulan'] ?? 0);
        $keluarga = (int) ($record['jumlah_anggota_keluarga'] ?? 0);
        $jk = strtoupper(trim((string) ($record['jenis_kelamin'] ?? '')));
        $pendidikan = strtoupper(trim((string) ($record['pendidikan'] ?? '')));
        $pekerjaan = strtoupper(trim((string) ($record['pekerjaan'] ?? '')));
        $statusKawin = strtoupper(trim((string) ($record['status_perkawinan'] ?? '')));
        $rumah = strtoupper(trim((string) ($record['kepemilikan_rumah'] ?? '')));
        $pkh = strtoupper(trim((string) ($record['penerima_pkh'] ?? '')));

        $stat['count']++;
        $stat['sum_usia'] += $usia;
        $stat['sum_pendapatan'] += $pendapatan;
        $stat['sum_keluarga'] += $keluarga;

        if ($jk === 'L') {
            $stat['sum_jumlah_laki']++;
        } elseif ($jk === 'P') {
            $stat['sum_jumlah_perempuan']++;
        }

        // Pendidikan
        if (str_contains($pendidikan, 'TIDAK SEKOLAH')) {
            $stat['edu_tidak_sekolah']++;
        } elseif (str_contains($pendidikan, 'SARJANA') || str_contains($pendidikan, 'S1')) {
            $stat['edu_sarjana']++;
        } elseif (str_contains($pendidikan, 'DIPLOMA')) {
            $stat['edu_diploma']++;
        } elseif (str_contains($pendidikan, 'SMA') || str_contains($pendidikan, 'SMK')) {
            $stat['edu_sma']++;
        } elseif (str_contains($pendidikan, 'SMP')) {
            $stat['edu_smp']++;
        } elseif (str_contains($pendidikan, 'SD')) {
            $stat['edu_sd']++;
        }

        // Pekerjaan
        if (str_contains($pekerjaan, 'PETANI')) {
            $stat['kerja_petani']++;
        } elseif (str_contains($pekerjaan, 'BURUH')) {
            $stat['kerja_buruh']++;
        } elseif (str_contains($pekerjaan, 'WIRASWASTA')) {
            $stat['kerja_wiraswasta']++;
        } elseif (str_contains($pekerjaan, 'PEDAGANG')) {
            $stat['kerja_pedagang']++;
        } elseif (str_contains($pekerjaan, 'IRT')) {
            $stat['kerja_irt']++;
        } elseif (str_contains($pekerjaan, 'NELAYAN')) {
            $stat['kerja_nelayan']++;
        } elseif (str_contains($pekerjaan, 'TIDAK BEKERJA')) {
            $stat['kerja_tidak_bekerja']++;
        } else {
            $stat['kerja_lainnya']++;
        }

        // Status perkawinan
        if (str_contains($statusKawin, 'BELUM')) {
            $stat['belum_kawin']++;
        } elseif (str_contains($statusKawin, 'CERAI')) {
            $stat['cerai']++;
        } elseif (str_contains($statusKawin, 'KAWIN')) {
            $stat['kawin']++;
        }

        // Kepemilikan rumah
        if (str_contains($rumah, 'MILIK SENDIRI')) {
            $stat['home_milik_sendiri']++;
        } elseif (str_contains($rumah, 'SEWA') || str_contains($rumah, 'KONTRAK')) {
            $stat['home_sewa']++;
        } elseif (str_contains($rumah, 'MENUMPANG')) {
            $stat['home_menumpang']++;
        } else {
            $stat['home_lainnya']++;
        }

        // PKH
        if ($pkh === 'YA') {
            $stat['penerima_pkh']++;
        } else {
            $stat['bukan_penerima_pkh']++;
        }

        // Member label
        $name = trim((string) ($record['nama'] ?? ''));
        $dusun = trim((string) ($record['dusun'] ?? ''));
        $displayName = $name !== '' ? $name : ($dusun !== '' ? $dusun : '-');
        $stat['members'][] = $displayName;
    }
}
