<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .leaflet-container {
                background: #f8fafc;
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(158, 122, 122, 0.77);
            }
            .dark .glass-card {
                background: rgba(30, 41, 59, 0.85);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.05);
            }
        </style>
    @endpush

    <div class="py-6">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Left Sidebar: Filter Form -->
                <div class="w-full lg:w-1/4 flex-shrink-0">
                    <div class="glass-card shadow-lg rounded-2xl p-6 sticky top-6">
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                                </svg>
                                CLUSTERING K-MEANS
                            </h3>
                            @if($totalRecords > 0)
                                <span class="px-2.5 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 rounded-full">
                                    {{ $totalRecords }} Data
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('dashboard') }}" method="GET" id="filterForm">
                            <!-- Kecamatan Dropdown -->
                            <div class="mb-4">
                                <label for="kecamatan" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">KECAMATAN</label>
                                <select name="kecamatan" id="kecamatan" onchange="this.form.submit()" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all duration-150 text-sm">
                                    <option value="all" {{ $selectedKecamatan === 'all' ? 'selected' : '' }}>(Semua Kecamatan)</option>
                                    @foreach($kecamatans as $kec)
                                        <option value="{{ $kec }}" {{ $selectedKecamatan === $kec ? 'selected' : '' }}>{{ $kec }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dusun Search & Filter -->
                            <div class="mb-5">
                                <label for="dusun" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">CARI / PILIH DUSUN</label>
                                <div class="relative">
                                    <input type="text" name="dusun" id="dusun" value="{{ $selectedDusun !== 'all' ? $selectedDusun : '' }}" placeholder="Ketik atau pilih dusun..." list="dusunList" onchange="this.form.submit()" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all duration-150 text-sm pl-9">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <datalist id="dusunList">
                                        <option value="all">Semua Dusun</option>
                                        @foreach($dusuns as $ds)
                                            <option value="{{ $ds }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            <!-- Indicators Checkboxes + Weights -->
                            <div class="mb-6">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">INDIKATOR CLUSTERING</label>
                                <div class="space-y-2 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 max-h-72 overflow-y-auto">
                                    @foreach($allIndicators as $key => $label)
                                        <div class="flex items-center justify-between gap-2">
                                            <label class="flex items-center cursor-pointer group flex-1">
                                                <input type="checkbox" name="indicators[]" value="{{ $key }}"
                                                    {{ in_array($key, $selectedIndicators) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 shadow-sm"
                                                >
                                                <span class="ms-3 text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                    {{ $label }}
                                                </span>
                                            </label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" name="weights[{{ $key }}]" step="0.1" min="0.1" max="10"
                                                    value="{{ $weights[$key] ?? 1 }}"
                                                    title="Bobot {{ $label }}"
                                                    class="w-14 text-center rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-xs py-1"
                                                >
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">Bobot menentukan seberapa besar pengaruh tiap indikator dalam clustering (semakin besar, semakin dominan).</p>
                            </div>

                            <!-- Apply Button -->
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold py-3 px-4 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-150 flex items-center justify-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Hitung & Klusterkan
                            </button>
                        </form>

                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400 text-center">
                            Iterasi K-Means: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $iterations }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Dashboard Grid -->
                <div class="w-full lg:w-3/4 flex flex-col gap-6">

                    @if($totalRecords === 0)
                        <div class="glass-card shadow-lg rounded-2xl p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300 mb-2">Data Belum Diimpor / Filter Tidak Cocok</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">Silakan unggah file Excel/CSV</p>
                            <a href="{{ route('demographics.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-xl transition-all shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Import File
                            </a>
                        </div>
                    @else
                        <!-- Dashboard Upper Grid: Map (2/3) + Distribution (1/3) -->
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                            <!-- Leaflet Map Card -->
                            <div class="xl:col-span-2 glass-card shadow-lg rounded-2xl overflow-hidden flex flex-col min-h-[500px]">
                                <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">PETA SEBARAN KLASTER PER DUSUN</h4>
                                    <span class="text-xs text-gray-400">{{ count($mapLocations) }} lokasi · koordinat (Longitude, Latitude)</span>
                                </div>
                                <div class="flex-grow relative h-[450px]" id="map"></div>
                            </div>

                            <!-- Distribution Charts Stack -->
                            <div class="flex flex-col gap-6">
                                <!-- Donut Distribution -->
                                <div class="glass-card shadow-lg rounded-2xl p-5 flex flex-col justify-between">
                                    <div class="pb-3 border-b border-gray-100 dark:border-gray-800">
                                        <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">DISTRIBUSI KLASTER</h4>
                                    </div>
                                    <div class="py-4">
                                        <div id="distributionChart" class="min-h-[220px]"></div>
                                    </div>
                                </div>

                                <!-- Cluster Indicators Profile -->
                                <div class="glass-card shadow-lg rounded-2xl p-5 flex flex-col justify-between">
                                    <div class="pb-3 border-b border-gray-100 dark:border-gray-800">
                                        <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">PROFIL INDIKATOR (RATA-RATA)</h4>
                                    </div>
                                    <div class="py-4">
                                        <div id="profileChart" class="min-h-[220px]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cluster Details / Profiles -->
                        <div class="glass-card shadow-lg rounded-2xl overflow-hidden">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">RINGKASAN PROFIL KLASTER</h4>
                                    <span class="text-xs text-gray-400">Hasil pengelompokan K-Means · {{ count($clusterStats) }} Klaster · {{ $totalRecords }} Data</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @php
                                        $exportQuery = request()->query();
                                    @endphp
                                    <a href="{{ route('dashboard.export', ['format' => 'pdf'] + $exportQuery) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0L8 12m4 4V4"/></svg>
                                        PDF
                                    </a>
                                    <a href="{{ route('dashboard.export', ['format' => 'excel'] + $exportQuery) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Excel
                                    </a>
                                    <a href="{{ route('dashboard.export', ['format' => 'csv'] + $exportQuery) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0L8 12m4 4V4"/></svg>
                                        CSV
                                    </a>
                                </div>
                            </div>

                            @php
                                // Colours match the map legend exactly (3 clusters).
                                // Cluster 1 = merah = Prioritas Tinggi,
                                // Cluster 2 = oranye = Prioritas Menengah,
                                // Cluster 3 = biru = Prioritas Rendah.
                                $priorityLabels = [1 => 'Prioritas Tinggi', 2 => 'Prioritas Menengah', 3 => 'Prioritas Rendah'];
                                $clusterColors = [1 => '#f1270d', 2 => '#f39214', 3 => '#0ea5e9'];
                            @endphp

                            @php
                                // Small helper: pick the label of the largest count.
                                $dominant = function (array $counts, array $labels) {
                                    $max = -1; $key = "Lainnya";
                                    foreach ($counts as $k => $v) {
                                        if ($v > $max) { $max = $v; $key = $k; }
                                    }
                                    return $key;
                                };
                            @endphp

                            <div class="overflow-x-auto p-5">
                                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="px-3 py-3 text-xs uppercase tracking-wider text-gray-400 font-bold whitespace-nowrap bg-gray-50 dark:bg-gray-900/60">Indikator</th>
                                            @foreach($clusterStats as $clusterId => $stat)
                                                @if($stat['count'] > 0)
                                                <th class="px-3 py-3 text-center whitespace-nowrap bg-gray-50 dark:bg-gray-900/60">
                                                    <button type="button" data-cluster-btn="{{ $clusterId }}"
                                                        class="group inline-flex flex-col items-center gap-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 px-2 py-1 transition-colors">
                                                        <span class="inline-flex items-center gap-1.5 font-bold text-gray-800 dark:text-white">
                                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $clusterColors[$clusterId] }}"></span>
                                                            Klaster {{ $clusterId }}
                                                        </span>
                                                        <span class="text-[10px] font-normal text-gray-400">{{ $priorityLabels[$clusterId] ?? '' }}</span>
                                                        <span class="text-[9px] font-medium text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">klik untuk lihat wilayah</span>
                                                    </button>
                                                </th>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach([
                                            ['label' => 'Jumlah Data', 'render' => fn($s) => $s['count'] . ' orang', 'bold' => true],
                                            ['label' => 'Rata-rata Usia', 'render' => fn($s) => number_format($s['avg_usia'] ?? 0, 1, ',', '.') . ' thn'],
                                            ['label' => 'Rata-rata Pendapatan', 'render' => fn($s) => 'Rp ' . number_format($s['avg_pendapatan'] ?? 0, 0, ',', '.')],
                                            ['label' => 'Anggota Keluarga', 'render' => fn($s) => number_format($s['avg_keluarga'] ?? 0, 1, ',', '.') . ' jiwa'],
                                            ['label' => 'Jenis Kelamin', 'render' => fn($s) => ($s['sum_jumlah_laki'] ?? 0) >= ($s['sum_jumlah_perempuan'] ?? 0) ? 'Laki-laki' : 'Perempuan'],
                                            ['label' => 'Pendidikan Terbanyak', 'render' => fn($s) => $dominant(['Tidak Sekolah' => $s['edu_tidak_sekolah'] ?? 0,'SD' => $s['edu_sd'] ?? 0,'SMP' => $s['edu_smp'] ?? 0,'SMA' => $s['edu_sma'] ?? 0,'Diploma' => $s['edu_diploma'] ?? 0,'Sarjana' => $s['edu_sarjana'] ?? 0],['Tidak Sekolah','SD','SMP','SMA','Diploma','Sarjana'])],
                                            ['label' => 'Pekerjaan Terbanyak', 'render' => fn($s) => $dominant(['Petani' => $s['kerja_petani'] ?? 0,'Buruh' => $s['kerja_buruh'] ?? 0,'Wiraswasta' => $s['kerja_wiraswasta'] ?? 0,'Pedagang' => $s['kerja_pedagang'] ?? 0,'IRT' => $s['kerja_irt'] ?? 0,'Nelayan' => $s['kerja_nelayan'] ?? 0,'Tidak Bekerja' => $s['kerja_tidak_bekerja'] ?? 0,'Lainnya' => $s['kerja_lainnya'] ?? 0],['Petani','Buruh','Wiraswasta','Pedagang','IRT','Nelayan','Tidak Bekerja','Lainnya'])],
                                            ['label' => 'Status Perkawinan', 'render' => fn($s) => $dominant(['Belum Kawin' => $s['belum_kawin'] ?? 0,'Kawin' => $s['kawin'] ?? 0,'Cerai' => $s['cerai'] ?? 0],['Belum Kawin','Kawin','Cerai'])],
                                            ['label' => 'Kepemilikan Rumah', 'render' => fn($s) => $dominant(['Milik Sendiri' => $s['home_milik_sendiri'] ?? 0,'Sewa' => $s['home_sewa'] ?? 0,'Menumpang' => $s['home_menumpang'] ?? 0,'Lainnya' => $s['home_lainnya'] ?? 0],['Milik Sendiri','Sewa','Menumpang','Lainnya'])],
                                            ['label' => 'Penerima PKH', 'render' => fn($s) => ($s['penerima_pkh'] ?? 0) . ' orang', 'badge' => true],
                                        ] as $row)
                                        <tr class="hover:bg-gray-50/40 dark:hover:bg-gray-800/20 transition-colors">
                                            <td class="px-3 py-2.5 font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap {{ ($row['bold'] ?? false) ? 'text-gray-900 dark:text-white' : '' }}">{{ $row['label'] }}</td>
                                            @foreach($clusterStats as $clusterId => $stat)
                                                @if($stat['count'] > 0)
                                                <td class="px-3 py-2.5 text-center whitespace-nowrap {{ ($row['bold'] ?? false) ? 'font-bold text-gray-900 dark:text-white' : '' }}">
                                                    @if($row['badge'] ?? false)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ ($stat['penerima_pkh'] ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                                            {{ $row['render']($stat) }}
                                                        </span>
                                                    @else
                                                        {{ $row['render']($stat) }}
                                                    @endif
                                                </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Clickable cluster detail cards (dusun/kecamatan anggota) --}}
                            @foreach($clusterStats as $clusterId => $stat)
                                @if($stat['count'] > 0 && isset($clusterRegions[$clusterId]) && count($clusterRegions[$clusterId]) > 0)
                                <div id="cluster-detail-{{ $clusterId }}" data-cluster-detail="{{ $clusterId }}"
                                    class="hidden border-t border-gray-100 dark:border-gray-800">
                                    <div class="m-4 rounded-2xl overflow-hidden border" style="border-color: {{ $clusterColors[$clusterId] }}33">
                                        <div class="px-5 py-3 flex items-center justify-between gap-3 flex-wrap"
                                            style="background-color: {{ $clusterColors[$clusterId] }}1a;">
                                            <div class="flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $clusterColors[$clusterId] }}"></span>
                                                <span class="font-bold text-gray-900 dark:text-white text-sm">Klaster {{ $clusterId }}: {{ $priorityLabels[$clusterId] ?? '' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['count'] }} data · {{ count($clusterRegions[$clusterId]) }} wilayah</span>
                                                @php
                                                    $exportQuery = request()->query();
                                                @endphp
                                                <a href="{{ route('dashboard.cluster.export', ['id' => $clusterId, 'format' => 'pdf'] + $exportQuery) }}"
                                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white/80 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200 text-[11px] font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors" title="Unduh PDF">
                                                    PDF
                                                </a>
                                                <a href="{{ route('dashboard.cluster.export', ['id' => $clusterId, 'format' => 'excel'] + $exportQuery) }}"
                                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white/80 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200 text-[11px] font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors" title="Unduh Excel">
                                                    Excel
                                                </a>
                                                <a href="{{ route('dashboard.cluster.detail', ['id' => $clusterId] + $exportQuery) }}"
                                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white/80 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200 text-[11px] font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors" title="Lihat daftar anggota">
                                                    Lihat Detail ›
                                                </a>
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($clusterRegions[$clusterId] as $region)
                                                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $clusterColors[$clusterId] }}"></span>
                                                        <div class="leading-tight">
                                                            <div class="text-[13px] font-semibold text-gray-800 dark:text-white">{{ $region['dusun'] ?: $region['desa'] }}</div>
                                                            <div class="text-[10px] text-gray-400">{{ $region['kecamatan'] ?? '-' }} · {{ $region['jumlah'] }} data</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>

                    @endif
                </div>

            </div>
        </div>
    </div>

    @if($totalRecords > 0)
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // --- MAP CONFIGURATION ---
                    // Each entry is one DUSUN marker with its own (longitude, latitude).
                    const locations = @json($mapLocations);

                    let center = [-8.6508, 116.5342];
                    let zoom = 11;

                    const map = L.map('map', {
                        zoomControl: true,
                        maxZoom: 18,
                        minZoom: 8
                    }).setView(center, zoom);

                    // OpenStreetMap standard tiles - free and works without any API key.
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        subdomains: 'abc',
                        maxZoom: 19
                    }).addTo(map);

                    const clusterConfig = {
                        1: { name: 'Cluster 1: Prioritas Tinggi', color: '#f1270d', desc: 'Wilayah yang paling berhak menerima bantuan' },
                        2: { name: 'Cluster 2: Prioritas Menengah', color: '#f39214', desc: 'Wilayah dengan prioritas menengah' },
                        3: { name: 'Cluster 3: Prioritas Rendah', color: '#0ea5e9', desc: 'Wilayah dengan prioritas rendah' }
                    };

                    const bounds = [];

                    locations.forEach(function (loc) {
                        if (!loc.latitude || !loc.longitude) return;

                        // Plot using (longitude, latitude) as required by Leaflet.
                        const latLng = L.latLng(loc.latitude, loc.longitude);
                        bounds.push(latLng);

                        const config = clusterConfig[loc.cluster] || { color: '#6b7280', name: 'Unknown' };

                        // Marker filled with the cluster colour so the point matches the legend
                        // (e.g. Cluster 1 = red = Prioritas Tinggi). A thin darker outline keeps
                        // the marker visible over the map while still showing the cluster colour.
                        const marker = L.circleMarker(latLng, {
                            radius: Math.min(18, 8 + loc.total * 0.1),
                            fillColor: config.color,
                            color: config.color,
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 1,
                            className: ''
                        }).addTo(map);

                        const dusunTitle = loc.dusun || loc.desa || '-';
                        const popupContent = `
                            <div class="p-3 text-xs leading-relaxed max-w-[280px]">
                                <div class="font-bold text-gray-900 border-b border-gray-100 pb-1.5 mb-2 text-sm">
                                    ${dusunTitle}
                                </div>
                                <div class="grid grid-cols-2 gap-x-2 gap-y-1 mb-2">
                                    <span class="text-gray-400">Desa:</span> <span class="font-semibold text-gray-700 text-right">${loc.desa || '-'}</span>
                                    <span class="text-gray-400">Kecamatan:</span> <span class="font-semibold text-gray-700 text-right">${loc.kecamatan || '-'}</span>
                                    <span class="text-gray-400">Koordinat:</span> <span class="font-semibold text-gray-700 text-right font-mono">${loc.longitude.toFixed(5)}, ${loc.latitude.toFixed(5)}</span>
                                    <span class="text-gray-400">Total Data:</span> <span class="font-semibold text-gray-700 text-right">${loc.total} orang</span>
                                    <span class="text-gray-400">Laki-laki:</span> <span class="font-semibold text-gray-700 text-right">${loc.laki} orang</span>
                                    <span class="text-gray-400">Perempuan:</span> <span class="font-semibold text-gray-700 text-right">${loc.perempuan} orang</span>
                                    <span class="text-gray-400">Penerima PKH:</span> <span class="font-semibold text-emerald-600 text-right">${loc.penerima_pkh} orang</span>
                                </div>
                                <div class="pt-1.5 mt-1 border-t border-gray-100 flex items-center gap-1.5 font-semibold text-gray-800 text-[11px]">
                                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: ${config.color}"></span>
                                    ${config.name}
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        marker.on('mouseover', function () {
                            marker.setStyle({ weight: 4, radius: marker.options.radius + 2 });
                        });
                        marker.on('mouseout', function () {
                            marker.setStyle({ weight: 2 });
                        });
                    });

                    // --- CLUSTER CENTERS: one marker per cluster (1..3) ---
                    // Each marker is placed at the centroid of its members. All four
                    // markers use the SAME fixed size and are distinguished by colour
                    // only (consistent with the legend). No radius scaling, no halo.
                    const centers = @json($clusterCenters);

                    Object.values(centers).forEach(function (center) {
                        if (!center.latitude || !center.longitude) return;

                        const config = clusterConfig[center.cluster] || { color: '#6b7280', name: 'Unknown' };

                        const centerMarker = L.circleMarker([center.latitude, center.longitude], {
                            radius: 10,
                            fillColor: config.color,
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map);

                        const centerPopup = `
                            <div class="p-3 text-xs leading-relaxed max-w-[260px]">
                                <div class="font-bold text-gray-900 border-b border-gray-100 pb-1.5 mb-2 text-sm flex items-center gap-2">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: ${config.color}"></span>
                                    ${config.name}
                                </div>
                                <div class="grid grid-cols-2 gap-x-2 gap-y-1 mb-2">
                                    <span class="text-gray-400">Jumlah Data:</span> <span class="font-semibold text-gray-700 text-right">${center.count} orang</span>
                                    <span class="text-gray-400">Titik Pusat:</span> <span class="font-semibold text-gray-700 text-right font-mono">${center.longitude}, ${center.latitude}</span>
                                </div>
                                <div class="pt-1.5 mt-1 border-t border-gray-100">
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Wilayah (${center.dusun.length})</div>
                                    <div class="flex flex-wrap gap-1">
                                        ${center.dusun.map(d => `<span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] text-gray-700 dark:text-gray-300">${d}</span>`).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                        centerMarker.bindPopup(centerPopup);

                        // Push the centroid into the autofit bounds too.
                        bounds.push([center.latitude, center.longitude]);
                    });

                    if (bounds.length > 0) {
                        map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40] });
                    }

                    // Legend
                    const legend = L.control({ position: 'bottomleft' });
                    legend.onAdd = function () {
                        const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 text-xs');
                        div.innerHTML = `
                            <h5 class="font-bold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-1.5 mb-2.5 uppercase tracking-wider text-[10px]">Legenda Klaster</h5>
                            <div class="space-y-2 text-gray-600 dark:text-gray-300">
                                ${Object.keys(clusterConfig).map(k => `
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-block w-3.5 h-3.5 rounded-full border border-white dark:border-gray-800 shadow-sm" style="background-color: ${clusterConfig[k].color}"></span>
                                        <div>
                                            <div class="font-bold text-[11px]">${clusterConfig[k].name}</div>
                                            <div class="text-[10px] text-gray-400">${clusterConfig[k].desc}</div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                        return div;
                    };
                    legend.addTo(map);

                    // --- APEX CHARTS ---
                    const stats = @json($clusterStats);
                    const donutLabels = Object.values(clusterConfig).map(c => c.name);
                    const donutColors = Object.values(clusterConfig).map(c => c.color);
                    const donutSeries = Object.keys(clusterConfig).map(k => stats[k] ? stats[k].count : 0);

                    const distributionOptions = {
                        series: donutSeries,
                        labels: donutLabels,
                        chart: { type: 'donut', height: 220, toolbar: { show: false } },
                        colors: donutColors,
                        stroke: { show: false },
                        dataLabels: { enabled: false },
                        legend: { show: false },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total Data',
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        tooltip: { y: { formatter: function (val) { return val + " Data"; } } }
                    };
                    const distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionOptions);
                    distributionChart.render();

                    // Profile Bar Chart - normalized averages across the available numeric indicators
                    const avgAge = Object.values(stats).map(s => s.avg_usia || 0);
                    const avgInc = Object.values(stats).map(s => s.avg_pendapatan || 0);
                    const avgFam = Object.values(stats).map(s => s.avg_keluarga || 0);
                    const pctMale = Object.values(stats).map(s => s.persen_laki || 0);
                    const maxVals = {
                        usia: Math.max(...avgAge) || 1,
                        pendapatan: Math.max(...avgInc) || 1,
                        keluarga: Math.max(...avgFam) || 1,
                        gender: Math.max(...pctMale) || 1,
                    };

                    const profileSeries = Object.keys(clusterConfig).map(k => {
                        const stat = stats[k] || { avg_usia: 0, avg_pendapatan: 0, avg_keluarga: 0, persen_laki: 0 };
                        return {
                            name: clusterConfig[k].name,
                            data: [
                                Math.round((stat.avg_usia / maxVals.usia) * 100),
                                Math.round((stat.avg_pendapatan / maxVals.pendapatan) * 100),
                                Math.round((stat.avg_keluarga / maxVals.keluarga) * 100),
                                Math.round((stat.persen_laki / maxVals.gender) * 100),
                            ],
                            rawValues: [
                                stat.avg_usia,
                                stat.avg_pendapatan,
                                stat.avg_keluarga,
                                stat.persen_laki,
                            ]
                        };
                    });

                    const profileOptions = {
                        series: profileSeries,
                        chart: { type: 'bar', height: 250, toolbar: { show: false } },
                        colors: donutColors,
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '75%',
                                dataLabels: { position: 'top' }
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 1, colors: ['#transparent'] },
                        xaxis: {
                            categories: ['Usia', 'Pendapatan', 'Anggota Keluarga', 'Rasio Laki-Laki'],
                            labels: { formatter: function (val) { return val + "%"; } },
                            max: 110
                        },
                        legend: { show: false },
                        tooltip: {
                            y: {
                                formatter: function (val, { seriesIndex, dataPointIndex, w }) {
                                    const rawVal = w.config.series[seriesIndex].rawValues[dataPointIndex];
                                    const category = w.globals.labels[dataPointIndex];
                                    if (category === 'Usia') return rawVal.toFixed(1) + " tahun";
                                    if (category === 'Pendapatan') return "Rp" + new Intl.NumberFormat('id-ID').format(rawVal);
                                    if (category === 'Anggota Keluarga') return rawVal.toFixed(1) + " jiwa/KK";
                                    if (category === 'Rasio Laki-Laki') return rawVal.toFixed(1) + "% Laki-Laki";
                                    return rawVal;
                                }
                            }
                        }
                    };
                    const profileChart = new ApexCharts(document.querySelector("#profileChart"), profileOptions);
                    profileChart.render();

                    // Toggle cluster detail cards when a cluster header is clicked.
                    document.querySelectorAll('[data-cluster-btn]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const id = btn.getAttribute('data-cluster-btn');
                            const detail = document.querySelector('[data-cluster-detail="' + id + '"]');
                            if (!detail) return;
                            detail.classList.toggle('hidden');
                        });
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
