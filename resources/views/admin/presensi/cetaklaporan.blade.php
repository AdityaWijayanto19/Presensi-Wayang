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
    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">

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
                <th>Hari</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Durasi Terlambat</th>
                <th>Keterangan</th>
                <th>Durasi Kerja</th>
                <th>Lembur</th>
                <th>WFH</th>

            </tr>

            @foreach ($days as $day)
                @php
                    $ket = $day['keterangan'];
                    $ketClass = match (true) {
                        str_starts_with($ket, 'Tepat') => 'ket-tepat',
                        str_starts_with($ket, 'Terlambat') => 'ket-terlambat',
                        $ket === 'Belum Absen Pulang' => 'ket-belum',
                        $ket === 'Tidak Masuk Kerja' => 'ket-tidakmasuk',
                        str_starts_with($ket, 'Izin') || $ket === 'Sakit' => 'ket-izin',
                        $ket === 'Cuti' => 'ket-cuti',
                        $ket === 'WFH' => 'ket-wfh',
                        $ket === 'Libur' => 'ket-libur',
                        default => '',
                    };
                @endphp

                <tr class="{{ $day['is_minggu'] ? 'baris-minggu' : '' }}">

                    <td align="center">

                        {{ $loop->iteration }}

                    </td>

                    <td align="center">

                        @if ($day['is_minggu'])
                            <span class="hari-minggu">{{ $day['hari'] }}</span>
                        @else
                            {{ $day['hari'] }}
                        @endif

                    </td>

                    <td align="center">

                        {{ $day['tanggal']->format('d-m-Y') }}

                    </td>

                    <td align="center">

                        {{ $day['presensi'] ? $day['presensi']->jam_in : '-' }}

                    </td>

                    <td align="center">

                        {{ $day['presensi'] && $day['presensi']->jam_out != null ? $day['presensi']->jam_out : '-' }}

                    </td>

                    {{-- ================================================== --}}
                    {{-- Durasi Terlambat --}}
                    {{-- ================================================== --}}
                    <td align="center">

                        @if ($day['presensi'] && $day['presensi']->terlambat > 0)

                            {{ $day['presensi']->terlambat }} Menit

                        @else

                            -

                        @endif

                    </td>

                    {{-- ================================================== --}}
                    {{-- Keterangan --}}
                    {{-- ================================================== --}}
                    <td align="center">

                        <span class="{{ $ketClass }}">{{ $ket }}</span>

                    </td>

                    <td align="center">

                        @if ($day['presensi'] && $day['presensi']->jam_out != null)

                            {{ selisih($day['presensi']->jam_in, $day['presensi']->jam_out) }}

                        @else

                            0 Jam 0 Menit

                        @endif

                    </td>

                    {{-- ================================================== --}}
                    {{-- Lembur --}}
                    {{-- ================================================== --}}
                    <td align="center">

                        @if ($day['lembur_jam'] !== null)

                            {{ $day['lembur_jam'] }} jam

                        @else

                            -

                        @endif

                    </td>

                    {{-- ================================================== --}}
                    {{-- Work From Home --}}
                    {{-- ================================================== --}}
                    <td align="center">

                        @if ($day['wfh'])

                            @php $wfhStatus = $day['wfh']->status; @endphp

                            <span
                                class="wfh-badge {{ $wfhStatus->value === 'approved' ? 'wfh-approved' : ($wfhStatus->value === 'rejected' ? 'wfh-rejected' : 'wfh-unpaid') }}">
                                {{ $wfhStatus->label() }}
                            </span>

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

                        <td colspan="7"
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

                                {{ str_replace('.', ',', rtrim(rtrim(number_format((float) $totalLembur, 1, '.', ''), '0'), '.')) }}
                                Jam

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
