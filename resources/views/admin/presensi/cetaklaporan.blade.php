<!DOCTYPE html>
<html lang="en">

<head>

    {{-- ================================================== --}}
    {{-- Meta --}}
    {{-- ================================================== --}}
    <meta charset="utf-8">

    <title>
        Rekap Presensi Karyawan
    </title>

    {{-- ================================================== --}}
    {{-- Stylesheet --}}
    {{-- ================================================== --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <link rel="stylesheet"
        href="{{ asset('assets/css/cetaklaporan.css') }}">

</head>

<body class="A4">

    <?php
    function selisih($jam_in, $jam_out)
    {
        $awal = strtotime($jam_in);
        $akhir = strtotime($jam_out);

        $selisih = $akhir - $awal;

        $jam = floor($selisih / 3600);
        $menit = floor(($selisih % 3600) / 60);

        return $jam . " Jam " . $menit . " Menit";
    }
    ?>

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

                        PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }}
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
        {{-- Data Karyawan --}}
        {{-- ================================================== --}}
        <table class="tabeldatakaryawan">

            <tr>

                <td>NIK</td>
                <td>:</td>
                <td>{{ $karyawan->nik }}</td>

            </tr>

            <tr>

                <td>Nama Karyawan</td>
                <td>:</td>
                <td>{{ $karyawan->nama_lengkap }}</td>

            </tr>

            <tr>

                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $karyawan->jabatan }}</td>

            </tr>

            <tr>

                <td>Unit Kerja</td>
                <td>:</td>
                <td>{{ $karyawan->unit }}</td>

            </tr>

            <tr>

                <td>No. Hp</td>
                <td>:</td>
                <td>{{ $karyawan->no_hp }}</td>

            </tr>

        </table>

        <br>

        {{-- ================================================== --}}
        {{-- Tabel Presensi --}}
        {{-- ================================================== --}}
        <table class="tabelpresensi"
            width="100%"
            cellspacing="0"
            border="1">
        
            <tr>
        
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Keterangan</th>
                <th>Durasi Kerja</th>
                <th>Lembur</th>
                <th>Prorate</th>
                <th>WFH</th>
        
            </tr>
        
            @foreach ($presensi as $p)
        
                <tr>
        
                    <td align="center">
        
                        {{ $loop->iteration }}
        
                    </td>
        
                    <td align="center">
        
                        {{ date('d-m-Y', strtotime($p->tgl_presensi)) }}
        
                    </td>
        
                    <td align="center">
        
                        {{ $p->jam_in }}
        
                    </td>
        
                    <td align="center">
        
                        {{ $p->jam_out != null ? $p->jam_out : 'Belum Presensi' }}
        
                    </td>
        
                    <td>
        
                        @if ($p->jam_out == null)
        
                            Belum Presensi Pulang
        
                        @elseif ($p->terlambat > 0)
        
                            Terlambat {{ $p->terlambat }} Menit
        
                        @else
        
                            Tepat Waktu
        
                        @endif
        
                    </td>
        
                    <td align="center">
        
                        @if ($p->jam_out != null)
        
                            @php
                                $jmljamkerja = selisih($p->jam_in, $p->jam_out);
                            @endphp
        
                            {{ $jmljamkerja }}
        
                        @else
        
                            0 Jam 0 Menit
        
                        @endif
        
                    </td>
        
                    {{-- ================================================== --}}
                    {{-- Lembur --}}
                    {{-- ================================================== --}}
                    <td align="center">
        
                        @if (isset($lembur[$p->tgl_presensi]) &&
                                $lembur[$p->tgl_presensi]->durasi != 'Prorate')
        
                            {{ $lembur[$p->tgl_presensi]->durasi }}
        
                        @else
        
                            -
        
                        @endif
        
                    </td>
        
                    {{-- ================================================== --}}
                    {{-- Prorate --}}
                    {{-- ================================================== --}}
                    <td align="center">
        
                        @if (isset($lembur[$p->tgl_presensi]) &&
                                $lembur[$p->tgl_presensi]->durasi == 'Prorate')
        
                            1
        
                        @else
        
                            -
        
                        @endif
        
                    </td>
        
                    {{-- ================================================== --}}
                    {{-- Work From Home --}}
                    {{-- ================================================== --}}
                    <td align="center">
        
                        @if(isset($wfh[$p->tgl_presensi]))
        
                            ✓
        
                        @else
        
                            -
        
                        @endif
        
                    </td>
        
                </tr>
        
            @endforeach
            
                    {{-- ================================================== --}}
                    {{-- Total --}}
                    {{-- ================================================== --}}
                    <tr>
                    
                        <td colspan="5"
                            style="text-align: center;">
                    
                            <b>TOTAL</b>
                    
                        </td>
                    
                        {{-- ================================================== --}}
                        {{-- Total Jam Kerja --}}
                        {{-- ================================================== --}}
                        <td style="text-align: center;">
                    
                            <b>
                    
                                {{ $totalJamKerja }} Jam {{ $sisaMenitKerja }} Menit
                    
                            </b>
                    
                        </td>
                    
                        {{-- ================================================== --}}
                        {{-- Total Lembur --}}
                        {{-- ================================================== --}}
                        <td style="text-align: center;">
                    
                            <b>
                    
                                {{ $totalLembur }} Jam
                    
                            </b>
                    
                        </td>
                    
                        {{-- ================================================== --}}
                        {{-- Total Prorate --}}
                        {{-- ================================================== --}}
                        <td style="text-align: center;">
                    
                            <b>
                    
                                {{ $totalProrate }}x
                    
                            </b>
                    
                        </td>
                    
                        {{-- ================================================== --}}
                        {{-- Total Work From Home --}}
                        {{-- ================================================== --}}
                        <td style="text-align: center;">
                    
                            <b>
                    
                                {{ $totalWfh }} Hari
                    
                            </b>
                    
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