<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Formulir Flexible Working Space</title>

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
       FULL WIDTH - MENTOK UJUNG KERTAS
    ========================================================= */

        .header {
            width: 100%;
            height: 125px;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }


        /*
     * header-surat.png
     *
     * Gambar ini adalah background/kop surat,
     * bukan logo kecil.
     */

        .logo {
            position: absolute;

            left: 0;
            top: 0;

            width: 100%;
            height: 118px;

            display: block;

            object-fit: fill;
        }


        /* =========================================================
       INFORMASI PERUSAHAAN
    ========================================================= */

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
       ORNAMENT
       
       Kalau ornament terpisah dari header-surat.png,
       tetap bisa digunakan.
    ========================================================= */

        .ornament {
            position: absolute;

            left: 0;
            top: 0;

            width: 115px;

            opacity: 0.45;

            z-index: 2;
        }

        .ornament-right {
            position: absolute;

            right: 0;
            top: 0;

            width: 115px;

            opacity: 0.45;

            z-index: 2;
        }


        /* =========================================================
       TITLE
    ========================================================= */

        .title {
            text-align: center;

            /*
         * Header sudah full-width.
         * Setelah header, title kembali mengikuti
         * margin dokumen 15mm.
         */

            margin-left: 15mm;
            margin-right: 15mm;

            margin-top: 5px;
            margin-bottom: 25px;
        }

        .title-main {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .title-sub {
            font-size: 15px;
            font-weight: bold;
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
       KEGIATAN
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
       APPROVAL TABLE
    ========================================================= */

        .approval {
            width: calc(100% - 30mm);

            margin-left: 15mm;
            margin-right: 15mm;

            margin-top: 0;
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


        {{-- =========================================================
         HEADER
    ========================================================== --}}

        <div class="header">

            {{-- Ornamen kiri --}}
            {{-- Aktifkan kalau file memang tersedia --}}
            {{--
        @if (file_exists(public_path('images/wfh/ornament-left.png')))
            <img
                src="{{ public_path('images/wfh/ornament-left.png') }}"
                class="ornament"
            >
        @endif
        --}}


            {{-- Ornamen kanan --}}
            {{--
        @if (file_exists(public_path('images/wfh/ornament-right.png')))
            <img
                src="{{ public_path('images/wfh/ornament-right.png') }}"
                class="ornament-right"
            >
        @endif
        --}}


            {{-- Logo --}}
            @if (!empty($headerSuratPath) && file_exists(public_path($headerSuratPath)))
                <img src="{{ public_path($headerSuratPath) }}" class="logo" alt="Logo">
            @endif


            {{-- Informasi perusahaan --}}
            <div class="company">

                <div class="company-name">
                    {{ $perusahaan }}
                </div>

                <div class="company-text">
                    Jl. Kedondong No. 5A, Rawamangun
                </div>

                <div class="company-text">
                    Pulo Gadung, Jakarta Timur - Indonesia
                </div>

                <div class="company-text">
                    Telephone: +6221 38859001
                </div>

                <div class="company-text">
                    Fax: +6221 38859001
                </div>

            </div>

        </div>


        {{-- =========================================================
         TITLE
    ========================================================== --}}

        <div class="title">

            <div class="title-main">
                Formulir
            </div>

            <div class="title-sub">
                Flexible Working Space
            </div>

        </div>


        {{-- =========================================================
         INFORMASI WFH
    ========================================================== --}}

        <table class="form-table">

            <tr>

                <td class="info-cell">

                    <span class="info-label">
                        Nama Pengaju:
                    </span>

                    {{ $nama_lengkap }}

                </td>


                <td class="info-cell">

                    <span class="info-label">
                        Nama Pekerjaan:
                    </span>

                    {{ $posisi }}

                </td>

            </tr>


            <tr>

                <td class="info-cell">

                    <span class="info-label">
                        Jabatan:
                    </span>

                    {{ $jabatan }}

                </td>


                <td class="info-cell">

                    <span class="info-label">
                        Nama Perusahaan:
                    </span>

                    {{ $perusahaan }}

                </td>

            </tr>


            <tr>

                <td class="activity-cell">

                    <span class="info-label">
                        Kegiatan:
                    </span>

                    <div class="activity-content">
                        {!! nl2br(e($deskripsi_pekerjaan)) !!}
                    </div>

                </td>


                <td class="activity-cell">

                    <span class="info-label">
                        Tanggal:
                    </span>

                    <div class="activity-content">

                        {{ \Carbon\Carbon::parse($tgl_wfh)->locale('id')->isoFormat('dddd, D MMMM Y') }}

                    </div>

                </td>

            </tr>

        </table>


        {{-- =========================================================
         APPROVAL
    ========================================================== --}}

        <table class="form-table approval">

            <tr>

                <td class="approval-header">
                    Diajukan oleh
                </td>

                <td class="approval-header">
                    Mengetahui
                </td>

                <td class="approval-header">
                    Menyetujui
                </td>

            </tr>


            <tr>

                {{-- =================================================
                 PEMOHON
            ================================================== --}}

                <td class="approval-body">

                    <div class="signature-space">

                        {{-- TTD pemohon kalau nanti tersedia --}}

                    </div>

                    <div class="signature-name">
                        {{ $nama_lengkap }}
                    </div>

                    <div class="signature-role">
                        Karyawan
                    </div>

                </td>


                {{-- =================================================
                 ATASAN
            ================================================== --}}

                <td class="approval-body">

                    <div class="signature-space">

                        @if (!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif

                    </div>

                    <div class="signature-name">
                        {{ $nama_atasan }}
                    </div>

                    <div class="signature-role">
                        {{ $jabatan_atasan }}
                    </div>

                </td>


                {{-- =================================================
                 APPROVER
            ================================================== --}}

                <td class="approval-body">

                    <div class="signature-space">

                        @if (!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif

                    </div>

                    <div class="signature-name">
                        {{ $nama_approver }}
                    </div>

                    <div class="signature-role">
                        {{ $jabatan_approver }}
                    </div>

                </td>

            </tr>

        </table>

    </div>

</body>

</html>
