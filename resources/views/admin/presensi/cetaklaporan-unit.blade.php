<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <title>Laporan Presensi Karyawan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <link rel="stylesheet"
        href="{{ asset('assets/css/cetaklaporan.css') }}">

    <style>
        .tabelringkasan {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .tabelringkasan th,
        .tabelringkasan td {
            border: 1px solid #94a3b8;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }
        .tabelringkasan th {
            background-color: #f1f5f9;
            font-weight: 600;
            font-size: 9px;
            color: #334155;
        }
        .tabelringkasan td {
            font-size: 9px;
        }
        .tabelringkasan td.text-left {
            text-align: left;
        }
        .tabelringkasan tr.baris-total td {
            background-color: #f1f5f9;
            font-weight: 700;
        }
        .info-unit {
            margin-bottom: 12px;
            font-size: 11px;
            color: #334155;
        }
        .info-unit td {
            border: none;
            padding: 2px 8px 2px 0;
            font-size: 11px;
        }
    </style>

</head>

<body class="A4">

    <section class="sheet padding-10mm">

        {{-- ================================================== --}}
        {{-- Header Laporan --}}
        {{-- ================================================== --}}
        <table>

            <tr>

                <td>

                    @php
                        $logo = public_path('assets/img/login/logo_buat_export.jpg');
                    @endphp

                    <img src="{{ $logo }}"
                        width="170">

                </td>

                <td>

                    <h2>

                        LAPORAN PRESENSI KARYAWAN
                        <br>

                        PERIODE {{ strtoupper($startDate->format('d M Y')) }} - {{ strtoupper($endDate->format('d M Y')) }}
                        <br>

                        PT WAYANG ARTHASENA GROUP

                    </h2>

                    <p>

                        Jl. Kedondong No.5A,
                        RT.11/RW.9,
                        Rawamangun,

                        <br>

                        Kec. Pulo Gadung,
                        Kota Jakarta Timur,

                        <br>

                        DKI Jakarta 13220

                    </p>

                </td>

            </tr>

        </table>

        {{-- ================================================== --}}
        {{-- Info Unit --}}
        {{-- ================================================== --}}
        <table class="info-unit">
            <tr>
                <td><b>Unit Kerja</b></td>
                <td>:</td>
                <td>{{ $unitData->perusahaan }}</td>
            </tr>
        </table>

        <br>

        {{-- ================================================== --}}
        {{-- Tabel Ringkasan --}}
        {{-- ================================================== --}}
        <table class="tabelringkasan"
            width="100%"
            cellspacing="0"
            border="1">

            <tr>

                <th>No.</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Total Hari Hadir</th>
                <th>Total Jam Kerja</th>
                <th>Total Lembur (Jam)</th>
                <th>Total Prorate</th>
                <th>Total WFH (Hari)</th>
                <th>Total Keterlambatan (Menit)</th>
                <th>Total Izin</th>
                <th>Total Sakit</th>
                <th>Total Unpaid</th>
                <th>Total Cuti</th>

            </tr>

            @foreach ($dataKaryawan as $index => $dk)
                <tr>

                    <td align="center">
                        {{ $index + 1 }}
                    </td>

                    <td align="center">
                        {{ $dk['nik'] }}
                    </td>

                    <td class="text-left">
                        {{ $dk['nama_lengkap'] }}
                    </td>

                    <td class="text-left">
                        {{ $dk['jabatan'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalHadir'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalJamKerja'] }} Jam {{ $dk['sisaMenitKerja'] }} Menit
                    </td>

                    <td align="center">
                        {{ $dk['totalLembur'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalProrate'] }}x
                    </td>

                    <td align="center">
                        {{ $dk['totalWfh'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalTerlambatMenit'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalIzin'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalSakit'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalUnpaid'] }}
                    </td>

                    <td align="center">
                        {{ $dk['totalCuti'] }}
                    </td>

                </tr>
            @endforeach

            <tr class="baris-total">

                <td colspan="4" align="center">
                    TOTAL
                </td>

                <td align="center">
                    {{ $grandTotal['totalHadir'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalJamKerja'] }} Jam {{ $grandTotal['sisaMenitKerja'] }} Menit
                </td>

                <td align="center">
                    {{ $grandTotal['totalLembur'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalProrate'] }}x
                </td>

                <td align="center">
                    {{ $grandTotal['totalWfh'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalTerlambatMenit'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalIzin'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalSakit'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalUnpaid'] }}
                </td>

                <td align="center">
                    {{ $grandTotal['totalCuti'] }}
                </td>

            </tr>

        </table>

        {{-- ================================================== --}}
        {{-- Tanda Tangan --}}
        {{-- ================================================== --}}
        <br>
        <br>

        <table class="ttd">

            <tr>

                <td>

                    Jakarta,
                    {{ date('d') }}
                    {{ $namabulan[date('n')] }}
                    {{ date('Y') }}

                    <br>
                    <br>

                    Mengetahui,

                    <br>
                    <br>

                    <b>Manager HRGA</b>

                </td>

            </tr>

            <tr>

                <td class="jarakttd"></td>

            </tr>

            <tr>

                <td>

                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>

                    <b>

                        Naufail Imamuddin

                    </b>

                </td>

            </tr>

        </table>

    </section>

</body>

</html>
