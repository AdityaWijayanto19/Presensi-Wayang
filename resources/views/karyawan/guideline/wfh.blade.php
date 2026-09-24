@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/guideline" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Panduan WFH</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $sections = [
            ['id' => 'apa', 'label' => 'Apa itu WFH', 'icon' => 'circle-help'],
            ['id' => 'alur', 'label' => 'Alur Pengajuan', 'icon' => 'git-branch'],
            ['id' => 'cara', 'label' => 'Cara Ajukan', 'icon' => 'send'],
            ['id' => 'jam', 'label' => 'Aturan Jam', 'icon' => 'clock'],
            ['id' => 'laporan', 'label' => 'Hari-H & Laporan', 'icon' => 'clipboard-check'],
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
                        <div class="w-11 h-11 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600 shrink-0">
                            <i data-lucide="home" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-[16px] font-bold text-[#1c1917] leading-tight">Panduan Work From Home</h1>
                            <p class="text-[13px] text-[#57534e] mt-1.5 leading-relaxed">
                                Panduan lengkap WFH dari awal sampai selesai: cara mengajukan, kapan boleh mengajukan,
                                aturan jam kerja, persetujuan atasan & HR, sampai laporan dan status <b>Unpaid</b>.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- 1. Apa itu WFH --}}
                <section id="apa" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                        Apa itu WFH?
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed">
                        WFH (Work From Home) berarti kamu <b>bekerja dari rumah</b> pada tanggal tertentu, bukan dari kantor.
                        Sebelum hari-H, kamu harus <b>mengajukan izin WFH</b> lebih dulu dan mendapat persetujuan
                        <b>atasan</b> lalu <b>HR</b>. Di hari-H, kamu tetap <b>absen masuk & pulang</b> seperti biasa
                        (foto + lokasi dari rumah), dan wajib <b>mengirim laporan</b> hasil kerja.
                    </p>

                    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="info" class="text-sky-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-sky-900">
                            Kalau kamu <b>Direktur</b> atau tidak punya atasan, pengajuanmu <b>langsung ke HR</b>
                            (tanpa tahap persetujuan atasan).
                        </p>
                    </div>
                </section>

                {{-- 2. Alur --}}
                <section id="alur" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                        Alur Pengajuan & Laporan
                    </h2>

                    {{-- Alur pengajuan --}}
                    <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase mb-2">Pengajuan Surat WFH</div>
                    <ol class="space-y-0">
                        @php
                            $alur = [
                                ['t' => 'Ajukan WFH', 'd' => 'Isi tanggal, alasan, dan rencana pekerjaan di menu Pengajuan → WFH.', 'c' => 'bg-sky-50 border-sky-200 text-sky-700'],
                                ['t' => 'Menunggu Atasan', 'd' => 'Atasan langsung menyetujui atau menolak (alasan wajib diisi saat menolak).', 'c' => 'bg-amber-50 border-amber-200 text-amber-700'],
                                ['t' => 'Menunggu HR', 'd' => 'Setelah atasan setujui, HR memeriksa dan memutuskan akhir.', 'c' => 'bg-amber-50 border-amber-200 text-amber-700'],
                                ['t' => 'Disetujui / Ditolak', 'd' => 'Jika disetujui → siap dipakai di hari-H. Jika ditolak → bisa ajukan ulang untuk tanggal lain.', 'c' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
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

                    {{-- Alur laporan --}}
                    <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase mt-1 mb-2">Laporan di Hari-H</div>
                    <ol class="space-y-0">
                        @php
                            $alur2 = [
                                ['t' => 'Absen masuk', 'd' => 'Buka mulai pukul 07.00, foto + lokasi dari rumah.'],
                                ['t' => 'Kerja minimal 7 jam', 'd' => 'Laporan baru boleh dikirim setelah 7 jam sejak absen masuk.'],
                                ['t' => 'Kirim laporan', 'd' => 'Deskripsi hasil kerja + 2–5 foto. Laporan wajib ada sebelum boleh absen pulang.'],
                                ['t' => 'Disetujui Atasan → HR', 'd' => 'Jika ditolak, perbaiki lalu kirim ulang.'],
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
                </section>

                {{-- 3. Cara ajukan --}}
                <section id="cara" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                        Cara Ajukan WFH
                    </h2>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka menu <b>Pengajuan → Work From Home</b>, lalu tombol <b>+</b> (Ajukan).</li>
                        <li>Pilih <b>Tanggal WFH</b> (hari ini atau mendatang).</li>
                        <li>Isi <b>Keterangan / Alasan</b> (min. 5 karakter), misal: kondisi kesehatan, jarak jauh, dll.</li>
                        <li>Isi <b>Deskripsi Pekerjaan</b> (min. 10 karakter) — tulis rencana pekerjaan, disarankan bernomor, maksimal 10 poin.</li>
                        <li>Tekan <b>Ajukan WFH</b> dan konfirmasi. Surat PDF otomatis dibuat dan dikirim ke atasanmu.</li>
                    </ol>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="alarm-clock" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <div class="text-[12px] leading-relaxed text-amber-900">
                            <b>Penting soal waktu:</b> pengajuan untuk <b>hari ini</b> hanya bisa dilakukan
                            <b>sebelum jam masuk</b> kantor (sesuai jam masuk unitmu, umumnya pukul 08.00).
                            Lewat dari itu, pilih tanggal besok / H+1.
                        </div>
                    </div>
                </section>

                {{-- 4. Aturan jam --}}
                <section id="jam" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
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
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Absen masuk dibuka</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Mulai 07.00</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Tutup pengajuan untuk hari ini</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Sebelum jam masuk (±08.00)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Boleh absen pulang</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Setelah bekerja 8 jam + laporan terkirim</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Boleh kirim laporan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Setelah 7 jam sejak absen masuk</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Hari kirim laporan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Hari yang sama dengan tanggal WFH</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Pengingat (reminder)</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Pukul 22.00, jika laporan belum ada</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Otomatis jadi Unpaid</td>
                                    <td class="text-[12.5px] font-semibold text-rose-600 py-2.5">Pukul 00.00, jika laporan tidak beres</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 5. Hari-H & Laporan --}}
                <section id="laporan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
                        Hari-H & Laporan Kerja
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed mb-3">
                        Di hari WFH, kamu tetap absen seperti di kantor — hanya tempatnya dari rumah.
                        Setelah bekerja cukup lama, kirim laporan supaya hari kerjamu dihitung sempurna.
                    </p>

                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="list-checks" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Isi laporan:</b> deskripsi hasil kerja (10–3000 karakter, maks. 10 poin bernomor)
                                + <b>2 sampai 5 foto</b> hasil pekerjaan (JPG/PNG, maks. 4 MB per foto).
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="map-pin" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Lokasi</b> laporan otomatis diambil dari lokasi absen masukmu hari itu.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                            <i data-lucide="pencil" class="text-coklat shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-[#57534e] leading-relaxed">
                                <b>Laporan ditolak?</b> Buka menu WFH → tombol <b>Edit Laporan</b>, perbaiki, lalu kirim ulang.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 bg-rose-50 border border-rose-200 rounded-xl p-3">
                            <i data-lucide="circle-alert" class="text-rose-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                            <div class="text-[12.5px] text-rose-800 leading-relaxed">
                                <b>Belum bisa absen pulang</b> selama laporan WFH hari itu belum dikirim.
                                Kalau sampai lewat hari tanpa laporan (atau laporan ditolak tak diperbaiki),
                                statusnya jadi <b>Unpaid</b> (tidak dibayar).
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 6. Status --}}
                <section id="status" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
                        Status & Artinya
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="border border-amber-200 bg-amber-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5">Menunggu Atasan</span>
                            <p class="text-[12px] text-amber-900 mt-1.5 leading-relaxed">Pengajuanmu sedang diperiksa atasan langsung.</p>
                        </div>
                        <div class="border border-sky-200 bg-sky-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-sky-100 text-sky-700 border border-sky-200 rounded-full px-2 py-0.5">Menunggu HR</span>
                            <p class="text-[12px] text-sky-900 mt-1.5 leading-relaxed">Atasan sudah setujui, tinggal menunggu keputusan HR.</p>
                        </div>
                        <div class="border border-emerald-200 bg-emerald-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5">Disetujui</span>
                            <p class="text-[12px] text-emerald-900 mt-1.5 leading-relaxed">WFH sah. Hadir virtual di hari-H dan jangan lupa laporan.</p>
                        </div>
                        <div class="border border-rose-200 bg-rose-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-rose-100 text-rose-700 border border-rose-200 rounded-full px-2 py-0.5">Ditolak</span>
                            <p class="text-[12px] text-rose-900 mt-1.5 leading-relaxed">Tidak disetujui. Ada alasan penolakan; boleh ajukan ulang untuk tanggal lain.</p>
                        </div>
                        <div class="border border-stone-200 bg-stone-50 rounded-xl p-3 sm:col-span-2">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-stone-100 text-stone-600 border border-stone-200 rounded-full px-2 py-0.5">Unpaid</span>
                            <p class="text-[12px] text-stone-700 mt-1.5 leading-relaxed">
                                WFH disetujui dulu, tapi syaratnya tidak terpenuhi (laporan tidak dikirim / ditolak / belum absen pulang) —
                                jadi <b>tidak dibayar</b>. Hubungi HR jika merasa ini keliru.
                            </p>
                        </div>
                    </div>

                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="layout-list" class="text-stone-500 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-stone-600">
                            Daftar di menu <b>WFH</b> hanya menampilkan data yang sudah <b>final</b>
                            (disetujui / ditolak / unpaid). Pengajuan yang masih menunggu persetujuan terlihat di <b>dashboard</b>.
                        </p>
                    </div>
                </section>

                {{-- 7. Boleh & Tidak --}}
                <section id="larangan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                        Yang Boleh & Tidak Boleh
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <div class="text-[12px] font-bold text-emerald-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-check" style="width:14px;height:14px;"></i> Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-emerald-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan WFH hari ini <b>sebelum</b> jam masuk.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Hapus pengajuan selama masih <b>Menunggu Atasan</b>.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Perbaiki & kirim ulang laporan setelah <b>ditolak</b>.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan ulang untuk tanggal lain setelah ditolak.</span></li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <div class="text-[12px] font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-x" style="width:14px;height:14px;"></i> Tidak Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-rose-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✗</span><span>Dua WFH untuk tanggal yang sama.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Ajukan untuk tanggal lampau.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Hapus pengajuan yang sudah masuk HR / disetujui.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Absen pulang sebelum laporan terkirim.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Kirim laporan kurang dari 2 foto atau lebih dari 5 foto.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 8. FAQ --}}
                <section id="faq" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center text-[11px] font-bold shrink-0">8</span>
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                    <div class="divide-y divide-[#f0ece8]">
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kapan saya boleh mengirim laporan WFH?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Setelah <b>7 jam</b> sejak absen masuk, pada <b>hari yang sama</b> dengan tanggal WFH —
                                dan sebelum kamu absen pulang.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa WFH saya jadi Unpaid?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Biasanya karena laporan tidak dikirim, laporan ditolak tapi tidak diperbaiki,
                                atau lupa absen pulang. Sistem menandainya otomatis pukul 00.00.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Bisa hapus pengajuan yang sudah dikirim?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Bisa, <b>selama masih Menunggu Atasan</b>. Setelah disetujui atasan atau masuk ke HR,
                                pengajuan tidak bisa dihapus sendiri — hubungi HR.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apakah hari ini masih bisa diajukan WFH-nya?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Hanya <b>sebelum jam masuk</b> (umumnya pukul 08.00). Setelah itu, pilih tanggal besok.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Laporan saya ditolak, bagaimana?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Buka menu <b>WFH</b>, tap <b>Edit Laporan</b>, perbaiki sesuai masukan atasan/HR,
                                lalu kirim ulang. Perbaikan diperiksa ulang dari awal (atasan → HR).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Di mana saya melihat pengajuan yang masih menunggu?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Di <b>dashboard</b>. Menu WFH hanya menampilkan data yang sudah final
                                (disetujui, ditolak, atau unpaid).
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

            // Scroll-spy untuk TOC desktop
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
