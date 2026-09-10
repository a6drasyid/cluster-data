<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; margin: 20px; }
        h1 { font-size: 15px; margin: 0 0 3px; color: #111827; }
        .subtitle { font-size: 10px; color: #6b7280; margin-bottom: 14px; }
        .cluster-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; margin-bottom: 4px; }
        .dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 3px 5px; }
        th { background: #f3f4f6; text-align: center; font-size: 9px; white-space: nowrap; }
        td { font-size: 9px; }
        .no { text-align: center; }
        .right { text-align: right; }
        .center { text-align: center; }
        .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="cluster-badge">
        <span class="dot" style="background-color: {{ $clusterColor }}"></span>
        Klaster {{ $clusterId }} · {{ $priorityLabel }}
    </div>
    <div class="subtitle">Total data: {{ $total }} orang · Kabupaten Lombok Timur</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>L/P</th>
                <th>Usia</th>
                <th>Status</th>
                <th>Pendidikan</th>
                <th>Pekerjaan</th>
                <th>Desa</th>
                <th>Dusun</th>
                <th>Kecamatan</th>
                <th>Klrg</th>
                <th>Pendapatan</th>
                <th>Rumah</th>
                <th>PKH</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $i => $m)
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td>{{ $m['nik'] ?? '-' }}</td>
                    <td>{{ $m['nama'] ?? '-' }}</td>
                    <td class="center">{{ $m['jenis_kelamin'] ?? '-' }}</td>
                    <td class="center">{{ $m['usia'] ?? '-' }}</td>
                    <td>{{ $m['status_perkawinan'] ?? '-' }}</td>
                    <td>{{ $m['pendidikan'] ?? '-' }}</td>
                    <td>{{ $m['pekerjaan'] ?? '-' }}</td>
                    <td>{{ $m['desa'] ?? '-' }}</td>
                    <td>{{ $m['dusun'] ?? '-' }}</td>
                    <td>{{ $m['kecamatan'] ?? '-' }}</td>
                    <td class="center">{{ $m['jumlah_anggota_keluarga'] ?? '-' }}</td>
                    <td class="right">Rp {{ number_format($m['pendapatan_per_bulan'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $m['kepemilikan_rumah'] ?? '-' }}</td>
                    <td class="center">{{ $m['penerima_pkh'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh Sistem Klasterisasi Data Kependudukan - Kabupaten Lombok Timur.
    </div>
</body>
</html>
