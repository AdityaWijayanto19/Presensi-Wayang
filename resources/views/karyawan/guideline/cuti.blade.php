@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/guideline" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Panduan Cuti</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $sections = [
            ['id' => 'apa', 'label' => 'Apa itu Cuti', 'icon' => 'circle-help'],
            ['id' => 'beda', 'label' => 'Beda & Izin/Lembur', 'icon' => 'git-compare'],
            ['id' => 'kuota', 'label' => 'Kuota & Durasi', 'icon' => 'calendar-check'],
            ['id' => 'cara', 'label' => 'Cara Ajukan', 'icon' => 'upload'],
            ['id' => 'status', 'label' => 'Status & Approval', 'icon' => 'check-circle-2'],
            ['id' => 'aturan', 'label' => 'Aturan Tanggal & File', 'icon' => 'file-check'],
            ['id' => 'efek', 'label' => 'Efek ke Laporan', 'icon' => 'clipboard-list'],
            ['id' => 'larangan', 'label' => 'Boleh & Tidak', 'icon' => 'shield-check'],
            ['id' => 'faq', 'label' => 'Tanya Jawab', 'icon' => 'messages-square'],
        ];
    @endphp

    {{-- Chip nav (mobile & tablet) --}}
    <div class="mt-[70px] sticky top-[calc(env(safe-area-inset-top)_+_56px)] z-30 bg-[#e9ecef]/95 backdrop-blur border-b border-[#e7e5e4] lg:hidden">
        <div class="flex gap-2 overflow-x-auto px-3 py-2.5" style="-webkit-overflow-scrolling: touch;">
            @foreach ($sections as $s)
                <a href="#{{ $s['id'] }}"
                    class="shrink-0 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#57534e] bg-white border border-[#e7e5e4] rounded-full px-3 py-1.5 no-underline whitespace-nowrap active:bg-stone-100">
                    <i data-lucide="{{ $s['icon'] }}" style="width:12px;height:12px;"></i>
                    {{ $s['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-3 lg:mt-[86px]">
        <div class="w-full px-3 max-w-6xl mx-auto lg:grid lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-8 lg:px-6">

            {{-- Sidebar TOC (desktop) --}}
            <aside class="hidden lg:block">
                <nav class="sticky top-[calc(env(safe-area-inset-top)_+_86px)] pt-1 pb-8">
                    <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase mb-2.5">Daftar Isi</div>
                    <ul class="space-y-1">
                        @foreach ($sections as $s)
                            <li>
                                <a href="#{{ $s['id'] }}"
                                    class="toc-link flex items-center gap-2 text-[13px] text-[#57534e] no-underline rounded-lg px-2.5 py-2 hover:bg-white hover:text-coklat transition-colors"
                                    data-target="{{ $s['id'] }}">
                                    <i data-lucide="{{ $s['icon'] }}" style="width:14px;height:14px;"></i>
                                    <span>{{ $s['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </aside>

            {{-- Konten --}}
            <main class="pb-8 space-y-3 max-w-3xl lg:pt-1">

                {{-- Hero --}}
                <section class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="palmtree" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-[16px] font-bold text-[#1c1917] leading-tight">Panduan Cuti Tahunan</h1>
                            <p class="text-[13px] text-[#57534e] mt-1.5 leading-relaxed">
                                Pengajuan cuti kini <b>2 langkah + persetujuan superadmin</b>: isi data &amp; cetak
                                formulir PDF, tanda tangani, lalu upload hasil scan. Kuota baru berkurang saat disetujui.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- 1. Apa itu Cuti --}}
                <section id="apa" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                        Apa itu Cuti di Aplikasi Ini?
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed">
                        Pengajuan cuti tahunan dilakukan dalam <b>2 langkah</b>:
                        (1) isi data pengajuan → sistem membuat <b>formulir PDF surat cuti</b> yang kamu cetak
                        &amp; tanda tangani, (2) upload file hasil scan/tfoto surat tersebut.
                        Setelah itu pengajuan <b>menunggu persetujuan superadmin</b> — kuota baru berkurang
                        saat disetujui.
                    </p>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="info" class="text-emerald-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-emerald-900">
                            Satu tanggal hanya boleh <b>satu kali</b> dipilih sebagai cuti (draft, menunggu, dan
                            yang disetujui memblokir tanggal). Tanggal boleh <b>tidak berurutan</b> dan boleh
                            <b>tanggal lampau</b>. Jika sisa kuota 0, form diajukan diblokir.
                        </p>
                    </div>
                </section>

                {{-- 2. Beda dengan Izin/Lembur --}}
                <section id="beda" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                        Bedanya dengan Izin / Lembur / WFH
                    </h2>

                    <div class="overflow-x-auto -mx-1 px-1">
                        <table class="w-full text-left border-collapse min-w-[320px]">
                            <thead>
                                <tr class="border-b border-[#e7e5e4]">
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Aspek</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Izin / Lembur / WFH</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Cuti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece8]">
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Approval</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Atasan → HR (berlapis)</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Superadmin saja (tanpa atasan)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Status</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Pending atasan / HR / approved</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Draft → Menunggu → Disetujui / Ditolak</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Kuota berkurang</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ikut aturan fitur</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Saat disetujui superadmin</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Dokumen</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">PDF form otomatis</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Formulir PDF dicetak &amp; ditandatangani, lalu diupload</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Efek ke absen</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ada (mis. Pulang Cepat, blokir lembur)</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Tidak memblokir absen</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 3. Kuota & Durasi --}}
                <section id="kuota" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                        Kuota &amp; Durasi Cuti
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-3">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                                <i data-lucide="calendar-check" style="width:15px;height:15px;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-[#1c1917]">Jatah cuti</div>
                                <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">
                                    Default <b>12 hari</b>, maksimal 12. Diatur admin lewat data karyawan.
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                                <i data-lucide="hourglass" style="width:15px;height:15px;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-[#1c1917]">Maks. per pengajuan</div>
                                <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">
                                    <b>1–3 hari</b> (pilihan form: min(3, sisa kuota)).
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto -mx-1 px-1">
                        <table class="w-full text-left border-collapse min-w-[320px]">
                            <thead>
                                <tr class="border-b border-[#e7e5e4]">
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Aturan</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Nilai / Syarat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece8]">
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Sisa kuota</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">max(0, jatah − total <b>disetujui</b>)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Draft &amp; menunggu persetujuan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Belum mengurangi kuota</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Durasi per pengajuan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">1, 2, atau 3 hari</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Jumlah tanggal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Wajib = durasi</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Sisa = 0</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Pengajuan diblokir — hubungi HR</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Saat approve</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Kuota dicek ulang — ditolak otomatis jika melebihi sisa</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Hapus cuti (admin)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Kuota otomatis kembali</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="calculator" class="text-violet-600 shrink-0 mt-0.5" style="width:16px;height:18px;"></i>
                        <p class="text-[12px] leading-relaxed text-violet-900">
                            <b>Contoh:</b> jatah 12, sudah <b>disetujui</b> 2 + 3 hari → sisa 7.
                            Pengajuan berikutnya maksimal <b>min(3, 7) = 3</b> hari.
                            Sisa dihitung dari <b>seluruh riwayat yang disetujui</b> (bukan reset otomatis tiap tahun).
                        </p>
                    </div>
                </section>

                {{-- 4. Cara Ajukan --}}
                <section id="cara" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
                        Cara Ajukan Cuti (2 Langkah)
                    </h2>

                    <div class="text-[12.5px] font-bold text-[#1c1917] mb-1.5">Langkah 1 — Isi data &amp; cetak formulir</div>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka menu <b>Pengajuan → Cuti Tahunan</b> (atau langsung <b>/cuti</b>).</li>
                        <li>Lihat kartu <b>Kuota</b> — pastikan sisa &gt; 0.</li>
                        <li>Tap tombol <b>+</b> / <b>Ajukan Cuti</b> → form langkah 1 muncul.</li>
                        <li>Pilih <b>Durasi Cuti</b> (1–3 hari, sesuai sisa).</li>
                        <li>Isi <b>Tanggal Cuti</b> sebanyak durasi (boleh tidak berurutan / lampau).</li>
                        <li>Isi <b>Keterangan</b> (opsional, maks. 500 karakter).</li>
                        <li>Tekan <b>Selanjutnya</b> → sistem membuat <b>formulir PDF</b> surat cuti
                            (kolom tanda tangan dikosongkan untuk tanda tangan manual).</li>
                        <li><b>Download &amp; cetak</b> formulir PDF tersebut, lalu <b>tanda tangani</b> di tempat
                            (Diajukan oleh / Mengetahui / Menyetujui).</li>
                    </ol>

                    <div class="text-[12.5px] font-bold text-[#1c1917] mb-1.5 mt-3">Langkah 2 — Upload file hasil tanda tangan</div>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka pengajuan berstatus <b>Draft</b> di riwayat → tap <b>Upload File</b>.</li>
                        <li>Upload <b>foto/scan surat cuti yang sudah ditandatangani</b> —
                            JPG/JPEG/PNG/PDF, maks. 4&nbsp;MB.</li>
                        <li>Tekan <b>Upload &amp; Ajukan</b> → status berubah menjadi
                            <b>Menunggu Persetujuan</b>.</li>
                        <li>Tunggu superadmin menyetujui / menolak. Notifikasi dikirim jika ada keputusan.</li>
                    </ol>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="triangle-alert" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-amber-900">
                            Upload file <b>setelah</b> formulir PDF dicetak dan ditandatangani.
                            Kuota <b>belum berkurang</b> saat draft/upload — baru berkurang saat
                            <b>disetujui superadmin</b>.
                        </p>
                    </div>
                </section>

                {{-- 5. Status & Approval --}}
                <section id="status" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
                        Status Pengajuan &amp; Persetujuan
                    </h2>

                    <div class="overflow-x-auto -mx-1 px-1">
                        <table class="w-full text-left border-collapse min-w-[320px]">
                            <thead>
                                <tr class="border-b border-[#e7e5e4]">
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Status</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Artinya</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Yang bisa dilakukan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece8]">
                                <tr>
                                    <td class="py-2.5 pr-3"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Draft</span></td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Data tersimpan, PDF sudah dibuat, belum upload file</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Edit data, Upload File, Hapus, Download PDF</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 pr-3"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700 border border-amber-200">Menunggu Persetujuan</span></td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">File sudah diupload, menunggu keputusan superadmin</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Lihat status &amp; file saja</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 pr-3"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">Disetujui</span></td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Cuti resmi — kuota sudah berkurang, muncul di laporan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Lihat/download file &amp; formulir</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 pr-3"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-100 text-rose-700 border border-rose-200">Ditolak</span></td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ditolak superadmin — alasan ditampilkan. Tanggal tidak memblokir cuti baru &amp; kuota tidak berkurang</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5"><b>Upload Ulang</b> file (data tidak perlu diulang) atau Hapus</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="shield-check" class="text-sky-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-sky-900">
                            Persetujuan dilakukan <b>hanya oleh superadmin</b> (permission <b>Cuti Approve</b>)
                            di panel <b>Data Cuti</b>. Jika ditolak, kamu cukup memperbaiki &amp; mengupload
                            ulang file surat yang sudah ditandatangani — tidak perlu mengulang langkah 1.
                        </p>
                    </div>
                </section>

                {{-- 6. Aturan Tanggal & File --}}
                <section id="aturan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
                        Aturan Tanggal &amp; Dokumen
                    </h2>

                    <div class="overflow-x-auto -mx-1 px-1">
                        <table class="w-full text-left border-collapse min-w-[320px]">
                            <thead>
                                <tr class="border-b border-[#e7e5e4]">
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Aturan</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Ketentuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece8]">
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Urutan tanggal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Boleh tidak berurutan</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tanggal lampau</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Boleh</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Duplikat dalam form</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Tidak boleh</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tanggal sama dgn cuti draft/pending/approved</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Tidak boleh (sudah dipakai di-disabled)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tanggal cuti ditolak</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Boleh dipakai lagi untuk pengajuan baru</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Formulir PDF</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Otomatis dibuat saat langkah 1; kolom tanda tangan dikosongkan</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">File upload (langkah 2)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Wajib, JPG/JPEG/PNG/PDF, maks. 4 MB, berisi surat yang sudah ditandatangani</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Keterangan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Opsional, maks. 500 karakter</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Edit data oleh admin</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Bisa (durasi/tanggal/keterangan) — formulir PDF ikut diregenerasi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 7. Efek ke Laporan --}}
                <section id="efek" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                        Efek ke Presensi &amp; Laporan
                    </h2>

                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="badge-check" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Hanya cuti yang disetujui</b> yang muncul di laporan &amp; dashboard.
                                Draft, menunggu persetujuan, dan ditolak <b>tidak dihitung</b>.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="fingerprint" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Tidak memblokir absen.</b> Sistem tetap memperbolehkan absen masuk/pulang
                                di tanggal cuti — pengaturan kehadiran ada di kebijakan perusahaan.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="clipboard-list" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Laporan harian:</b> tanggal cuti yang disetujui ditandai
                                <b>"Cuti"</b> (setelah cek presensi / izin / WFH approved). Laporan per karyawan
                                menampilkan ringkasan <b>Hari Cuti</b> pada periode tersebut.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="table-2" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Rekap per unit:</b> tiap tanggal cuti disetujui dihitung sebagai <b>Total Cuti</b>.
                                Jika ada presensi pada tanggal cuti, hari itu <b>tidak dihitung hadir</b> di rekap unit.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="layout-dashboard" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Dashboard admin:</b> kartu "Cuti Hari Ini", chart penggunaan cuti,
                                serta feed 5 cuti terbaru — semua dari data yang sudah <b>disetujui</b>.
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 8. Boleh & Tidak --}}
                <section id="larangan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">8</span>
                        Yang Boleh &amp; Tidak Boleh
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <div class="text-[12px] font-bold text-emerald-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-check" style="width:14px;height:14px;"></i> Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-emerald-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan cuti 1–3 hari selama sisa kuota mencukupi.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Pilih tanggal tidak berurutan / tanggal lampau.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Edit &amp; hapus pengajuan selama masih <b>draft</b>.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Upload ulang file jika pengajuan <b>ditolak</b> (data tidak diulang).</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Download formulir PDF kapan pun selama ada di riwayat.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Hubungi HR jika kuota habis / perlu penyesuaian jatah.</span></li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <div class="text-[12px] font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-x" style="width:14px;height:14px;"></i> Tidak Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-rose-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✗</span><span>Durasi &gt; sisa kuota atau &gt; 3 hari per pengajuan.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Jumlah tanggal ≠ durasi yang dipilih.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Tanggal sama dalam form atau sama dgn cuti draft/pending/approved.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Upload file tanpa tanda tangan / format selain JPG/JPEG/PNG/PDF / &gt; 4 MB.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Mengubah pengajuan yang sudah <b>Menunggu/Disetujui/Ditolak</b> (hubungi HR).</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Keterangan &gt; 500 karakter.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 9. FAQ --}}
                <section id="faq" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">9</span>
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                    <div class="divide-y divide-[#f0ece8]">
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apakah cuti perlu disetujui atasan dulu?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Tidak. Berbeda izin/lembur/WFH yang berlapis atasan → HR, cuti tahunan
                                <b>langsung disetujui oleh superadmin</b> di panel Data Cuti.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kapan kuota cuti saya berkurang?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Saat superadmin <b>menyetujui</b>. Draft dan pengajuan yang menunggu persetujuan
                                belum mengurangi kuota. Saat approve, kuota dicek ulang — jika melebihi sisa,
                                approve ditolak sistem.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Berapa sisa cuti saya dan kenapa form terkunci?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Sisa = jatah − total hari yang sudah <b>disetujui</b>.
                                Form terkunci saat sisa = 0. Kartu kuota di halaman <b>/cuti</b> menampilkan
                                angkanya. Untuk penyesuaian jatah, hubungi HR/admin.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa maksimal 3 hari sekali pengajuan?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Batas sistem adalah <b>3 hari per pengajuan</b>. Jika butuh cuti lebih lama,
                                buat beberapa pengajuan terpisah (selama total tidak melebihi sisa kuota).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Pengajuan saya ditolak, harus ulang dari awal?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Tidak. Alasan penolakan tampil di riwayat. Kamu cukup menekan
                                <b>Upload Ulang</b> dan mengunggah file surat baru yang sudah ditandatangani —
                                data langkah 1 tetap dipakai. Perubahan data (durasi/tanggal) bisa via
                                <b>edit draft</b> (jika masih draft) atau hubungi HR.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apakah cuti menghalangi absen pulang?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Tidak. Cuti tidak memblokir absen di sistem.
                                Beda dengan WFH (laporan wajib sebelum pulang) atau izin tertentu.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kuota reset setiap tahun?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Secara sistem, <b>tidak ada reset otomatis</b>. Sisa dihitung dari total
                                seluruh riwayat yang disetujui. Chart dashboard menampilkan pemakaian per tahun
                                berjalan, tetapi kartu sisa karyawan memakai total sepanjang masa.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Formulir PDF-nya seperti apa?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Formulir Surat Cuti Tahunan berisi data pengaju, tanggal &amp; durasi cuti,
                                dan keterangan, dengan <b>3 kolom tanda tangan yang dikosongkan</b>
                                (Diajukan oleh / Mengetahui / Menyetujui). Cetak, tanda tangani manual,
                                lalu foto/scan untuk diupload di langkah 2.
                            </p>
                        </details>
                    </div>
                </section>

                <p class="text-[11px] text-[#a8a29e] text-center pt-2 pb-4">
                    Panduan ini mengikuti aturan aplikasi saat ini. Ada pertanyaan lain? Hubungi HR.
                </p>

            </main>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();

            var links = Array.prototype.slice.call(document.querySelectorAll('.toc-link'));
            if (!links.length || !('IntersectionObserver' in window)) return;

            var map = {};
            links.forEach(function(l) { map[l.dataset.target] = l; });

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        links.forEach(function(l) {
                            l.classList.remove('bg-white', 'text-coklat', 'font-semibold');
                        });
                        var active = map[entry.target.id];
                        if (active) active.classList.add('bg-white', 'text-coklat', 'font-semibold');
                    }
                });
            }, { rootMargin: '-20% 0px -65% 0px', threshold: 0 });

            links.forEach(function(l) {
                var el = document.getElementById(l.dataset.target);
                if (el) observer.observe(el);
            });
        });
    </script>
@endpush
