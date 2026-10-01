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
        <div class="title-main">Laporan Hasil Pekerjaan</div>
        <div class="title-sub">Lembur</div>
        <div class="title-sub" style="margin-top:4px;">Tanggal: {{ now('Asia/Jakarta')->format('d/m/Y H:i') }}</div>
    </div>


    {{-- INFORMASI LAPORAN --}}
    <table class="form-table">

        <tr>
            <td class="info-cell">
                <span class="info-label">Nama Karyawan:</span>
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
                <span class="info-label">Perusahaan:</span>
                {{ $perusahaan }}
            </td>
        </tr>

        <tr>
            <td class="activity-cell">
                <span class="info-label">Tanggal Lembur:</span>
                <div class="activity-content">
                    {{ \Carbon\Carbon::parse($tgl_lembur)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </div>
            </td>
            <td class="activity-cell">
                <span class="info-label">Durasi:</span>
                <div class="activity-content">
                    Mulai: {{ $jam_mulai }} WIB — Selesai: {{ $jam_selesai }} WIB<br>
                    Total: {{ $durasi }}
                </div>
            </td>
        </tr>

        <tr>
            <td class="activity-cell" colspan="2">
                <span class="info-label">Keterangan:</span>
                <div class="activity-content">
                    {{ $keterangan ?? '-' }}
                </div>
            </td>
        </tr>

    </table>


    {{-- DESKRIPSI PEKERJAAN --}}
    <table class="form-table" style="margin-top:0;">
        <tr>
            <td class="activity-cell" style="height:auto;min-height:72px;">
                <span class="info-label">Deskripsi Pekerjaan:</span>
                <div class="activity-content" style="white-space:pre-wrap;">{{ $deskripsi_pekerjaan }}</div>
            </td>
        </tr>
    </table>


    {{-- FOTO MULAI & SELESAI --}}
    @if (!empty($foto_mulai) || !empty($foto_selesai))
        <div class="foto-section">
            <div class="foto-label">Bukti Foto Lembur</div>
            <div class="foto-grid">
                @if (!empty($foto_mulai))
                    @php $mulaiPath = storage_path('app/public/uploads/lembur/' . $foto_mulai); @endphp
                    @if (file_exists($mulaiPath))
                        <div class="foto-item">
                            <div class="foto-cap">Mulai Lembur</div>
                            <img src="{{ $mulaiPath }}" alt="Foto Mulai Lembur">
                        </div>
                    @endif
                @endif
                @if (!empty($foto_selesai))
                    @php $selesaiPath = storage_path('app/public/uploads/lembur/' . $foto_selesai); @endphp
                    @if (file_exists($selesaiPath))
                        <div class="foto-item">
                            <div class="foto-cap">Selesai Lembur</div>
                            <img src="{{ $selesaiPath }}" alt="Foto Selesai Lembur">
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif


    {{-- FOTO HASIL PEKERJAAN --}}
    @if (!empty($laporan_images) && count($laporan_images) > 0)
        <div class="foto-section">
            <div class="foto-label">Foto Hasil Pekerjaan</div>
            <div class="foto-grid">
                @foreach ($laporan_images as $img)
                    @php $imgPath = storage_path('app/public/' . $img); @endphp
                    @if (file_exists($imgPath))
                        <div class="foto-item">
                            <img src="{{ $imgPath }}" alt="Foto {{ $loop->iteration }}">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif


    {{-- APPROVAL / TANDA TANGAN --}}
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
