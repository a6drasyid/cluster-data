<?php

namespace App\Http\Controllers;

use App\Models\DemographicData;
use App\Services\KMeansService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class DashboardController extends Controller
{
    protected $kMeansService;

    public function __construct(KMeansService $kMeansService)
    {
        $this->kMeansService = $kMeansService;
    }

    /**
     * Display the demographic clustering dashboard.
     */
    public function index(Request $request)
    {
        $d = $this->prepareClustering($request);

        return view('dashboard', [
            'kecamatans' => $d['kecamatans'],
            'dusuns' => $d['dusuns'],
            'selectedKecamatan' => $d['selectedKecamatan'],
            'selectedDusun' => $d['selectedDusun'],
            'allIndicators' => $d['allIndicators'],
            'selectedIndicators' => $d['selectedIndicators'],
            'weights' => $d['weights'],
            'clusteredData' => $d['clusteredData'],
            'clusterStats' => $d['clusterStats'],
            'iterations' => $d['iterations'],
            'totalRecords' => $d['totalRecords'],
            'mapLocations' => $d['mapLocations'],
            'clusterRegions' => $d['clusterRegions'],
            'clusterCenters' => $d['clusterCenters'],
        ]);
    }

    /**
     * Validate and sanitize a single weight value submitted from the dashboard.
     *
     * @param mixed $raw the submitted value (may be null / empty / string)
     */
    private function sanitizeWeight($raw, float $default): float
    {
        if ($raw === null || $raw === '') {
            return $default;
        }
        if (!is_numeric($raw)) {
            return $default;
        }
        $w = (float) $raw;
        // Keep weights in a sensible positive range so the distance stays stable.
        return max(0.1, min(10.0, $w));
    }

    /**
     * Shared data preparation used by both the dashboard page and the exports.
     *
     * @return array<string, mixed>
     */
    private function prepareClustering(Request $request): array
    {
        // 1. Available filters
        $kecamatans = DemographicData::select('kecamatan')
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->distinct()
            ->orderBy('kecamatan')
            ->pluck('kecamatan')
            ->toArray();

        $dusuns = DemographicData::select('dusun')
            ->whereNotNull('dusun')
            ->where('dusun', '!=', '')
            ->distinct()
            ->orderBy('dusun')
            ->pluck('dusun')
            ->toArray();

        // 2. Selected filters
        $selectedKecamatan = $request->input('kecamatan', 'all');
        $selectedDusun = trim($request->input('dusun', 'all'));

        // 3. The 8 clustering indicators.
        $allIndicators = [
            'pendidikan'            => 'Pendidikan',
            'pekerjaan'             => 'Pekerjaan',
            'pendapatan_per_bulan'  => 'Pendapatan',
            'jumlah_anggota_keluarga' => 'Jumlah Anggota Keluarga',
            'jenis_kelamin'         => 'Jenis Kelamin',
            'usia'                  => 'Usia',
            'status_perkawinan'     => 'Status Rumah (Perkawinan)',
            'kepemilikan_rumah'     => 'Kepemilikan Rumah',
        ];

        $selectedIndicators = $request->input('indicators', array_keys($allIndicators));
        if (!is_array($selectedIndicators) || empty($selectedIndicators)) {
            $selectedIndicators = array_keys($allIndicators);
        }
        $selectedIndicators = array_values(array_intersect($selectedIndicators, array_keys($allIndicators)));
        if (empty($selectedIndicators)) {
            $selectedIndicators = array_keys($allIndicators);
        }

        // 4. Query demographic data.
        $query = DemographicData::query();

        if ($selectedKecamatan !== 'all' && !empty($selectedKecamatan)) {
            $query->where('kecamatan', $selectedKecamatan);
        }

        if ($selectedDusun !== 'all' && !empty($selectedDusun)) {
            $query->where('dusun', 'like', "%{$selectedDusun}%");
        }

        $data = $query->get();

        // 4b. Resolve per-indicator weights from the request (fall back to defaults).
        $defaultWeights = $this->kMeansService->defaultWeights();
        $weights = [];
        foreach ($allIndicators as $key => $label) {
            $default = $defaultWeights[$key] ?? 1.0;
            $raw = $request->input("weights.{$key}");
            $weights[$key] = $this->sanitizeWeight($raw, $default);
        }

        // 5. Run K-Means on the filtered rows (3 clusters: Prioritas Tinggi,
        //    Menengah, dan Rendah), applying the user-selected weights.
        $k = min(3, max(1, $data->count()));
        $clusteringResult = [];

        if ($data->count() > 0) {
            $clusteringResult = $this->kMeansService->cluster($data, $selectedIndicators, $k, 100, $weights);
        } else {
            $clusteringResult = [
                'data' => [],
                'stats' => [],
                'iterations' => 0,
            ];
        }

        $clusteredData = $clusteringResult['data'] ?? [];
        $clusterStats = $clusteringResult['stats'] ?? [];
        $iterations = $clusteringResult['iterations'] ?? 0;

        // 6-8. Derived structures.
        $mapLocations = $this->buildMapLocations($clusteredData);
        $clusterRegions = $this->buildClusterRegions($clusteredData);
        $clusterCenters = $this->buildClusterCenters($clusteredData);

        return [
            'kecamatans' => $kecamatans,
            'dusuns' => $dusuns,
            'selectedKecamatan' => $selectedKecamatan,
            'selectedDusun' => $selectedDusun,
            'allIndicators' => $allIndicators,
            'selectedIndicators' => $selectedIndicators,
            'weights' => $weights,
            'clusteredData' => $clusteredData,
            'clusterStats' => $clusterStats,
            'iterations' => $iterations,
            'totalRecords' => count($clusteredData),
            'mapLocations' => $mapLocations,
            'clusterRegions' => $clusterRegions,
            'clusterCenters' => $clusterCenters,
        ];
    }

    /**
     * Build the rows (label => value per cluster) of the summary profile table.
     *
     * @return array<int, array{label: string, values: array<int,string|int>}>
     */
    private function buildSummaryRows(array $clusterStats): array
    {
        $dominant = function (array $counts, array $labels) {
            $max = -1; $key = 'Lainnya';
            foreach ($counts as $k => $v) {
                if ($v > $max) { $max = $v; $key = $k; }
            }
            return $key;
        };

        $rows = [];

        // label => callback returning per-cluster display value
        $defs = [
            'Jumlah Data' => fn($s) => ($s['count'] ?? 0) . ' orang',
            'Rata-rata Usia' => fn($s) => number_format($s['avg_usia'] ?? 0, 1, ',', '.') . ' thn',
            'Rata-rata Pendapatan' => fn($s) => 'Rp ' . number_format($s['avg_pendapatan'] ?? 0, 0, ',', '.'),
            'Anggota Keluarga' => fn($s) => number_format($s['avg_keluarga'] ?? 0, 1, ',', '.') . ' jiwa',
            'Jenis Kelamin' => fn($s) => ($s['sum_jumlah_laki'] ?? 0) >= ($s['sum_jumlah_perempuan'] ?? 0) ? 'Laki-laki' : 'Perempuan',
            'Pendidikan Terbanyak' => fn($s) => $dominant(['Tidak Sekolah' => $s['edu_tidak_sekolah'] ?? 0, 'SD' => $s['edu_sd'] ?? 0, 'SMP' => $s['edu_smp'] ?? 0, 'SMA' => $s['edu_sma'] ?? 0, 'Diploma' => $s['edu_diploma'] ?? 0, 'Sarjana' => $s['edu_sarjana'] ?? 0], ['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'Diploma', 'Sarjana']),
            'Pekerjaan Terbanyak' => fn($s) => $dominant(['Petani' => $s['kerja_petani'] ?? 0, 'Buruh' => $s['kerja_buruh'] ?? 0, 'Wiraswasta' => $s['kerja_wiraswasta'] ?? 0, 'Pedagang' => $s['kerja_pedagang'] ?? 0, 'IRT' => $s['kerja_irt'] ?? 0, 'Nelayan' => $s['kerja_nelayan'] ?? 0, 'Tidak Bekerja' => $s['kerja_tidak_bekerja'] ?? 0, 'Lainnya' => $s['kerja_lainnya'] ?? 0], ['Petani', 'Buruh', 'Wiraswasta', 'Pedagang', 'IRT', 'Nelayan', 'Tidak Bekerja', 'Lainnya']),
            'Status Perkawinan' => fn($s) => $dominant(['Belum Kawin' => $s['belum_kawin'] ?? 0, 'Kawin' => $s['kawin'] ?? 0, 'Cerai' => $s['cerai'] ?? 0], ['Belum Kawin', 'Kawin', 'Cerai']),
            'Kepemilikan Rumah' => fn($s) => $dominant(['Milik Sendiri' => $s['home_milik_sendiri'] ?? 0, 'Sewa' => $s['home_sewa'] ?? 0, 'Menumpang' => $s['home_menumpang'] ?? 0, 'Lainnya' => $s['home_lainnya'] ?? 0], ['Milik Sendiri', 'Sewa', 'Menumpang', 'Lainnya']),
            'Penerima PKH' => fn($s) => ($s['penerima_pkh'] ?? 0) . ' orang',
        ];

        foreach ($defs as $label => $cb) {
            $values = [];
            foreach ($clusterStats as $clusterId => $stat) {
                if (($stat['count'] ?? 0) > 0) {
                    $values[$clusterId] = $cb($stat);
                }
            }
            $rows[] = ['label' => $label, 'values' => $values];
        }

        return $rows;
    }

    /**
     * Build per-dusun aggregated map markers.
     *
     * Groups the clustered rows by dusun, uses the dusun's (longitude, latitude),
     * picks the dominant cluster, and aggregates a few summary counts.
     *
     * @return array<int, array> list of marker objects
     */
    private function buildMapLocations(array $clusteredData): array
    {
        $groups = [];

        foreach ($clusteredData as $row) {
            $dusun = trim((string) ($row['dusun'] ?? ''));
            $desa = trim((string) ($row['desa'] ?? ''));
            $kecamatan = trim((string) ($row['kecamatan'] ?? ''));
            $key = $dusun !== '' ? $dusun : $desa;
            if ($key === '') {
                $key = 'Lokasi';
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'dusun' => $dusun,
                    'desa' => $desa,
                    'kecamatan' => $kecamatan,
                    'latitude' => (float) ($row['latitude'] ?? 0),
                    'longitude' => (float) ($row['longitude'] ?? 0),
                    'total' => 0,
                    'clusterCounts' => [],
                    'penerima_pkh' => 0,
                    'laki' => 0,
                    'perempuan' => 0,
                ];
            }

            $groups[$key]['total']++;
            $cluster = (int) ($row['cluster'] ?? 0);
            $groups[$key]['clusterCounts'][$cluster] = ($groups[$key]['clusterCounts'][$cluster] ?? 0) + 1;

            if (strtoupper(trim((string) ($row['penerima_pkh'] ?? ''))) === 'YA') {
                $groups[$key]['penerima_pkh']++;
            }
            if (strtoupper(trim((string) ($row['jenis_kelamin'] ?? ''))) === 'L') {
                $groups[$key]['laki']++;
            } else {
                $groups[$key]['perempuan']++;
            }
        }

        $locations = [];
        foreach ($groups as $key => $g) {
            // Dominant cluster = the one with most members in this dusun.
            $dominant = 0;
            $max = -1;
            foreach ($g['clusterCounts'] as $c => $count) {
                if ($count > $max) {
                    $max = $count;
                    $dominant = $c;
                }
            }

            $locations[] = [
                'dusun' => $g['dusun'],
                'desa' => $g['desa'],
                'kecamatan' => $g['kecamatan'],
                'latitude' => $g['latitude'],
                'longitude' => $g['longitude'],
                'total' => $g['total'],
                'cluster' => $dominant,
                'penerima_pkh' => $g['penerima_pkh'],
                'laki' => $g['laki'],
                'perempuan' => $g['perempuan'],
            ];
        }

        return $locations;
    }

    /**
     * Build the list of dusun (+ their kecamatan) that belong to each cluster,
     * for the clickable summary table.
     *
     * @return array<int, array<int, array{dusun: string, desa: string, kecamatan: string, jumlah: int}>>
     */
    private function buildClusterRegions(array $clusteredData): array
    {
        // 1. Count, per dusun, how many residents fall into each cluster.
        //    First pass fills: [dusun_key => [cluster_id => count]].
        $dusunClusterCounts = [];
        $dusunMeta = [];

        foreach ($clusteredData as $row) {
            $cluster = (int) ($row['cluster'] ?? 0);
            $dusun = trim((string) ($row['dusun'] ?? ''));
            $desa = trim((string) ($row['desa'] ?? ''));
            $kecamatan = trim((string) ($row['kecamatan'] ?? ''));
            $key = $dusun ?: ($desa ?: 'Lainnya');

            $dusunMeta[$key] = [
                'dusun' => $dusun,
                'desa' => $desa,
                'kecamatan' => $kecamatan,
            ];
            $dusunClusterCounts[$key][$cluster] = ($dusunClusterCounts[$key][$cluster] ?? 0) + 1;
        }

        // 2. Compute the MODE (dominant cluster) for every dusun: the cluster with
        //    the highest resident count wins, so each dusun is assigned to exactly
        //    ONE cluster (avoids a dusun appearing in every cluster).
        $regions = [];
        foreach ($dusunClusterCounts as $key => $clusterCounts) {
            arsort($clusterCounts);
            $dominantCluster = (int) array_key_first($clusterCounts);
            $residentCount = $clusterCounts[$dominantCluster];

            // Also sum the total residents of the dusun (across all clusters).
            $totalResidents = array_sum($clusterCounts);

            $regions[$dominantCluster][$key] = [
                'dusun' => $dusunMeta[$key]['dusun'],
                'desa' => $dusunMeta[$key]['desa'],
                'kecamatan' => $dusunMeta[$key]['kecamatan'],
                // Number of residents in the dominant cluster.
                'jumlah' => $residentCount,
                // Total residents of this dusun (regardless of cluster).
                'total' => $totalResidents,
            ];
        }

        // 3. Sort each cluster's regions by name, and re-index.
        foreach ($regions as $cluster => $list) {
            uasort($list, fn($a, $b) => strcmp($a['dusun'] ?: $a['desa'], $b['dusun'] ?: $b['desa']));
            $regions[$cluster] = array_values($list);
        }

        return $regions;
    }

    /**
     * Compute one representative map marker per cluster.
     *
     * Each marker sits at the average (latitude, longitude) of that cluster's
     * members and carries a `count` so the map can size it proportionally,
     * letting all four clusters be shown at once.
     *
     * @return array<int, array{cluster: int, latitude: float, longitude: float, count: int, dusun: string[]}>
     */
    private function buildClusterCenters(array $clusteredData): array
    {
        $centers = [];

        foreach ($clusteredData as $row) {
            $cluster = (int) ($row['cluster'] ?? 0);
            if ($cluster < 1) {
                continue;
            }

            $lat = (float) ($row['latitude'] ?? 0);
            $lng = (float) ($row['longitude'] ?? 0);
            $dusun = trim((string) ($row['dusun'] ?? '')) ?: trim((string) ($row['desa'] ?? ''));

            if (!isset($centers[$cluster])) {
                $centers[$cluster] = [
                    'cluster' => $cluster,
                    'latitude' => 0,
                    'longitude' => 0,
                    'count' => 0,
                    'dusun' => [],
                ];
            }

            $centers[$cluster]['latitude'] += $lat;
            $centers[$cluster]['longitude'] += $lng;
            $centers[$cluster]['count']++;

            if ($dusun !== '' && !in_array($dusun, $centers[$cluster]['dusun'], true)) {
                $centers[$cluster]['dusun'][] = $dusun;
            }
        }

        foreach ($centers as $cluster => &$c) {
            if ($c['count'] > 0) {
                $c['latitude'] = round($c['latitude'] / $c['count'], 6);
                $c['longitude'] = round($c['longitude'] / $c['count'], 6);
            }
            sort($c['dusun']);
        }
        unset($c);

        // Re-index so the view can iterate 1..k in order.
        ksort($centers);

        return $centers;
    }

    /**
     * Export the cluster summary as PDF, Excel (.xlsx) or CSV.
     */
    public function export(Request $request, string $format = 'pdf')
    {
        $d = $this->prepareClustering($request);

        $clusterStats = $d['clusterStats'];
        $clusterRegions = $d['clusterRegions'];
        $rows = $this->buildSummaryRows($clusterStats);
        $priorityLabels = [1 => 'Prioritas Tinggi', 2 => 'Prioritas Menengah', 3 => 'Prioritas Rendah'];
        $clusterColors = [1 => '#f1270d', 2 => '#f39214', 3 => '#0ea5e9'];

        // Determine the ordered list of clusters actually present.
        $clusters = [];
        foreach ($clusterStats as $id => $s) {
            if (($s['count'] ?? 0) > 0) {
                $clusters[] = $id;
            }
        }
        sort($clusters);

        $title = 'Ringkasan Profil Klaster - Data Kependudukan Lombok Timur';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.cluster-summary-pdf', [
                'title' => $title,
                'rows' => $rows,
                'clusters' => $clusters,
                'clusterStats' => $clusterStats,
                'clusterRegions' => $clusterRegions,
                'priorityLabels' => $priorityLabels,
                'clusterColors' => $clusterColors,
                'totalRecords' => $d['totalRecords'],
                'selectedKecamatan' => $d['selectedKecamatan'],
                'selectedDusun' => $d['selectedDusun'],
                'iterations' => $d['iterations'],
            ]);
            return $pdf->download('ringkasan-profil-klaster.pdf');
        }

        // Build the tabular data (Excel / CSV share the same column layout).
        $data = $this->buildExportTable($rows, $clusters, $clusterStats, $clusterRegions, $priorityLabels);

        if ($format === 'csv') {
            return $this->outputCsv($data, 'ringkasan-profil-klaster.csv');
        }

        if ($format === 'excel') {
            return $this->outputExcel($data, 'ringkasan-profil-klaster.xlsx');
        }

        abort(404, 'Format tidak dikenali.');
    }

    /**
     * Show the detailed list of members that belong to one cluster.
     */
    public function showCluster(Request $request, int $id)
    {
        $d = $this->prepareClustering($request);

        // The cluster must actually exist in the current result.
        if (!isset($d['clusterStats'][$id]) || ($d['clusterStats'][$id]['count'] ?? 0) === 0) {
            abort(404, 'Klaster tidak ditemukan pada hasil clustering saat ini.');
        }

        $allMembers = collect($d['clusteredData'])
            ->where('cluster', $id)
            ->values()
            ->toArray();

        $clusterColors = [1 => '#f1270d', 2 => '#f39214', 3 => '#0ea5e9'];
        $priorityLabels = [1 => 'Prioritas Tinggi', 2 => 'Prioritas Menengah', 3 => 'Prioritas Rendah'];

        // Manual pagination over the array of members.
        $perPage = 25;
        $page = max(1, (int) Paginator::resolveCurrentPage('page'));
        $current = array_slice($allMembers, ($page - 1) * $perPage, $perPage);
        $members = new LengthAwarePaginator(
            $current,
            count($allMembers),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
        $members->appends(request()->query());

        return view('cluster-detail', [
            'clusterId' => $id,
            'clusterColor' => $clusterColors[$id] ?? '#6b7280',
            'priorityLabel' => $priorityLabels[$id] ?? '',
            'members' => $members,
            'totalMembers' => count($allMembers),
            'stat' => $d['clusterStats'][$id],
            'selectedKecamatan' => $d['selectedKecamatan'],
            'selectedDusun' => $d['selectedDusun'],
            'selectedIndicators' => $d['selectedIndicators'],
        ]);
    }

    /**
     * Export the member list of a single cluster as PDF / Excel / CSV.
     */
    public function exportCluster(Request $request, int $id, string $format)
    {
        $d = $this->prepareClustering($request);

        if (!isset($d['clusterStats'][$id]) || ($d['clusterStats'][$id]['count'] ?? 0) === 0) {
            abort(404, 'Klaster tidak ditemukan.');
        }

        $members = collect($d['clusteredData'])
            ->where('cluster', $id)
            ->values()
            ->toArray();

        $clusterColors = [1 => '#f1270d', 2 => '#f39214', 3 => '#0ea5e9'];
        $priorityLabels = [1 => 'Prioritas Tinggi', 2 => 'Prioritas Menengah', 3 => 'Prioritas Rendah'];
        $title = "Data Anggota Klaster {$id} - Kependudukan Lombok Timur";

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.cluster-members-pdf', [
                'title' => $title,
                'clusterId' => $id,
                'clusterColor' => $clusterColors[$id] ?? '#6b7280',
                'priorityLabel' => $priorityLabels[$id] ?? '',
                'members' => $members,
                'total' => count($members),
            ]);
            return $pdf->download("data-anggota-klaster-{$id}.pdf");
        }

        // Build tabular data for Excel / CSV.
        $data = [];
        $data[] = ['No', 'NIK', 'Nama', 'Desa', 'Dusun', 'Kecamatan', 'Jenis Kelamin', 'Usia', 'Status Perkawinan', 'Pendidikan', 'Pekerjaan', 'Jumlah Anggota Keluarga', 'Pendapatan/Bulan', 'Kepemilikan Rumah', 'Penerima PKH'];
        foreach ($members as $i => $m) {
            $data[] = [
                $i + 1,
                $m['nik'] ?? '',
                $m['nama'] ?? '',
                $m['desa'] ?? '',
                $m['dusun'] ?? '',
                $m['kecamatan'] ?? '',
                $m['jenis_kelamin'] ?? '',
                $m['usia'] ?? '',
                $m['status_perkawinan'] ?? '',
                $m['pendidikan'] ?? '',
                $m['pekerjaan'] ?? '',
                $m['jumlah_anggota_keluarga'] ?? '',
                $m['pendapatan_per_bulan'] ?? '',
                $m['kepemilikan_rumah'] ?? '',
                $m['penerima_pkh'] ?? '',
            ];
        }

        if ($format === 'csv') {
            return $this->outputCsv($data, "data-anggota-klaster-{$id}.csv");
        }

        if ($format === 'excel') {
            return $this->outputMemberExcel($data, $clusterColors[$id] ?? '6B7280', "data-anggota-klaster-{$id}.xlsx");
        }

        abort(404, 'Format tidak dikenali.');
    }

    /**
     * Build a rectangular array of table cells (rows of label + per-cluster value).
     */
    private function buildExportTable(array $rows, array $clusters, array $clusterStats, array $clusterRegions, array $priorityLabels): array
    {
        $data = [];

        // Header row(s).
        $headerFirst = ['Klaster / Indikator'];
        $headerSecond = ['Indikator'];
        foreach ($clusters as $id) {
            $headerFirst[] = "Klaster {$id}";
            $headerSecond[] = $priorityLabels[$id] ?? '';
        }
        $data[] = $headerFirst;
        $data[] = $headerSecond;

        // Data rows.
        foreach ($rows as $row) {
            $line = [$row['label']];
            foreach ($clusters as $id) {
                $line[] = $row['values'][$id] ?? '-';
            }
            $data[] = $line;
        }

        // Optional: wilayah per klaster.
        $data[] = [];
        $wilayahHeader = ['Wilayah (Dusun/Kecamatan)'];
        foreach ($clusters as $id) {
            $wilayahHeader[] = '';
        }
        $data[] = $wilayahHeader;

        $regions = $clusterRegions;
        // Find the max number of regions among clusters for column alignment.
        $maxRegions = 0;
        foreach ($clusters as $id) {
            $maxRegions = max($maxRegions, count($regions[$id] ?? []));
        }

        for ($i = 0; $i < $maxRegions; $i++) {
            $line = ['-'];
            foreach ($clusters as $id) {
                $region = $regions[$id][$i] ?? null;
                if ($region) {
                    // Show the hamlet with its DESA (e.g. "Dusun Nenggung Barat (Paok Motong)")
                    // instead of the kecamatan, so the village name is what stands out.
                    $hamlet = $region['dusun'] ?: $region['desa'];
                    $village = $region['desa'] ?: $region['dusun'];
                    $line[] = $hamlet . ' (' . $village . ')';
                } else {
                    $line[] = '';
                }
            }
            $data[] = $line;
        }

        return $data;
    }

    /**
     * Stream a CSV download using PhpSpreadsheet.
     */
    private function outputCsv(array $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        $writer = new Csv($spreadsheet);
        $writer->setUseBOM(true);
        $writer->setDelimiter(';');

        $temp = tempnam(sys_get_temp_dir(), 'csv');
        $writer->save($temp);

        return response()->download($temp, $filename, ['Content-Type' => 'text/csv; charset=UTF-8'])->deleteFileAfterSend(true);
    }

    /**
     * Stream a nicely styled Excel (.xlsx) download using PhpSpreadsheet.
     */
    private function outputExcel(array $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Cluster accent colours (match the map legend).
        $clusterColors = [1 => 'F1270D', 2 => 'F39214', 3 => '0EA5E9'];
        $numClusters = count(array_filter($data[0] ?? [], fn($v) => str_starts_with((string)$v, 'Klaster ')));

        // ---- Title styling: row 1 header ----
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getFont()->setSize(11);
        $sheet->getStyle('A1:' . $highestCol . '1')
            ->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1:' . $highestCol . '1')
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1F2937');
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()->setHorizontal('center');

        // ---- Sub-header: row 2 ----
        $sheet->getStyle('A2:' . $highestCol . '2')
            ->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('374151'));
        $sheet->getStyle('A2:' . $highestCol . '2')
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');
        $sheet->getStyle('A2:' . $highestCol . '2')->getAlignment()->setHorizontal('center');

        // ---- First column bold ----
        $sheet->getStyle('A1:A' . $highestRow)->getFont()->setBold(true);

        // ---- Accent header per cluster column (rows 1 & 2) ----
        for ($i = 1; $i <= $numClusters; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $accent = $clusterColors[$i] ?? '6B7280';
            $tint = $this->lightenHex($accent, 0.85);
            $sheet->getStyle($colLetter . '1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($accent);
            $sheet->getStyle($colLetter . '2')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($tint);
        }

        // ---- Borders for the whole table ----
        $thin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]]];
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray($thin);

        // ---- Alignment & wrap ----
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getAlignment()->setVertical('top')->setWrapText(true);
        // Center the data cells (from row 3 onward).
        if ($highestRow >= 3) {
            $sheet->getStyle('B3:' . $highestCol . $highestRow)->getAlignment()->setHorizontal('center');
        }

        // ---- Auto-width columns (with a sensible cap) ----
        foreach ($sheet->getColumnIterator() as $column) {
            $letter = $column->getColumnIndex();
            if ($letter === $highestCol) {
                break;
            }
            $max = 10;
            foreach ($column->getCellIterator() as $cell) {
                $length = mb_strlen((string) $cell->getValue());
                if ($length > $max) {
                    $max = $length;
                }
            }
            $sheet->getColumnDimension($letter)->setWidth(min(40, max(12, $max + 2)));
        }

        // Freeze header rows so they stay visible when scrolling.
        $sheet->freezePane('A3');

        $writer = new Xlsx($spreadsheet);

        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Lighten a hex colour by mixing it toward white.
     */
    private function lightenHex(string $hex, float $factor): string
    {
        $hex = ltrim(strtoupper($hex), '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) round(($r + (255 - $r) * $factor));
        $g = (int) round(($g + (255 - $g) * $factor));
        $b = (int) round(($b + (255 - $b) * $factor));
        return sprintf('%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Stream a nicely styled Excel (.xlsx) of a cluster member list.
     */
    private function outputMemberExcel(array $data, string $accent, string $filename)
    {
        $accent = ltrim(strtoupper($accent), '#');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Header row styled with the cluster accent colour.
        $sheet->getStyle('A1:' . $highestCol . '1')
            ->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1:' . $highestCol . '1')
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($accent);
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()->setHorizontal('center');

        // Thin borders on the whole table.
        $thin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]]];
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray($thin);

        // Centre numeric-ish columns.
        foreach (['A', 'C', 'D', 'E', 'F', 'I', 'M', 'N', 'O'] as $letter) {
            if (\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($letter) <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol)) {
                $sheet->getStyle($letter . '1:' . $letter . $highestRow)->getAlignment()->setHorizontal('center');
            }
        }
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getAlignment()->setVertical('top');

        // Auto-width.
        foreach ($sheet->getColumnIterator() as $column) {
            $letter = $column->getColumnIndex();
            if ($letter === $highestCol) {
                break;
            }
            $max = 8;
            foreach ($column->getCellIterator() as $cell) {
                $len = mb_strlen((string) $cell->getValue());
                if ($len > $max) {
                    $max = $len;
                }
            }
            $sheet->getColumnDimension($letter)->setWidth(min(40, max(10, $max + 2)));
        }

        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
