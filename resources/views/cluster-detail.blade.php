<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                @php
                    // Keep the active dashboard filters when going back, but drop the
                    // member-list pagination 'page' param (not used on the dashboard).
                    $backQuery = request()->except(['page']);
                @endphp
                <a href="{{ route('dashboard', $backQuery) }}" title="Kembali ke Dashboard" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $clusterColor }}"></span>
                            Data Anggota Klaster {{ $clusterId }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $priorityLabel }} · {{ $totalMembers }} data</p>
                </div>
            </div>

            {{-- Export buttons --}}
            @php $exportQuery = request()->query(); @endphp
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.cluster.export', ['id' => $clusterId, 'format' => 'pdf'] + $exportQuery) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0L8 12m4 4V4"/></svg>
                    PDF
                </a>
                <a href="{{ route('dashboard.cluster.export', ['id' => $clusterId, 'format' => 'excel'] + $exportQuery) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </a>
                <a href="{{ route('dashboard.cluster.export', ['id' => $clusterId, 'format' => 'csv'] + $exportQuery) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0L8 12m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Daftar Anggota Klaster {{ $clusterId }}</h4>
                    <span class="text-xs text-gray-400">Total: {{ $totalMembers }} data</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/60 uppercase border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5 text-center whitespace-nowrap">No</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">NIK</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Nama</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">L/P</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Usia</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Pendidikan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Pekerjaan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Desa</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Dusun</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Kecamatan</th>
                                <th class="px-4 py-3.5 text-center whitespace-nowrap">Keluarga</th>
                                <th class="px-4 py-3.5 text-right whitespace-nowrap">Pendapatan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Rumah</th>
                                <th class="px-4 py-3.5 text-center whitespace-nowrap">PKH</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse($members as $i => $m)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                    <td class="px-4 py-3 text-center text-gray-400 whitespace-nowrap">{{ $members->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $m['nik'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $m['nama'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($m['jenis_kelamin'] === 'L')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">L</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-pink-50 text-pink-700 dark:bg-pink-950/50 dark:text-pink-300">P</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['usia'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['status_perkawinan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['pendidikan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['pekerjaan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['desa'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['dusun'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['kecamatan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['jumlah_anggota_keluarga'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">Rp {{ number_format($m['pendapatan_per_bulan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m['kepemilikan_rumah'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($m['penerima_pkh'] === 'Ya')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Ya</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Tidak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="px-4 py-12 text-center text-gray-400">Tidak ada data pada klaster ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($members->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
