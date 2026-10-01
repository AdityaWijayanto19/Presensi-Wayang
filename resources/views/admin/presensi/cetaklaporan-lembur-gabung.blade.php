<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Lembur - {{ $unitData->perusahaan }}</title>

    @include('admin.presensi.partials.lembur-laporan-style')

    <style>
        .page-break {
            page-break-after: always;
        }

        .cover-info {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 14px;
        }

        .cover-info td {
            border: 1px solid #777;
            padding: 5px 9px;
            font-size: 10px;
            line-height: 1.4;
            vertical-align: top;
        }

        .cover-label {
            width: 32%;
            font-weight: bold;
            background: #f7f4f1;
        }

        table.daftar {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.daftar th,
        table.daftar td {
            border: 1px solid #777;
            padding: 5px 6px;
            font-size: 10px;
            text-align: left;
            vertical-align: top;
        }

        table.daftar th {
            background: #f7f4f1;
            color: #7a5234;
            font-weight: bold;
            text-align: center;
        }

        table.daftar td.center,
        table.daftar th.center {
            text-align: center;
        }

        table.daftar tr.total td {
            background: #f7f4f1;
            font-weight: bold;
        }

        .cover-note {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            margin-top: 14px;
            font-size: 9px;
            color: #57534e;
            line-height: 1.5;
        }
    </style>
</head>


<body>

    {{-- ================= COVER / DAFTAR ISI ================= --}}
    <div class="document">

        {{-- HEADER --}}
        <div class="header">
            @if (!empty($unitData) && file_exists(public_path('assets/img/header-surat.png')))
                <img src="{{ public_path('assets/img/header-surat.png') }}" class="logo" alt="Logo">
            @endif

            <div class="company">
                <div class="company-name">PT Wayang Arthasena Group</div>
                <div class="company-text">Jl. Kedondong No. 5A, Rawamangun</div>
                <div class="company-text">Pulo Gadung, Jakarta Timur - Indonesia</div>
                <div class="company-text">Telephone: +6221 38859001</div>
                <div class="company-text">Fax: +6221 38859001</div>
            </div>
        </div>


        {{-- TITLE --}}
        <div class="title">
            <div class="title-main">Laporan Lembur</div>
            <div class="title-sub">Kumpulan Laporan Hasil Pekerjaan Lembur</div>
            <div class="title-sub" style="margin-top:4px;">
                Periode Cut-off: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
            </div>
        </div>


        {{-- INFO LAPORAN --}}
        @php
            $fmtDurasi = function ($jam) {
                $formatted = rtrim(rtrim(number_format((float) $jam, 1, '.', ''), '0'), '.');
                return str_replace('.', ',', $formatted) . ' jam';
            };
        @endphp
        <table class="cover-info">
            <tr>
                <td class="cover-label">Unit Kerja</td>
                <td>{{ $unitData->unit }}</td>
                <td class="cover-label">Perusahaan</td>
                <td>{{ $unitData->perusahaan }}</td>
            </tr>
            <tr>
                <td class="cover-label">Periode Cut-off</td>
                <td>{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</td>
                <td class="cover-label">Dicetak Pada</td>
                <td>{{ now('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="cover-label">Jumlah Karyawan</td>
                <td>{{ count($daftar) }} karyawan</td>
                <td class="cover-label">Total Laporan Lembur</td>
                <td>{{ $totalLaporan }} laporan ({{ $fmtDurasi($totalDurasi) }})</td>
            </tr>
        </table>


        {{-- DAFTAR ISI --}}
        <table class="daftar">
            <thead>
                <tr>
                    <th class="center" style="width:7%;">No.</th>
                    <th style="width:16%;">NIK</th>
                    <th style="width:27%;">Nama Karyawan</th>
                    <th style="width:21%;">Jabatan</th>
                    <th class="center" style="width:14%;">Jumlah Laporan</th>
                    <th class="center" style="width:15%;">Total Durasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item['nik'] }}</td>
                        <td>{{ $item['nama_lengkap'] }}</td>
                        <td>{{ $item['jabatan'] }}</td>
                        <td class="center">{{ $item['jumlah'] }}</td>
                        <td class="center">{{ $fmtDurasi($item['total_durasi']) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td class="center" colspan="4">Total</td>
                    <td class="center">{{ $totalLaporan }}</td>
                    <td class="center">{{ $fmtDurasi($totalDurasi) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="cover-note">
            Dokumen ini berisi kumpulan laporan hasil pekerjaan lembur karyawan terpilih pada periode cut-off
            di atas, diurutkan berdasarkan nama karyawan lalu tanggal lembur. Karyawan tanpa laporan lembur
            yang disetujui pada periode tersebut tetap tercantum dengan jumlah laporan 0.
        </div>

    </div>

    <div class="page-break"></div>


    {{-- ================= ISI LAPORAN PER KARYAWAN ================= --}}
    @foreach ($daftar as $item)
        @foreach ($item['reports'] as $report)
            @include('admin.presensi.partials.lembur-laporan-section', $report)
            <div class="page-break"></div>
        @endforeach
    @endforeach

</body>

</html>
