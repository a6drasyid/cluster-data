<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 4px; color: #111827; }
        .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 16px; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 14px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; vertical-align: top; }
        th { background: #f3f4f6; text-align: center; font-size: 10px; }
        td.label { font-weight: bold; white-space: nowrap; }
        .cluster-head { text-align: center; }
        .legend { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; }
        .section-title { font-size: 11px; font-weight: bold; margin: 14px 0 6px; color: #374151; }
        .footer { margin-top: 20px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">Kabupaten Lombok Timur · Metode K-Means Clustering</div>
    <div class="meta">
        Jumlah data: {{ $totalRecords }} · Iterasi: {{ $iterations }} ·<br>
        Filter kecamatan: {{ $selectedKecamatan === 'all' ? 'Semua' : $selectedKecamatan }} · Filter dusun: {{ $selectedDusun === 'all' ? 'Semua' : $selectedDusun }}
    </div>

    <div class="section-title">Ringkasan Profil Klaster</div>
    <table>
        <thead>
            <tr>
                <th style="width:150px">Indikator</th>
                @foreach($clusters as $id)
                    <th class="cluster-head">
                        <span class="legend" style="background-color: {{ $clusterColors[$id] }}"></span>
                        Klaster {{ $id }}
                        <br><span style="font-weight:normal">{{ $priorityLabels[$id] ?? '' }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td class="label">{{ $row['label'] }}</td>
                    @foreach($clusters as $id)
                        <td style="text-align:center">{{ $row['values'][$id] ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($clusterRegions) > 0)
        <div class="section-title">Wilayah (Dusun / Kecamatan) per Klaster</div>
        <table>
            <thead>
                <tr>
                    @foreach($clusters as $id)
                        <th class="cluster-head">
                            <span class="legend" style="background-color: {{ $clusterColors[$id] }}"></span>
                            Klaster {{ $id }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $maxRegions = 0;
                    foreach ($clusters as $id) {
                        $maxRegions = max($maxRegions, count($clusterRegions[$id] ?? []));
                    }
                @endphp
                @for($i = 0; $i < $maxRegions; $i++)
                    <tr>
                        @foreach($clusters as $id)
                            @php
                                $region = $clusterRegions[$id][$i] ?? null;
                            @endphp
                            <td>
                                @if($region)
                                    <strong>{{ $region['dusun'] ?: $region['desa'] }}</strong>
                                    <br><span style="color:#6b7280">Desa {{ $region['desa'] ?: $region['dusun'] }} · {{ $region['jumlah'] }} data</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh Sistem Klasterisasi Data Kependudukan - Kabupaten Lombok Timur.
    </div>
</body>
</html>
