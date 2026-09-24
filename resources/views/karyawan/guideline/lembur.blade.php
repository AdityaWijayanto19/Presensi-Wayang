@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/guideline" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Panduan Lembur</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $sections = [
            ['id' => 'apa', 'label' => 'Apa itu Lembur', 'icon' => 'circle-help'],
            ['id' => 'alur', 'label' => 'Alur Pengajuan', 'icon' => 'git-branch'],
            ['id' => 'cara', 'label' => 'Cara Ajukan', 'icon' => 'send'],
            ['id' => 'jam', 'label' => 'Aturan Jam', 'icon' => 'clock'],
            ['id' => 'durasi', 'label' => 'Durasi & Prorate', 'icon' => 'hourglass'],
            ['id' => 'foto', 'label' => 'Foto Lembur', 'icon' => 'camera'],
            ['id' => 'laporan', 'label' => 'Laporan', 'icon' => 'clipboard-check'],
            ['id' => 'status', 'label' => 'Status & Artinya', 'icon' => 'tags'],
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
                        <div class="w-11 h-11 rounded-xl bg-violet-50 border border-violet-200 flex items-center justify-center text-violet-600 shrink-0">
                            <i data-lucide="timer" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-[16px] font-bold text-[#1c1917] leading-tight">Panduan Lembur</h1>
                            <p class="text-[13px] text-[#57534e] mt-1.5 leading-relaxed">
                                Panduan lengkap lembur dari awal sampai selesai: cara mengajukan, aturan jam & durasi,
                                persetujuan atasan & HR, foto mulai/selesai, sampai laporan hasil kerja.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- 1. Apa itu Lembur --}}
                <section id="apa" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                        Apa itu Lembur?
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed">
                        Lembur adalah <b>surat izin kerja lembur</b> untuk tanggal tertentu — misal harus lembur
                        malam atau menyelesaikan pekerjaan mendesak. Sebelum dianggap sah, pengajuanmu harus
                        <b>disetujui atasan</b> lalu <b>HR</b>. Setelah disetujui, kamu wajib
                        <b>foto mulai & selesai</b> lalu <b>mengirim laporan</b> hasil kerja.
                    </p>

                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="info" class="text-violet-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-violet-900">
                            Kalau kamu <b>Direktur</b> atau tidak punya atasan, pengajuanmu <b>langsung ke HR</b>
                            (tanpa tahap persetujuan atasan). Satu tanggal hanya boleh <b>satu</b> lembur.
                            Lembur hanya bisa diajukan untuk <b>hari ini</b>.
                        </p>
                    </div>
                </section>

                {{-- 2. Alur --}}
                <section id="alur" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                        Alur Pengajuan & Laporan
                    </h2>

                    {{-- Alur pengajuan --}}
                    <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase mb-2">Pengajuan Surat Lembur</div>
                    <ol class="space-y-0">
                        @php
                            $alur = [
                                ['t' => 'Ajukan Lembur', 'd' => 'Pilih durasi, jam mulai, dan keterangan di menu Pengajuan → Lembur.', 'c' => 'bg-violet-50 border-violet-200 text-violet-700'],
                                ['t' => 'Menunggu Atasan', 'd' => 'Atasan menyetujui atau menolak (alasan wajib diisi saat menolak).', 'c' => 'bg-amber-50 border-amber-200 text-amber-700'],
                                ['t' => 'Menunggu HR', 'd' => 'Setelah atasan setujui, HR memeriksa dan memutuskan akhir.', 'c' => 'bg-sky-50 border-sky-200 text-sky-700'],
                                ['t' => 'Disetujui / Ditolak', 'd' => 'Disetujui → lanjut foto & laporan. Ditolak → boleh ajukan ulang tanggal lain.', 'c' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
                            ];
                        @endphp
                        @foreach ($alur as $i => $a)
                            <li class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full border-2 {{ $a['c'] }} flex items-center justify-center text-[11px] font-bold shrink-0">{{ $i + 1 }}</div>
                                    @if (!$loop->last)
                                        <div class="w-px flex-1 min-h-[14px] bg-[#e7e5e4] my-1"></div>
                                    @endif
                                </div>
                                <div class="pb-3 min-w-0">
                                    <div class="text-[13px] font-bold text-[#1c1917]">{{ $a['t'] }}</div>
                                    <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">{{ $a['d'] }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    {{-- Alur foto + laporan --}}
                    <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase mt-1 mb-2">Setelah Disetujui: Foto & Laporan</div>
                    <ol class="space-y-0">
                        @php
                            $alur2 = [
                                ['t' => 'Ambil Foto Mulai', 'd' => 'Saat mulai bekerja lembur (webcam, sekali saja).'],
                                ['t' => 'Ambil Foto Selesai', 'd' => 'Minimal 60 menit setelah foto mulai. Durasi aktual dihitung otomatis.'],
                                ['t' => 'Kirim Laporan', 'd' => 'Deskripsi hasil kerja (10–3000 karakter) + 2–5 foto. Wajib sebelum boleh absen pulang.'],
                                ['t' => 'Disetujui Atasan → HR', 'd' => 'Laporan juga disetujui 2 tahap. Jika ditolak, perbaiki lalu Edit Laporan.'],
                            ];
                        @endphp
                        @foreach ($alur2 as $i => $a)
                            <li class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full border-2 border-emerald-200 text-emerald-700 bg-emerald-50 flex items-center justify-center text-[11px] font-bold shrink-0">{{ $i + 1 }}</div>
                                    @if (!$loop->last)
                                        <div class="w-px flex-1 min-h-[14px] bg-[#e7e5e4] my-1"></div>
                                    @endif
                                </div>
                                <div class="pb-3 min-w-0">
                                    <div class="text-[13px] font-bold text-[#1c1917]">{{ $a['t'] }}</div>
                                    <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">{{ $a['d'] }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mt-1 flex gap-2.5">
                        <i data-lucide="circle-alert" class="text-rose-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-rose-800">
                            Selama laporan lembur belum dikirim (untuk lembur yang disetujui hari itu),
                            kamu <b>belum bisa absen pulang</b>.
                        </p>
                    </div>
                </section>

                {{-- 3. Cara ajukan --}}
                <section id="cara" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                        Cara Ajukan Lembur
                    </h2>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka menu <b>Pengajuan → Lembur</b>, lalu tombol <b>+</b> (Ajukan).</li>
                        <li>Tanggal lembur otomatis <b>hari ini</b> (tidak bisa diganti).</li>
                        <li>Pilih <b>Durasi</b> (1–5 jam) atau <b>Prorate</b> (durasi aktual).</li>
                        <li>Isi <b>Rencana Jam Mulai</b> — menit hanya <b>:00</b> atau <b>:30</b>.</li>
                        <li>Jam selesai dihitung otomatis (Prorate → "Menyesuaikan").</li>
                        <li>Isi <b>Keterangan</b> (min. 5 karakter) — alasan lembur yang jelas.</li>
                        <li>Tekan <b>Kirim Pengajuan Lembur</b> dan konfirmasi. Surat PDF otomatis dibuat dan dikirim ke atasanmu.</li>
                    </ol>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="alarm-clock" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <div class="text-[12px] leading-relaxed text-amber-900">
                            <b>Penting soal waktu:</b> pengajuan lembur hanya bisa dilakukan pada pukul
                            <b>00.01–16.50</b> atau <b>18.00–23.59</b>. Di antaranya (16.51–17.59) sistem menutup
                            sementara. Satu hari hanya boleh satu pengajuan lembur.
                        </div>
                    </div>
                </section>

                {{-- 4. Aturan jam --}}
                <section id="jam" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
                        Aturan Jam & Ketentuan
                    </h2>

                    <div class="overflow-x-auto -mx-1 px-1">
                        <table class="w-full text-left border-collapse min-w-[320px]">
                            <thead>
                                <tr class="border-b border-[#e7e5e4]">
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2 pr-3">Aturan</th>
                                    <th class="text-[11px] font-semibold text-[#a8a29e] uppercase tracking-wide py-2">Waktu / Syarat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece8]">
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Window ajukan (pagi)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">00.01 – 16.50</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Window ajukan (malam)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">18.00 – 23.59</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Gap (tidak bisa ajukan)</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">16.51 – 17.59</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tanggal lembur</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Hari ini saja</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Satu lembur per tanggal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Tidak boleh dobel</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Rencana jam mulai</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Interval :00 atau :30</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Keterangan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">5 – 1000 karakter</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Foto selesai (setelah mulai)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Minimal 60 menit</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Absen pulang normal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Minimal 8 jam kerja</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Absen pulang + laporan lembur</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Laporan wajib sudah dikirim</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Reminder laporan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Pukul 22.00 (7 hari terakhir)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="circle-alert" class="text-rose-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-rose-800">
                            Lembur <b>hanya bisa diajukan untuk hari ini</b>. Lewat window jam (16.51–17.59),
                            coba lagi setelah pukul 18.00.
                        </p>
                    </div>
                </section>

                {{-- 5. Durasi & Prorate --}}
                <section id="durasi" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
                        Durasi & Prorate
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <div class="w-8 h-8 rounded-lg border bg-violet-50 border-violet-200 text-violet-600 flex items-center justify-center shrink-0">
                                <i data-lucide="hourglass" style="width:15px;height:15px;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-[#1c1917]">Durasi Tetap</div>
                                <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">
                                    Pilih 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, atau 5 jam.
                                    Jam selesai dihitung otomatis dari jam mulai + durasi.
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <div class="w-8 h-8 rounded-lg border bg-emerald-50 border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                                <i data-lucide="infinity" style="width:15px;height:15px;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-[#1c1917]">Prorate</div>
                                <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">
                                    Durasi tidak tetap / menyesuaikan. Jam selesai tampil "Menyesuaikan".
                                    Durasi <b>aktual</b> dihitung dari foto mulai → foto selesai
                                    (dibulatkan ke 30 menit terdekat, min. 0.5 jam).
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="calculator" class="text-violet-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-violet-900">
                            Durasi tetap dipakai sebagai <b>rencana</b> di surat pengajuan.
                            Durasi yang dihitung untuk keperluan teknis (laporan, monitoring) memakai
                            <b>durasi aktual</b> hasil pembulatan saat foto selesai — terutama untuk Prorate.
                        </p>
                    </div>
                </section>

                {{-- 6. Foto Lembur --}}
                <section id="foto" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
                        Foto Mulai & Selesai
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed mb-3">
                        Setelah lembur disetujui, buka kartu lembur → <b>Ambil Foto</b>.
                        Foto diambil dari kamera belakang (bisa diganti depan), rasio 4:5, dan hanya boleh sekali masing-masing.
                    </p>

                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="camera" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Foto Mulai</b> — diambil saat mulai lembur. Boleh kapan saja setelah disetujui
                                (termasuk lembur pre-shift sebelum pukul 07.00).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="clock" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Foto Selesai</b> — wajib <b>minimal 60 menit</b> setelah foto mulai.
                                Sisa waktu ditampilkan di pesan error jika terlalu cepat.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="hourglass" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                Saat foto selesai tersimpan, <b>durasi aktual</b> dihitung dan dibulatkan ke
                                kelipatan 30 menit (min. 0.5 jam).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                            <i data-lucide="check-circle" class="text-emerald-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-emerald-800 leading-relaxed">
                                Setelah kedua foto lengkap, tombol <b>Isi Laporan</b> akan muncul.
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 7. Laporan --}}
                <section id="laporan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                        Laporan Hasil Kerja
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed mb-3">
                        Laporan wajib dikirim setelah foto mulai & selesai lengkap. Laporan juga disetujui
                        <b>atasan → HR</b>.
                    </p>

                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="list-checks" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Isi laporan:</b> deskripsi hasil kerja (10–3000 karakter, disarankan bernomor maks. 10 poin)
                                + <b>2 sampai 5 foto</b> hasil pekerjaan (JPG/JPEG/PNG, maks. 4 MB per foto).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="file-check" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                PDF laporan otomatis dibuat dan bisa diunduh dari kartu riwayat lembur.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="pencil" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Laporan ditolak?</b> Buka menu Lembur → tombol <b>Edit Laporan</b>,
                                perbaiki sesuai masukan, lalu kirim ulang (disetujui ulang atasan → HR).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl p-3">
                            <i data-lucide="bell" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-amber-900 leading-relaxed">
                                <b>Pengingat otomatis pukul 22.00</b> jika lembur (7 hari terakhir) belum upload laporan.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-rose-50 border border-rose-200 rounded-xl p-3">
                            <i data-lucide="circle-alert" class="text-rose-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-rose-800 leading-relaxed">
                                <b>Belum bisa absen pulang</b> selama laporan lembur hari itu belum dikirim
                                (untuk lembur yang disetujui).
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 8. Status --}}
                <section id="status" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">8</span>
                        Status & Artinya
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="border border-amber-200 bg-amber-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5">Menunggu Persetujuan</span>
                            <p class="text-[12px] text-amber-900 mt-1.5 leading-relaxed">Pengajuanmu sedang diperiksa atasan langsung.</p>
                        </div>
                        <div class="border border-sky-200 bg-sky-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-sky-100 text-sky-700 border border-sky-200 rounded-full px-2 py-0.5">Menunggu Persetujuan HR</span>
                            <p class="text-[12px] text-sky-900 mt-1.5 leading-relaxed">Atasan sudah setujui, tinggal menunggu keputusan HR.</p>
                        </div>
                        <div class="border border-emerald-200 bg-emerald-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5">Disetujui</span>
                            <p class="text-[12px] text-emerald-900 mt-1.5 leading-relaxed">Lembur sah. Ambil foto mulai & selesai, lalu kirim laporan.</p>
                        </div>
                        <div class="border border-rose-200 bg-rose-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-rose-100 text-rose-700 border border-rose-200 rounded-full px-2 py-0.5">Ditolak</span>
                            <p class="text-[12px] text-rose-900 mt-1.5 leading-relaxed">Tidak disetujui. Ada alasan penolakan; boleh ajukan ulang untuk tanggal lain.</p>
                        </div>
                        <div class="border border-stone-200 bg-stone-50 rounded-xl p-3 sm:col-span-2">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-stone-100 text-stone-600 border border-stone-200 rounded-full px-2 py-0.5">Laporan</span>
                            <p class="text-[12px] text-stone-700 mt-1.5 leading-relaxed">
                                Laporan punya status sendiri: <b>Menunggu Atasan → Menunggu HR → Disetujui / Ditolak</b>.
                                Jika ditolak, gunakan tombol <b>Edit Laporan</b>.
                            </p>
                        </div>
                    </div>

                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="layout-list" class="text-stone-500 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-stone-600">
                            Menu <b>Lembur</b> menampilkan semua status. Yang masih menunggu persetujuan juga
                            terlihat di <b>dashboard</b>. Di dashboard, lembur disetujui tapi belum ada laporan
                            tampil sebagai <b>"Menunggu Laporan"</b>.
                        </p>
                    </div>
                </section>

                {{-- 9. Boleh & Tidak --}}
                <section id="larangan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">9</span>
                        Yang Boleh & Tidak Boleh
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <div class="text-[12px] font-bold text-emerald-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-check" style="width:14px;height:14px;"></i> Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-emerald-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan lembur hari ini di window <b>00.01–16.50</b> atau <b>18.00–23.59</b>.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Pilih Prorate jika durasi lembur tidak pasti.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Ambil foto mulai setelah lembur disetujui.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Ambil foto selesai setelah ≥ 60 menit dari foto mulai.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Edit & kirim ulang laporan setelah <b>Ditolak</b>.</span></li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <div class="text-[12px] font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-x" style="width:14px;height:14px;"></i> Tidak Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-rose-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✗</span><span>Dua lembur untuk tanggal yang sama.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Ajukan di gap 16.51–17.59 atau jam 00.00.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Jam mulai selain :00 atau :30.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Ambil foto mulai/selesai lebih dari sekali.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Foto selesai sebelum 60 menit dari foto mulai.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Kirim laporan sebelum foto lengkap, atau kurang dari 2 foto.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Edit laporan yang belum berstatus Ditolak.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Absen pulang sebelum laporan lembur terkirim.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 10. FAQ --}}
                <section id="faq" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 border border-violet-200 text-violet-600 flex items-center justify-center text-[11px] font-bold shrink-0">10</span>
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                    <div class="divide-y divide-[#f0ece8]">
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kapan saya boleh mengajukan lembur?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Pada pukul <b>00.01–16.50</b> atau <b>18.00–23.59</b>, untuk <b>hari yang sama</b>.
                                Di antaranya (16.51–17.59) sistem menutup pengajuan sementara.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apa bedanya durasi biasa dengan Prorate?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Durasi biasa (1–5 jam) adalah rencana tetap di surat pengajuan.
                                <b>Prorate</b> dipakai jika durasi aktual tidak pasti — jam selesai "Menyesuaikan",
                                dan durasi dihitung dari foto mulai → foto selesai (dibulatkan 30 menit, min. 0.5 jam).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa belum bisa ambil foto selesai?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Foto selesai baru boleh diambil setelah <b>minimal 60 menit</b> sejak foto mulai.
                                Pesan error menampilkan sisa waktu yang harus ditunggu.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa saya belum bisa absen pulang?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Jika ada lembur yang disetujui <b>hari ini</b> dan laporannya belum dikirim,
                                sistem memblokir presensi pulang. Kirim laporan dulu (deskripsi + 2–5 foto).
                                Aturan minimal 8 jam kerja juga tetap berlaku.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Laporan saya ditolak, bagaimana?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Buka menu <b>Lembur</b>, tap <b>Edit Laporan</b>, perbaiki sesuai masukan
                                atasan/HR, lalu kirim ulang. Perbaikan diperiksa ulang dari awal (atasan → HR).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Bisa hapus pengajuan lembur?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Secara sistem, lembur hanya bisa dihapus selama masih <b>Menunggu Persetujuan</b>
                                (sebelum masuk HR). Di aplikasi saat ini tidak ada tombol hapus di daftar —
                                jika perlu batal, hubungi atasan/HR.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Di mana saya melihat lembur yang masih menunggu?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Di <b>dashboard</b> (seksi persetujuan & "Lembur Saya") dan di menu <b>Lembur</b>
                                (riwayat lengkap). Di dashboard, lembur disetujui tapi laporan belum ada tampil
                                sebagai <b>"Menunggu Laporan"</b>.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa jam mulai harus :00 atau :30?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Agar rencana lembur rapi dan mudah dicocokkan. Pilihan menit di form hanya
                                :00 dan :30 (step 30 menit).
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
