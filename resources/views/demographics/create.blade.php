<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('demographics.index') }}" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Import Data Kependudukan Lombok Timur') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Global Alert Messages -->
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-sm">
                    <div class="flex items-center gap-2 font-semibold mb-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Peringatan / Catatan Kesalahan Import:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs max-h-48 overflow-y-auto pl-2">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- IMPORT CARD -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Unggah File Data Kependudukan
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Format yang didukung: <strong>Excel (.xlsx, .xls)</strong> dan <strong>CSV (.csv, .txt)</strong>.
                        </p>
                    </div>
                    <a href="{{ route('demographics.template.download') }}" class="inline-flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 font-semibold px-4 py-2.5 rounded-xl text-xs border border-emerald-200 dark:border-emerald-800 transition-colors shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Template CSV/Excel
                    </a>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('demographics.import') }}" method="POST" enctype="multipart/form-data" x-data="{ fileName: '', fileSize: '' }">
                    @csrf

                    <!-- Dropzone area -->
                    <div class="mb-6">
                        <label for="file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih File Excel / CSV</label>
                        
                        <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-2xl p-10 text-center bg-gray-50/50 dark:bg-gray-900/30 transition-all cursor-pointer group">
                            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv,.txt" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   @change="
                                       if ($event.target.files.length > 0) {
                                           fileName = $event.target.files[0].name;
                                           fileSize = ($event.target.files[0].size / 1024).toFixed(1) + ' KB';
                                       } else {
                                           fileName = '';
                                           fileSize = '';
                                       }
                                   ">
                            
                            <div x-show="!fileName" class="space-y-3">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
                                        Klik untuk memilih file atau drag & drop file ke sini
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        Mendukung Tabel (Kecamatan, Desa/Kelurahan, Laki-Laki, Perempuan) maupun CSV Flat (Maksimal 10MB)
                                    </p>
                                </div>
                            </div>

                            <div x-show="fileName" class="flex items-center justify-center gap-3 py-4" x-cloak>
                                <div class="p-3.5 bg-emerald-100 dark:bg-emerald-950/60 rounded-xl text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200" x-text="fileName"></p>
                                    <p class="text-xs text-gray-400" x-text="fileSize"></p>
                                </div>
                            </div>
                        </div>

                        @error('file')
                            <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Option to replace existing data -->
                    <div class="mb-6 p-4 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="replace_all" value="1" checked class="mt-0.5 w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                            <div>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Gantikan (Timpa) Seluruh Data Lama Pada Tabel & Database</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Jika dicentang, semua data kependudukan sebelumnya yang ada pada tabel & database akan digantikan sepenuhnya dengan data baru hasil impor. (Jika tidak dicentang, data lama yang cocok akan diperbarui / edit data).</p>
                            </div>
                        </label>
                    </div>

                    <!-- Guide Column Specifications -->
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-5 border border-gray-100 dark:border-gray-700/50 mb-6">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2.5">Fitur Parser Otomatis File BPS Lombok Timur:</h4>
                        <ul class="list-disc list-inside space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                            <li><strong class="text-indigo-600 dark:text-indigo-400">Tabel BPS Multi-Tahun:</strong> Otomatis mengekstrak nama Desa/Kelurahan, jumlah Laki-Laki, Perempuan, dan Total Penduduk dari file BPS Lombok Timur.</li>
                            <li><strong class="text-indigo-600 dark:text-indigo-400">Pembersihan Otomatis:</strong> Mengabaikan baris header judul, baris total (*KEC. KERUAK*, *TOTAL*), dan catatan kaki BPS (*Catatan*, *Sumber*).</li>
                            <li><strong class="text-indigo-600 dark:text-indigo-400">Geo-tagging Otomatis:</strong> Otomatis menentukan koordinat geografis (Latitude & Longitude) Desa/Kelurahan untuk seluruh Kecamatan di Kabupaten Lombok Timur.</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('demographics.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold text-sm transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition-colors shadow flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Data Kependudukan Lombok Timur
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
