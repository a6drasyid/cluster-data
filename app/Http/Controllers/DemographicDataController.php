<?php

namespace App\Http\Controllers;

use App\Models\DemographicData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DemographicDataController extends Controller
{
    /**
     * Display a listing of demographic data.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedKecamatan = $request->input('kecamatan', 'all');

        $query = DemographicData::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('desa', 'like', "%{$search}%")
                  ->orWhere('dusun', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%");
            });
        }

        if ($selectedKecamatan !== 'all') {
            $query->where('kecamatan', $selectedKecamatan);
        }

        $demographics = $query->orderBy('kecamatan')
            ->orderBy('desa')
            ->paginate(15)
            ->withQueryString();

        $kecamatans = DemographicData::select('kecamatan')
            ->whereNotNull('kecamatan')
            ->distinct()
            ->orderBy('kecamatan')
            ->pluck('kecamatan')
            ->toArray();

        return view('demographics.index', [
            'demographics' => $demographics,
            'kecamatans' => $kecamatans,
            'selectedKecamatan' => $selectedKecamatan,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating (Importing) a new resource.
     */
    public function create()
    {
        return view('demographics.create');
    }

    /**
     * Download sample Excel template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_penerima_pkh.csv"',
        ];

        $columns = [
            'ID',
            'NIK',
            'Nama',
            'Jenis_Kelamin',
            'Usia',
            'Status_Perkawinan',
            'Pendidikan',
            'Pekerjaan',
            'Desa',
            'Dusun',
            'Kecamatan',
            'Kabupaten',
            'Jumlah_Anggota_Keluarga',
            'Pendapatan_per_Bulan',
            'Kepemilikan_Rumah',
            'Penerima_PKH',
            'Lat',
            'Long',
        ];

        $sampleRows = [
            ['1', '4355950783669724', 'Lina Hidayat', 'P', '70', 'Belum Kawin', 'Diploma', 'Petani', 'Paok Motong', 'Dusun Nenggung Barat', 'Masbagik', 'Lombok Timur', '4', '3200000', 'Milik Sendiri', 'Ya', '-8.632526', '116.476502'],
            ['2', '5315403526933230', 'Yusuf Hidayat', 'L', '38', 'Cerai', 'Diploma', 'IRT', 'Paok Motong', 'Dusun Tunjang Selatan', 'Masbagik', 'Lombok Timur', '1', '3750000', 'Sewa', 'Tidak', '-8.632111', '116.464988'],
            ['3', '7104007624101213', 'Fajar Maulana', 'L', '63', 'Cerai', 'Sarjana', 'Wiraswasta', 'Paok Motong', 'Dusun Nenggung Timur', 'Masbagik', 'Lombok Timur', '3', '300000', 'Menumpang', 'Ya', '-8.632625', '116.477895'],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Excel/CSV data.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'file.required' => 'Silakan pilih file Excel atau CSV terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv, .txt).',
            'file.max' => 'Ukuran file tidak boleh melebihi 10MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                if (!class_exists('ZipArchive')) {
                    return back()->with('error', 'Ekstensi PHP "zip" (ZipArchive) belum aktif. Silakan aktifkan extension=zip pada php.ini dan restart server.');
                }
                $spreadsheet = IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray(null, true, true, false);
            } else {
                $content = file_get_contents($path);
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
                $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", trim($content)));
                if (empty($lines)) {
                    return back()->with('error', 'File yang diunggah kosong.');
                }

                $firstLine = $lines[0];
                $delimiter = ',';
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                    $delimiter = "\t";
                }

                foreach ($lines as $line) {
                    if (trim($line) === '') continue;
                    $rows[] = str_getcsv($line, $delimiter);
                }
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File yang diunggah tidak berisi data.');
        }

        // Map headers to column indexes
        $headerMap = $this->mapHeaders($rows[0]);

        if (!isset($headerMap['nama'])) {
            return back()->with('error', 'Header file tidak dikenali. Pastikan file memiliki kolom: Nama, Jenis_Kelamin, Usia, dll.');
        }

        // Extract data rows
        $extracted = [];
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            if (empty(array_filter($row, fn($val) => $val !== null && trim((string)$val) !== ''))) {
                continue;
            }

            $nama = trim((string)($row[$headerMap['nama']] ?? ''));
            if (empty($nama)) continue;

            $extracted[] = [
                'nik' => trim((string)($row[$headerMap['nik']] ?? '')),
                'nama' => $nama,
                'jenis_kelamin' => strtoupper(trim((string)($row[$headerMap['jenis_kelamin']] ?? ''))),
                'usia' => (int)($row[$headerMap['usia']] ?? 0),
                'status_perkawinan' => trim((string)($row[$headerMap['status_perkawinan']] ?? '')),
                'pendidikan' => trim((string)($row[$headerMap['pendidikan']] ?? '')),
                'pekerjaan' => trim((string)($row[$headerMap['pekerjaan']] ?? '')),
                'desa' => trim((string)($row[$headerMap['desa']] ?? '')),
                'dusun' => trim((string)($row[$headerMap['dusun']] ?? '')),
                'kecamatan' => trim((string)($row[$headerMap['kecamatan']] ?? '')),
                'kabupaten' => trim((string)($row[$headerMap['kabupaten']] ?? '')),
                'jumlah_anggota_keluarga' => (int)($row[$headerMap['jumlah_anggota_keluarga']] ?? 0),
                'pendapatan_per_bulan' => (float)str_replace(['.', ','], ['', '.'], (string)($row[$headerMap['pendapatan_per_bulan']] ?? 0)),
                'kepemilikan_rumah' => trim((string)($row[$headerMap['kepemilikan_rumah']] ?? '')),
                'penerima_pkh' => trim((string)($row[$headerMap['penerima_pkh']] ?? '')),
                'latitude' => (float)($row[$headerMap['latitude']] ?? 0),
                'longitude' => (float)($row[$headerMap['longitude']] ?? 0),
            ];
        }

        if (empty($extracted)) {
            return back()->with('error', 'Gagal mengekstrak data dari file. Pastikan file memiliki data yang valid.');
        }

        // Insert to database
        $insertedCount = 0;
        $replaceAll = $request->boolean('replace_all', true);

        DB::transaction(function () use ($extracted, $replaceAll, &$insertedCount) {
            if ($replaceAll) {
                DemographicData::query()->delete();
            }

            foreach ($extracted as $item) {
                DemographicData::create($item);
                $insertedCount++;
            }
        });

        $message = $replaceAll
            ? "Berhasil menggantikan seluruh data dengan {$insertedCount} data baru dari file yang diimpor."
            : "Berhasil mengimpor {$insertedCount} data baru.";

        return redirect()->route('demographics.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DemographicData $demographic)
    {
        return view('demographics.edit', [
            'demographic' => $demographic,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DemographicData $demographic)
    {
        $validated = $request->validate([
            'nik' => 'nullable|string|max:255',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:L,P',
            'usia' => 'required|integer|min:0|max:150',
            'status_perkawinan' => 'required|string|max:255',
            'pendidikan' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'dusun' => 'nullable|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'jumlah_anggota_keluarga' => 'required|integer|min:1',
            'pendapatan_per_bulan' => 'required|numeric|min:0',
            'kepemilikan_rumah' => 'required|string|max:255',
            'penerima_pkh' => 'required|string|in:Ya,Tidak',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'usia.required' => 'Usia wajib diisi.',
            'status_perkawinan.required' => 'Status perkawinan wajib diisi.',
            'pendidikan.required' => 'Pendidikan wajib diisi.',
            'pekerjaan.required' => 'Pekerjaan wajib diisi.',
            'desa.required' => 'Desa wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kabupaten.required' => 'Kabupaten wajib diisi.',
            'jumlah_anggota_keluarga.required' => 'Jumlah anggota keluarga wajib diisi.',
            'pendapatan_per_bulan.required' => 'Pendapatan per bulan wajib diisi.',
            'kepemilikan_rumah.required' => 'Kepemilikan rumah wajib diisi.',
            'penerima_pkh.required' => 'Status penerima PKH wajib diisi.',
            'latitude.required' => 'Latitude wajib diisi.',
            'longitude.required' => 'Longitude wajib diisi.',
        ]);

        $demographic->update($validated);

        return redirect()->route('demographics.index')
            ->with('success', "Data '{$demographic->nama}' berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DemographicData $demographic)
    {
        $demographic->delete();

        return redirect()->route('demographics.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Map header names to column indexes.
     */
    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $rawHeader) {
            $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$rawHeader)));

            if (in_array($clean, ['nik'])) {
                $map['nik'] = $index;
            } elseif (in_array($clean, ['nama', 'name'])) {
                $map['nama'] = $index;
            } elseif (in_array($clean, ['jeniskelamin', 'jk', 'jenis_kelamin'])) {
                $map['jenis_kelamin'] = $index;
            } elseif (in_array($clean, ['usia', 'umur', 'age'])) {
                $map['usia'] = $index;
            } elseif (in_array($clean, ['statusperkawinan', 'perkawinan', 'status_perkawinan'])) {
                $map['status_perkawinan'] = $index;
            } elseif (in_array($clean, ['pendidikan', 'education', 'tingkatpendidikan'])) {
                $map['pendidikan'] = $index;
            } elseif (in_array($clean, ['pekerjaan', 'occupation', 'employment'])) {
                $map['pekerjaan'] = $index;
            } elseif (in_array($clean, ['desa', 'kelurahan', 'desakelurahan'])) {
                $map['desa'] = $index;
            } elseif (in_array($clean, ['dusun', 'hamlet', 'subdistrict'])) {
                $map['dusun'] = $index;
            } elseif (in_array($clean, ['kecamatan', 'district', 'kec'])) {
                $map['kecamatan'] = $index;
            } elseif (in_array($clean, ['kabupaten', 'kab', 'regency'])) {
                $map['kabupaten'] = $index;
            } elseif (in_array($clean, ['jumlahanggotakeluarga', 'anggotakeluarga', 'familysize'])) {
                $map['jumlah_anggota_keluarga'] = $index;
            } elseif (in_array($clean, ['pendapatanperbulan', 'pendapatan', 'income'])) {
                $map['pendapatan_per_bulan'] = $index;
            } elseif (in_array($clean, ['kepemilikanrumah', 'rumah', 'homeownership'])) {
                $map['kepemilikan_rumah'] = $index;
            } elseif (in_array($clean, ['penerimapkh', 'pkh', 'penerima_pkh'])) {
                $map['penerima_pkh'] = $index;
            } elseif (in_array($clean, ['latitude', 'lat'])) {
                $map['latitude'] = $index;
            } elseif (in_array($clean, ['longitude', 'lng', 'long'])) {
                $map['longitude'] = $index;
            }
        }
        return $map;
    }
}
