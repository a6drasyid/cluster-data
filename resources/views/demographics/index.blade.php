<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Data Kependudukan Lombok Timur') }}
            </h2>
            <a href="{{ route('demographics.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import Excel / CSV
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Alert Session Notification -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-sm">
                    <div class="flex items-center gap-2 font-semibold mb-2">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Catatan Kesalahan Baris Pada File Import:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs max-h-40 overflow-y-auto pl-2">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Search and Filter Panel -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 rounded-2xl shadow-sm p-6 mb-6">
                <form action="{{ route('demographics.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Cari Nama / Desa / Dusun</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama, desa, atau dusun..." class="w-full pl-10 pr-4 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4.5 w-4.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Kecamatan Filter -->
                    <div>
                        <label for="kecamatan" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Kecamatan</label>
                        <select name="kecamatan" id="kecamatan" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all">
                            <option value="all">Semua Kecamatan</option>
                            @foreach($kecamatans as $kec)
                                <option value="{{ $kec }}" {{ $selectedKecamatan === $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit button -->
                    <div class="flex gap-2">
                        <button type="submit" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white px-6 h-[42px] rounded-xl border border-gray-300 dark:border-gray-600 transition-all flex items-center justify-center font-semibold text-sm">
                            Filter
                        </button>
                        <a href="{{ route('demographics.index') }}" class="px-4 h-[42px] rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center justify-center text-sm font-medium">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Demographics Table -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 rounded-2xl shadow-sm overflow-hidden mb-6">
                @if($demographics->isEmpty())
                    <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h4 class="font-bold text-lg mb-1">Data Tidak Ditemukan</h4>
                        <p class="text-sm mb-4">Belum ada data atau filter Anda tidak cocok.</p>
                        <a href="{{ route('demographics.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-xl transition-all shadow">
                            Import File Excel / CSV
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/60 uppercase border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3.5 whitespace-nowrap">NIK</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Nama</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">L/P</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Usia</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Status Kawin</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Pendidikan</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Pekerjaan</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Desa</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Dusun</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Kecamatan</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Kabupaten</th>
                                    <th class="px-4 py-3.5 text-center whitespace-nowrap">Keluarga</th>
                                    <th class="px-4 py-3.5 text-right whitespace-nowrap">Pendapatan/Bulan</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Rumah</th>
                                    <th class="px-4 py-3.5 text-center whitespace-nowrap">PKH</th>
                                    <th class="px-4 py-3.5 text-center whitespace-nowrap">Koordinat</th>
                                    <th class="px-4 py-3.5 text-center whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach($demographics as $data)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                        <td class="px-4 py-4 font-mono text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $data->nik ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                            {{ $data->nama ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            @if($data->jenis_kelamin === 'L')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">L</span>
                                            @elseif($data->jenis_kelamin === 'P')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-50 text-pink-700 dark:bg-pink-950/50 dark:text-pink-300">P</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->usia ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->status_perkawinan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->pendidikan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->pekerjaan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->desa ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->dusun ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                                {{ $data->kecamatan ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->kabupaten ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->jumlah_anggota_keluarga ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            Rp {{ number_format($data->pendapatan_per_bulan ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $data->kepemilikan_rumah ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            @if($data->penerima_pkh === 'Ya')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Ya</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center font-mono text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                            {{ $data->latitude ? round($data->latitude, 4) : '-' }}, {{ $data->longitude ? round($data->longitude, 4) : '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('demographics.edit', $data->id) }}" class="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-gray-400 hover:text-indigo-600 transition-colors" title="Edit Data">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('demographics.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-gray-400 hover:text-rose-600 transition-colors" title="Hapus Data">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($demographics->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                            {{ $demographics->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
