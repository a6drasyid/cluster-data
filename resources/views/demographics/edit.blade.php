<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('demographics.index') }}" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Data Kependudukan') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8">

                    <form action="{{ route('demographics.update', $demographic->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- NIK -->
                            <div>
                                <label for="nik" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">NIK</label>
                                <input type="text" name="nik" id="nik" value="{{ old('nik', $demographic->nik) }}" placeholder="Nomor Induk Kependudukan" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('nik')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nama -->
                            <div>
                                <label for="nama" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama</label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama', $demographic->nama) }}" placeholder="Nama lengkap" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('nama')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label for="jenis_kelamin" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    <option value="L" {{ old('jenis_kelamin', $demographic->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('jenis_kelamin', $demographic->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                                @error('jenis_kelamin')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Usia -->
                            <div>
                                <label for="usia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Usia</label>
                                <input type="number" name="usia" id="usia" value="{{ old('usia', $demographic->usia) }}" min="0" max="150" placeholder="Contoh: 35" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('usia')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status Perkawinan -->
                            <div>
                                <label for="status_perkawinan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status Perkawinan</label>
                                <select name="status_perkawinan" id="status_perkawinan" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    @foreach(['Belum Kawin', 'Kawin', 'Cerai', 'Janda/Duda'] as $opt)
                                        <option value="{{ $opt }}" {{ old('status_perkawinan', $demographic->status_perkawinan) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('status_perkawinan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pendidikan -->
                            <div>
                                <label for="pendidikan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pendidikan</label>
                                <select name="pendidikan" id="pendidikan" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    @foreach(['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'Diploma', 'Sarjana'] as $opt)
                                        <option value="{{ $opt }}" {{ old('pendidikan', $demographic->pendidikan) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('pendidikan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pekerjaan -->
                            <div>
                                <label for="pekerjaan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pekerjaan</label>
                                <select name="pekerjaan" id="pekerjaan" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    @foreach(['Petani', 'Buruh', 'Wiraswasta', 'IRT', 'Nelayan', 'Tidak Bekerja', 'Pedagang'] as $opt)
                                        <option value="{{ $opt }}" {{ old('pekerjaan', $demographic->pekerjaan) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('pekerjaan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Desa -->
                            <div>
                                <label for="desa" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Desa</label>
                                <input type="text" name="desa" id="desa" value="{{ old('desa', $demographic->desa) }}" placeholder="Contoh: Paok Motong" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('desa')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dusun -->
                            <div>
                                <label for="dusun" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Dusun</label>
                                <input type="text" name="dusun" id="dusun" value="{{ old('dusun', $demographic->dusun) }}" placeholder="Contoh: Dusun Nenggung Barat" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('dusun')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kecamatan -->
                            <div>
                                <label for="kecamatan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kecamatan</label>
                                <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan', $demographic->kecamatan) }}" placeholder="Contoh: Masbagik" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('kecamatan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kabupaten -->
                            <div>
                                <label for="kabupaten" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kabupaten</label>
                                <input type="text" name="kabupaten" id="kabupaten" value="{{ old('kabupaten', $demographic->kabupaten) }}" placeholder="Contoh: Lombok Timur" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('kabupaten')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Jumlah Anggota Keluarga -->
                            <div>
                                <label for="jumlah_anggota_keluarga" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jumlah Anggota Keluarga</label>
                                <input type="number" name="jumlah_anggota_keluarga" id="jumlah_anggota_keluarga" value="{{ old('jumlah_anggota_keluarga', $demographic->jumlah_anggota_keluarga) }}" min="1" placeholder="Contoh: 4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('jumlah_anggota_keluarga')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pendapatan Per Bulan -->
                            <div>
                                <label for="pendapatan_per_bulan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pendapatan Per Bulan (Rp)</label>
                                <input type="number" name="pendapatan_per_bulan" id="pendapatan_per_bulan" value="{{ old('pendapatan_per_bulan', $demographic->pendapatan_per_bulan) }}" min="0" placeholder="Contoh: 3000000" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('pendapatan_per_bulan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kepemilikan Rumah -->
                            <div>
                                <label for="kepemilikan_rumah" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kepemilikan Rumah</label>
                                <select name="kepemilikan_rumah" id="kepemilikan_rumah" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    @foreach(['Milik Sendiri', 'Sewa', 'Menumpang', 'Lainnya'] as $opt)
                                        <option value="{{ $opt }}" {{ old('kepemilikan_rumah', $demographic->kepemilikan_rumah) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('kepemilikan_rumah')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Penerima PKH -->
                            <div>
                                <label for="penerima_pkh" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Penerima PKH</label>
                                <select name="penerima_pkh" id="penerima_pkh" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                    <option value="Ya" {{ old('penerima_pkh', $demographic->penerima_pkh) === 'Ya' ? 'selected' : '' }}>Ya</option>
                                    <option value="Tidak" {{ old('penerima_pkh', $demographic->penerima_pkh) === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                </select>
                                @error('penerima_pkh')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div>
                                <label for="latitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Garis Lintang (Latitude)</label>
                                <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude', $demographic->latitude) }}" placeholder="Contoh: -8.632526" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('latitude')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div>
                                <label for="longitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Garis Bujur (Longitude)</label>
                                <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude', $demographic->longitude) }}" placeholder="Contoh: 116.476502" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                                @error('longitude')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                            <a href="{{ route('demographics.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold text-sm transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition-colors shadow">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
