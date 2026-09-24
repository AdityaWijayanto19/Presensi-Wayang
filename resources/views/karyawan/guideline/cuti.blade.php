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
            ['id' => 'cara', 'label' => 'Cara Upload', 'icon' => 'upload'],
            ['id' => 'aturan', 'label' => 'Aturan Tanggal & File', 'icon' => 'file-check'],
            ['id' => 'efek', 'label' => 'Efek ke Laporan', 'icon' => 'clipboard-list'],
            ['id' => 'larangan', 'label' => 'Boleh & Tidak', 'icon' => 'shield-check'],
            ['id' => 'faq', 'label' => 'Tanya Jawab', 'icon' => 'messages-square'],
        ];
    @endphp

    {{-- Chip nav (mobile & tablet) --}}
    <div class="mt-[70px] sticky top-[56px] z-30 bg-[#e9ecef]/95 backdrop-blur border-b border-[#e7e5e4] lg:hidden">
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
                <nav class="sticky top-[86px] pt-1 pb-8">
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
                                Panduan lengkap cuti tahunan: kuota &amp; sisa, cara upload surat yang sudah
                                ditandatangani, aturan tanggal &amp; file, sampai efeknya ke laporan presensi.
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
                        Cuti adalah <b>upload dokumen cuti tahunan yang sudah ditandatangani di luar</b>.
                        Karena suratnya sudah sah di luar, aplikasi <b>tidak menjalankan approval</b>:
                        begitu kamu upload, kuota langsung berkurang dan cuti langsung tercatat.
                    </p>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="info" class="text-emerald-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-emerald-900">
                            Satu tanggal hanya boleh <b>satu kali</b> dipilih sebagai cuti.
                            Tanggal boleh <b>tidak berurutan</b> dan boleh <b>tanggal lampau</b>.
                            Jika sisa kuota 0, form upload diblokir.
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
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Approval atasan → HR</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ya (berlapis)</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Tidak ada</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Status (pending/…)</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ada</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Tidak ada — langsung final</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Kuota berkurang</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Ikut aturan fitur</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">Langsung saat upload</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Dokumen</td>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">PDF form otomatis</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-700 py-2.5">File ditandatangani diupload manual</td>
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
                                <div class="text-[13px] font-bold text-[#1c1917]">Maks. per upload</div>
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
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">max(0, jatah − total terpakai)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Durasi per upload</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">1, 2, atau 3 hari</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Jumlah tanggal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Wajib = durasi</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Sisa = 0</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Upload diblokir — hubungi HR</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Hapus cuti (admin)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Kuota otomatis kembali</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="calculator" class="text-violet-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-violet-900">
                            <b>Contoh:</b> jatah 12, sudah upload 2 + 3 hari → sisa 7.
                            Upload berikutnya maksimal <b>min(3, 7) = 3</b> hari.
                            Sisa dihitung dari <b>seluruh riwayat</b> (bukan reset otomatis tiap tahun).
                        </p>
                    </div>
                </section>

                {{-- 4. Cara Upload --}}
                <section id="cara" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
                        Cara Upload Cuti
                    </h2>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka menu <b>Pengajuan → Cuti Tahunan</b> (atau langsung <b>/cuti</b>).</li>
                        <li>Lihat kartu <b>Kuota</b> — pastikan sisa &gt; 0.</li>
                        <li>Tap tombol <b>+</b> / <b>Upload Cuti</b> → form upload muncul.</li>
                        <li>Pilih <b>Durasi Cuti</b> (1–3 hari, sesuai sisa).</li>
                        <li>Isi <b>Tanggal Cuti</b> sebanyak durasi (boleh tidak berurutan / lampau).</li>
                        <li>Isi <b>Keterangan</b> (opsional, maks. 500 karakter).</li>
                        <li>Upload <b>File Cuti (sudah ditandatangani)</b> — JPG/JPEG/PNG/PDF, maks. 4&nbsp;MB.</li>
                        <li>Tekan <b>Upload Cuti</b> → konfirmasi dialog ("kuota akan berkurang N hari").</li>
                    </ol>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="triangle-alert" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-amber-900">
                            Pastikan file sudah <b>ditandatangani</b> sebelum upload.
                            Setelah sukses, kuota <b>langsung berkurang</b> dan tidak ada tahap menunggu persetujuan.
                        </p>
                    </div>
                </section>

                {{-- 5. Aturan Tanggal & File --}}
                <section id="aturan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
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
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tanggal sama dgn cuti lama</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Tidak boleh (sudah dipakai di-disabled)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">File cuti</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Wajib, JPG/JPEG/PNG/PDF, maks. 4 MB</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Isi file</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Surat cuti yang sudah ditandatangani</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Keterangan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Opsional, maks. 500 karakter</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Edit file oleh admin</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Tidak bisa — hanya durasi/tanggal/keterangan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 6. Efek ke Laporan --}}
                <section id="efek" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
                        Efek ke Presensi &amp; Laporan
                    </h2>

                    <div class="space-y-2.5">
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
                                <b>Laporan harian:</b> tanggal yang ada di data cuti ditandai
                                <b>"Cuti"</b> (setelah cek presensi / izin / WFH approved).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="table-2" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Rekap per unit:</b> tiap tanggal cuti dihitung sebagai <b>Total Cuti</b>.
                                Jika ada presensi pada tanggal cuti, hari itu <b>tidak dihitung hadir</b> di rekap unit.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="layout-dashboard" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Dashboard admin:</b> kartu "Cuti Hari Ini", chart penggunaan cuti,
                                serta feed 5 cuti terbaru — semua dari data upload yang sudah final.
                            </div>
                        </div>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="check-circle" class="text-emerald-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-emerald-900">
                            Tidak ada status "Menunggu" untuk cuti. Setelah upload sukses, data langsung
                            muncul di riwayat karyawan dan daftar admin.
                        </p>
                    </div>
                </section>

                {{-- 7. Boleh & Tidak --}}
                <section id="larangan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                        Yang Boleh &amp; Tidak Boleh
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <div class="text-[12px] font-bold text-emerald-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-check" style="width:14px;height:14px;"></i> Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-emerald-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✓</span><span>Upload cuti 1–3 hari selama sisa kuota mencukupi.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Pilih tanggal tidak berurutan / tanggal lampau.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Isi keterangan atau dikosongkan saja (opsional).</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Lihat file cuti sendiri lewat tombol di riwayat.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Hubungi HR jika kuota habis / perlu penyesuaian jatah.</span></li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <div class="text-[12px] font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-x" style="width:14px;height:14px;"></i> Tidak Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-rose-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✗</span><span>Durasi &gt; sisa kuota atau &gt; 3 hari per upload.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Jumlah tanggal ≠ durasi yang dipilih.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Tanggal sama dalam form atau sama dgn cuti lama.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Upload tanpa file / format selain JPG/JPEG/PNG/PDF / &gt; 4 MB.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Keterangan &gt; 500 karakter.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Upload saat sisa kuota = 0.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 8. FAQ --}}
                <section id="faq" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-[11px] font-bold shrink-0">8</span>
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                    <div class="divide-y divide-[#f0ece8]">
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apakah cuti perlu disetujui atasan / HR dulu?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Tidak. Karena surat sudah ditandatangani di luar, upload = final.
                                Kuota langsung berkurang dan data langsung tercatat — berbeda izin/lembur/WFH
                                yang harus melewati atasan lalu HR.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Berapa sisa cuti saya dan kenapa form terkunci?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Sisa = jatah − total hari yang sudah diupload (seluruh riwayat).
                                Form terkunci saat sisa = 0. Kartu kuota di halaman <b>/cuti</b> menampilkan
                                angkanya. Untuk penyesuaian jatah, hubungi HR/admin.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa maksimal 3 hari sekali upload?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Batas sistem adalah <b>3 hari per upload</b>. Jika butuh cuti lebih lama,
                                buat beberapa upload terpisah (selama total tidak melebihi sisa kuota).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Tanggal sudah terlanjur dipilih, bisa diganti?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Karyawan tidak ada tombol edit/hapus cuti di aplikasi.
                                Perubahan data (durasi/tanggal/keterangan) dilakukan <b>admin</b> lewat
                                panel <b>Data Cuti</b> (permission edit). Hapus → kuota kembali.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apakah cuti menghalangi absen pulang?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Tidak. Cuti tidak memblokir absen di sistem.
                                Beda dengan lembur (laporan wajib sebelum pulang) atau izin tertentu.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kuota reset setiap tahun?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Secara sistem, <b>tidak ada reset otomatis</b>. Sisa dihitung dari total
                                seluruh riwayat. Chart dashboard menampilkan pemakaian per tahun berjalan,
                                tetapi kartu sisa karyawan memakai total sepanjang masa.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                File cuti apa yang diterima?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                JPG, JPEG, PNG, atau PDF, maksimal <b>4 MB</b>. Isinya surat cuti
                                yang <b>sudah ditandatangani</b>. File yang sama tidak bisa dipakai
                                untuk tanggal yang sudah terpakai cuti.
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
