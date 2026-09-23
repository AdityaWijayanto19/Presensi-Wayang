<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Formulir Lembur</title>

    <style>
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

        .document {
            width: 100%;
            position: relative;
        }

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

        .title {
            text-align: center;
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
            height: auto;
            padding: 5px 9px;
            font-size: 10px;
            line-height: 1.4;
        }

        .info-label {
            font-weight: bold;
        }

        .activity-cell {
            height: auto;
            min-height: 50px;
            padding: 5px 9px;
            vertical-align: top;
        }

        .activity-content {
            margin-top: 3px;
            line-height: 1.55;
        }

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
                <div class="company-name">PT Wayang Arthasena Group</div>
                <div class="company-text">Jl. Kedondong No. 5A, Rawamangun</div>
                <div class="company-text">Pulo Gadung, Jakarta Timur - Indonesia</div>
                <div class="company-text">Telephone: +6221 38859001</div>
                <div class="company-text">Fax: +6221 38859001</div>
            </div>
        </div>


        {{-- TITLE --}}
        <div class="title">
            <div class="title-main">Formulir</div>
            <div class="title-sub">Surat Perintah Lembur</div>
        </div>


        {{-- INFORMASI LEMBUR --}}
        <table class="form-table">

            <tr>
                <td class="info-cell">
                    <span class="info-label">Nama Pengaju:</span>
                    {{ $nama_lengkap }}
                </td>
                <td class="info-cell">
                    <span class="info-label">Jabatan:</span>
                    {{ $jabatan }}
                </td>
            </tr>

            <tr>
                <td class="info-cell">
                    <span class="info-label">Posisi:</span>
                    {{ $posisi }}
                </td>
                <td class="info-cell">
                    <span class="info-label">Nama Perusahaan:</span>
                    {{ $perusahaan }}
                </td>
            </tr>

            <tr>
                <td class="activity-cell">
                    <span class="info-label">Keterangan:</span>
                    <div class="activity-content">
                        {!! nl2br(e($keterangan)) !!}
                    </div>
                </td>
                <td class="activity-cell">
                    <span class="info-label">Tanggal Lembur:</span>
                    <div class="activity-content">
                        {{ \Carbon\Carbon::parse($tgl_lembur)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </div>
                </td>
            </tr>

            @if (!empty($jam_mulai) && !empty($jam_selesai))
            <tr>
                <td class="info-cell">
                    <span class="info-label">Rencana Waktu:</span>
                    {{ $jam_mulai }} - {{ $jam_selesai }}
                </td>
                <td class="info-cell">
                    <span class="info-label">Durasi:</span>
                    {{ number_format($durasi_jam, 1, ',', '') }} jam
                </td>
            </tr>
            @endif

        </table>


        {{-- APPROVAL --}}
        <table class="form-table approval">

            <tr>
                <td class="approval-header">Diajukan oleh</td>
                <td class="approval-header">Mengetahui</td>
                <td class="approval-header">Menyetujui</td>
            </tr>

            <tr>
                {{-- PEMOHON --}}
                <td class="approval-body">
                    <div class="signature-space">
                        @if (file_exists(public_path('assets/img/stempel-pengaju.png')))
                            <img src="{{ public_path('assets/img/stempel-pengaju.png') }}" class="stamp"
                                alt="Submission">
                        @endif
                    </div>
                    <div class="signature-name">{{ $nama_lengkap }}</div>
                    <div class="signature-role">{{ $jabatan }}</div>
                </td>

                {{-- ATASAN --}}
                <td class="approval-body">
                    <div class="signature-space">
                        @if (!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif
                    </div>
                    <div class="signature-name">{{ $nama_atasan }}</div>
                    <div class="signature-role">{{ $jabatan_atasan }}</div>
                </td>

                {{-- APPROVER --}}
                <td class="approval-body">
                    <div class="signature-space">
                        @if (!empty($stempelPath) && file_exists(public_path($stempelPath)))
                            <img src="{{ public_path($stempelPath) }}" class="stamp" alt="Stempel">
                        @endif
                    </div>
                    <div class="signature-name">Naufail Imamuddin</div>
                    <div class="signature-role">Manager HRGA</div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
