<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan WFH - {{ $nama_lengkap }}</title>

    <style>
        /* =========================================================
       PAGE
    ========================================================= */

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1c1917;
            background: #ffffff;
        }


        /* =========================================================
       DOCUMENT
    ========================================================= */

        .document {
            width: 100%;
            position: relative;
        }


        /* =========================================================
       HEADER / KOP SURAT
    ========================================================= */

        .header {
            width: 100%;
            height: 125px;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 118px;
            display: block;
            object-fit: fill;
        }

        .company {
            position: absolute;
            right: 25mm;
            top: 22px;
            width: 290px;
            text-align: right;
            line-height: 1.45;
            z-index: 2;
        }

        .company-name {
            font-size: 11px;
            font-weight: bold;
            color: #7a5234;
        }

        .company-text {
            font-size: 8.5px;
            color: #a08b78;
        }


        /* =========================================================
       TITLE
    ========================================================= */

        .title {
            text-align: center;
            margin-left: 15mm;
            margin-right: 15mm;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        .title-main {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .title-sub {
            font-size: 12px;
            color: #57534e;
        }


        /* =========================================================
       MAIN INFORMATION TABLE
    ========================================================= */

        table.form-table {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.form-table td {
            border: 1px solid #777;
            vertical-align: top;
        }

        .info-cell {
            width: 50%;
            height: 53px;
            padding: 8px 9px;
            font-size: 10px;
            line-height: 1.5;
        }

        .info-label {
            font-weight: bold;
        }


        /* =========================================================
       KETERANGAN / DESKRIPSI
    ========================================================= */

        .activity-cell {
            height: 72px;
            padding: 8px 9px;
            vertical-align: top;
        }

        .activity-content {
            margin-top: 3px;
            line-height: 1.55;
        }


        /* =========================================================
       FOTO GRID
    ========================================================= */

        .foto-section {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            margin-top: 10px;
        }

        .foto-section .foto-label {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7a5234;
            margin-bottom: 6px;
        }

        .foto-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .foto-grid img {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #f0ece8;
            border-radius: 4px;
        }


        /* =========================================================
       APPROVAL TABLE
    ========================================================= */

        .approval {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            margin-top: 10px;
        }

        .approval td {
            width: 33.333%;
            border: 1px dotted #777;
            text-align: center;
        }

        .approval-header {
            height: 38px;
            vertical-align: middle !important;
            font-size: 10px;
        }

        .approval-body {
            height: 190px;
            vertical-align: bottom !important;
            padding-bottom: 7px;
        }


        /* =========================================================
       SIGNATURE
    ========================================================= */

        .signature-space {
            height: 125px;
            position: relative;
        }

        .stamp {
            position: absolute;
            width: 80px;
            height: 80px;
            object-fit: contain;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.85;
        }

        .signature-name {
            font-size: 10px;
            text-decoration: underline;
        }

        .signature-role {
            margin-top: 2px;
            font-size: 10px;
        }


        /* =========================================================
       SMALL META
    ========================================================= */

        .meta {
            width: calc(100% - 30mm);
            margin-left: 15mm;
            margin-right: 15mm;
            margin-top: 12px;
            font-size: 7px;
            color: #a8a29e;
            text-align: right;
        }
    </style>
</head>


<body>

    <div class="document">

        {{-- HEADER --}}
        <div class="header">
            @if (!empty($headerSuratPath) && file_exists(public_path($headerSuratPath)))
                <img src="{{ public_path($headerSuratPath) }}" class="logo" alt="Logo">
            @endif

            <div class="company">
                <div class="company-name">{{ $perusahaan }}</div>
                <div class="company-text">Jl. Kedondong No. 5A, Rawamangun</div>
                <div class="company-text">Pulo Gadung, Jakarta Timur - Indonesia</div>
                <div class="company-text">Telephone: +6221 38859001</div>
                <div class="company-text">Fax: +6221 38859001</div>
            </div>
        </div>


        {{-- TITLE --}}
        <div class="title">
            <div class="title-main">Laporan Hasil Pekerjaan</div>
            <div class="title-sub">Work From Home</div>
            <div class="title-sub" style="margin-top:4px;">
                No: LAPORAN-{{ date('Ymd', strtotime($tgl_wfh)) }}-{{ $nik }}
                &nbsp;|&nbsp;
                Tanggal: {{ date('d/m/Y H:i') }}
            </div>
        </div>


        {{-- INFORMASI LAPORAN --}}
        <table class="form-table">

            <tr>
                <td class="info-cell">
                    <span class="info-label">Nama Karyawan:</span>
                    {{ $nama_lengkap }}
                </td>
                <td class="info-cell">
                    <span class="info-label">Posisi:</span>
                    {{ $posisi }}
                </td>
            </tr>

            <tr>
                <td class="info-cell">
                    <span class="info-label">Jabatan:</span>
                    {{ $jabatan }}
                </td>
                <td class="info-cell">
                    <span class="info-label">Unit / Perusahaan:</span>
                    {{ $unit }} — {{ $perusahaan }}
                </td>
            </tr>

            <tr>
                <td class="activity-cell">
                    <span class="info-label">Tanggal WFH:</span>
                    <div class="activity-content">
                        {{ \Carbon\Carbon::parse($tgl_wfh)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </div>
                </td>
                <td class="activity-cell">
                    <span class="info-label">Lokasi Absen Masuk:</span>
                    <div class="activity-content">
                        {{ $live_location ?? '-' }}
                    </div>
                </td>
            </tr>

            <tr>
                <td class="activity-cell" colspan="2">
                    <span class="info-label">Keterangan WFH:</span>
                    <div class="activity-content">
                        {{ $keterangan ?? '-' }}
                    </div>
                </td>
            </tr>

        </table>


        {{-- DESKRIPSI HASIL PEKERJAAN --}}
        <table class="form-table" style="margin-top:0;">
            <tr>
                <td class="activity-cell" style="height:auto;min-height:72px;">
                    <span class="info-label">Deskripsi Hasil Pekerjaan:</span>
                    <div class="activity-content" style="white-space:pre-wrap;">{{ $laporan_deskripsi }}</div>
                </td>
            </tr>
        </table>


        {{-- FOTO HASIL PEKERJAAN --}}
        @if(!empty($laporan_images) && count($laporan_images) > 0)
        <div class="foto-section">
            <div class="foto-label">Foto Hasil Pekerjaan</div>
            <div class="foto-grid">
                @foreach($laporan_images as $img)
                    @php $imgPath = storage_path('app/public/' . $img); @endphp
                    @if(file_exists($imgPath))
                        <img src="file://{{ $imgPath }}" alt="Foto {{ $loop->iteration }}">
                    @endif
                @endforeach
            </div>
        </div>
        @endif


        {{-- APPROVAL / TANDA TANGAN --}}
        <table class="form-table approval">

            <tr>
                <td class="approval-header">Disetujui Oleh</td>
                <td class="approval-header">Mengetahui</td>
                <td class="approval-header">Dilaksanakan Oleh</td>
            </tr>

            <tr>

                {{-- ATASAN --}}
                <td class="approval-body">
                    <div class="signature-space">
                        @if(!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif
                    </div>
                    <div class="signature-name">{{ $nama_atasan ?? '-' }}</div>
                    <div class="signature-role">{{ $jabatan_atasan ?? '-' }}</div>
                </td>

                {{-- ADMIN HR --}}
                <td class="approval-body">
                    <div class="signature-space">
                        @if(!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif
                    </div>
                    <div class="signature-name">Administrator HR</div>
                    <div class="signature-role">HRGA</div>
                </td>

                {{-- KARYAWAN --}}
                <td class="approval-body">
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $nama_lengkap }}</div>
                    <div class="signature-role">{{ $jabatan }}</div>
                </td>

            </tr>

        </table>

    </div>

    <div class="meta">
        Dokumen ini digenerate otomatis oleh Sistem Presensi Digital — {{ date('d/m/Y H:i:s') }}
    </div>

</body>

</html>
