@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/guideline" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Panduan Izin</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $sections = [
            ['id' => 'apa', 'label' => 'Apa itu Izin', 'icon' => 'circle-help'],
            ['id' => 'alur', 'label' => 'Alur Pengajuan', 'icon' => 'git-branch'],
            ['id' => 'jenis', 'label' => '5 Jenis Izin', 'icon' => 'list'],
            ['id' => 'cara', 'label' => 'Cara Ajukan', 'icon' => 'send'],
            ['id' => 'jam', 'label' => 'Aturan Jam', 'icon' => 'clock'],
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
                        <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shrink-0">
                            <i data-lucide="file-text" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-[16px] font-bold text-[#1c1917] leading-tight">Panduan Izin & Sakit</h1>
                            <p class="text-[13px] text-[#57534e] mt-1.5 leading-relaxed">
                                Panduan lengkap izin dari awal sampai selesai: 5 jenis izin, cara mengajukan,
                                aturan jam & batas waktu, persetujuan atasan & HR, sampai status yang tampil di aplikasi.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- 1. Apa itu Izin --}}
                <section id="apa" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                        Apa itu Izin?
                    </h2>
                    <p class="text-[13px] text-[#57534e] leading-relaxed">
                        Izin adalah <b>surat izin / sakit</b> untuk tanggal tertentu — misal tidak masuk, terlambat,
                        setengah hari, pulang cepat, atau sakit. Sebelum dianggap sah, pengajuanmu harus
                        <b>disetujui atasan</b> lalu <b>HR</b>. Setelah disetujui, barulah berlaku (misalnya
                        untuk absen pulang lebih awal).
                    </p>
                </section>

                {{-- 2. Alur --}}
                <section id="alur" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                        Alur Pengajuan & Persetujuan
                    </h2>

                    <ol class="space-y-0">
                        @php
                            $alur = [
                                ['t' => 'Ajukan Izin', 'd' => 'Pilih jenis, tanggal, keterangan, dan unggah bukti (foto/PDF) di menu Pengajuan → Izin.', 'c' => 'bg-amber-50 border-amber-200 text-amber-700'],
                                ['t' => 'Menunggu Atasan', 'd' => 'Atasan menyetujui atau menolak (alasan wajib diisi saat menolak).', 'c' => 'bg-amber-50 border-amber-200 text-amber-700'],
                                ['t' => 'Menunggu HR', 'd' => 'Setelah atasan setujui, HR memeriksa dan memutuskan akhir.', 'c' => 'bg-sky-50 border-sky-200 text-sky-700'],
                                ['t' => 'Disetujui / Ditolak', 'd' => 'Disetujui → izin sah berlaku. Ditolak → boleh edit & kirim ulang dari menu Izin.', 'c' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
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

                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 mt-1 flex gap-2.5">
                        <i data-lucide="layout-list" class="text-stone-500 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-stone-600">
                            Progress yang masih menunggu terlihat di <b>dashboard</b>. Menu <b>Izin</b> hanya
                            menampilkan data yang sudah final (disetujui / ditolak).
                        </p>
                    </div>
                </section>

                {{-- 3. 5 Jenis --}}
                <section id="jenis" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                        5 Jenis Izin & Aturannya
                    </h2>
                    <div class="space-y-2.5">
                        @php
                            $jenis = [
                                ['icon' => 'calendar-x', 'c' => 'bg-amber-50 border-amber-200 text-amber-600', 't' => 'Izin Tidak Masuk', 'd' => 'Tidak masuk kerja pada tanggal itu. Tidak memblokir absen — absen tetap bisa jika kamu akhirnya masuk.'],
                                ['icon' => 'alarm-clock', 'c' => 'bg-orange-50 border-orange-200 text-orange-600', 't' => 'Izin Terlambat', 'd' => 'Wajib isi <b>Jam Datang</b> antara <b>08.00–12.00</b>. Catatan: izin ini tidak otomatis menghapus keterlambatan di laporan.'],
                                ['icon' => 'clock-4', 'c' => 'bg-indigo-50 border-indigo-200 text-indigo-600', 't' => 'Izin Setengah Hari', 'd' => 'Izin paruh hari. Saat ini hanya berupa kategori surat — tidak ada aturan jam masuk/pulang otomatis di sistem.'],
                                ['icon' => 'log-out', 'c' => 'bg-cyan-50 border-cyan-200 text-cyan-600', 't' => 'Izin Pulang Cepat', 'd' => '<b>Wajib sudah absen masuk hari ini</b> sebelum mengajukan. Setelah disetujui, kamu <b>boleh absen pulang walau belum 8 jam</b>.'],
                                ['icon' => 'heart-pulse', 'c' => 'bg-rose-50 border-rose-200 text-rose-600', 't' => 'Sakit', 'd' => 'Sertakan bukti (disarankan surat dokter). Dihitung terpisah sebagai total Sakit di laporan.'],
                            ];
                        @endphp
                        @foreach ($jenis as $j)
                            <div class="flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3">
                                <div class="w-8 h-8 rounded-lg border {{ $j['c'] }} flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $j['icon'] }}" style="width:15px;height:15px;"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[13px] font-bold text-[#1c1917]">{{ $j['t'] }}</div>
                                    <div class="text-[12px] text-[#57534e] leading-relaxed mt-0.5">{!! $j['d'] !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- 4. Cara ajukan --}}
                <section id="cara" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-2.5">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
                        Cara Ajukan Izin
                    </h2>
                    <ol class="list-decimal pl-5 space-y-1.5 text-[13px] text-[#57534e] leading-relaxed">
                        <li>Buka menu <b>Pengajuan → Izin</b>, lalu tombol <b>+</b> (Ajukan).</li>
                        <li>Pilih <b>Kategori Izin</b> (Tidak Masuk / Terlambat / Setengah Hari / Pulang Cepat / Sakit).</li>
                        <li>Pilih <b>Tanggal Izin</b> (hari ini sebelum batas, atau tanggal mendatang).</li>
                        <li>Jika Terlambat: isi <b>Jam Datang</b> (08.00–12.00).</li>
                        <li>Isi <b>Keterangan</b> (min. 5 karakter) — alasan singkat & jelas.</li>
                        <li>Unggah <b>Bukti</b> (JPG/PNG/PDF, maks. 4 MB) — misal surat dokter untuk Sakit.</li>
                        <li>Tekan <b>Ajukan Izin</b> dan konfirmasi. Surat PDF otomatis dibuat dan dikirim ke atasanmu.</li>
                    </ol>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="alarm-clock" class="text-amber-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <div class="text-[12px] leading-relaxed text-amber-900">
                            <b>Penting soal waktu:</b> pengajuan untuk <b>hari ini</b> (selain Pulang Cepat) maksimal
                            <b>1 jam setelah jam masuk</b> unitmu (umumnya pukul 08.00 → batas 09.00).
                            Lewat dari itu, pilih tanggal lain. Untuk <b>Pulang Cepat</b>, pastikan sudah absen masuk dulu.
                        </div>
                    </div>
                </section>

                {{-- 5. Aturan jam --}}
                <section id="jam" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
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
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Batas ajukan izin hari ini</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Maks. 1 jam setelah jam masuk (±09.00)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Izin tanggal lain</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Bisa kapan saja</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Pulang Cepat — syarat</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Wajib sudah absen masuk hari ini</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Terlambat — jam datang</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">08.00 – 12.00 (wajib diisi)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Satu izin per tanggal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Tidak boleh dobel (nik + tanggal sama)</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Bukti file</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">JPG / PNG / PDF, maks. 4 MB</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Keterangan</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">5 – 500 karakter</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Absen pulang normal</td>
                                    <td class="text-[12.5px] font-semibold text-[#1c1917] py-2.5">Minimal 8 jam kerja</td>
                                </tr>
                                <tr>
                                    <td class="text-[12.5px] text-[#57534e] py-2.5 pr-3">Absen pulang + Pulang Cepat disetujui</td>
                                    <td class="text-[12.5px] font-semibold text-emerald-600 py-2.5">Boleh walau belum 8 jam</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="circle-alert" class="text-rose-600 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-rose-800">
                            Izin <b>Pulang Cepat</b> baru berlaku setelah statusnya <b>Disetujui</b>.
                            Selama masih menunggu, aturan 8 jam tetap berlaku.
                        </p>
                    </div>
                </section>

                {{-- 6. Status --}}
                <section id="status" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
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
                            <p class="text-[12px] text-emerald-900 mt-1.5 leading-relaxed">Izin sah berlaku. Pulang Cepat: boleh absen pulang &lt; 8 jam.</p>
                        </div>
                        <div class="border border-rose-200 bg-rose-50 rounded-xl p-3">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wide bg-rose-100 text-rose-700 border border-rose-200 rounded-full px-2 py-0.5">Ditolak</span>
                            <p class="text-[12px] text-rose-900 mt-1.5 leading-relaxed">Tidak disetujui. Ada alasan penolakan; boleh Edit lalu kirim ulang.</p>
                        </div>
                    </div>

                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 mt-3 flex gap-2.5">
                        <i data-lucide="layout-list" class="text-stone-500 shrink-0 mt-0.5" style="width:16px;height:16px;"></i>
                        <p class="text-[12px] leading-relaxed text-stone-600">
                            Daftar di menu <b>Izin</b> hanya menampilkan data yang sudah <b>final</b>
                            (disetujui / ditolak). Pengajuan yang masih menunggu terlihat di <b>dashboard</b>.
                        </p>
                    </div>
                </section>

                {{-- 7. Boleh & Tidak --}}
                <section id="larangan" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                        Yang Boleh & Tidak Boleh
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <div class="text-[12px] font-bold text-emerald-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-check" style="width:14px;height:14px;"></i> Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-emerald-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan izin hari ini <b>sebelum</b> batas (jam masuk + 1 jam).</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan izin tanggal mendatang kapan saja.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Edit & kirim ulang setelah <b>Ditolak</b>.</span></li>
                                <li class="flex gap-1.5"><span>✓</span><span>Ajukan Pulang Cepat setelah absen masuk hari ini.</span></li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <div class="text-[12px] font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                                <i data-lucide="circle-x" style="width:14px;height:14px;"></i> Tidak Boleh
                            </div>
                            <ul class="space-y-1.5 text-[12.5px] text-rose-900 leading-relaxed">
                                <li class="flex gap-1.5"><span>✗</span><span>Dua izin untuk tanggal yang sama.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Edit izin yang sudah Menunggu HR / Disetujui.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Pulang Cepat sebelum absen masuk.</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Jam Datang di luar 08.00–12.00 (jenis Terlambat).</span></li>
                                <li class="flex gap-1.5"><span>✗</span><span>Bukti file selain JPG/PNG/PDF, atau &gt; 4 MB.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 8. FAQ --}}
                <section id="faq" class="bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] scroll-mt-[130px] sm:p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1c1917] flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-[11px] font-bold shrink-0">8</span>
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                    <div class="divide-y divide-[#f0ece8]">
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Sampai kapan izin hari ini bisa diajukan?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Maksimal <b>1 jam setelah jam masuk</b> unitmu (umumnya pukul 09.00).
                                Lewat dari itu, pilih tanggal lain. Pengecualian: <b>Pulang Cepat</b>
                                (asal sudah absen masuk).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kenapa izin saya tidak muncul di menu Izin?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Menu Izin hanya menampilkan yang sudah <b>Disetujui</b> atau <b>Ditolak</b>.
                                Yang masih menunggu persetujuan terlihat di <b>dashboard</b>.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Izin ditolak, bagaimana?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Buka menu <b>Izin</b>, tap <b>Edit</b> di kartu yang ditolak, perbaiki sesuai alasan,
                                lalu Update. Status kembali ke awal dan diperiksa ulang (atasan → HR).
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Bisa hapus pengajuan izin?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Secara sistem, izin hanya bisa dihapus selama masih <b>Menunggu Atasan</b>.
                                Di aplikasi saat ini tidak ada tombol hapus di daftar — jika perlu batal,
                                hubungi atasan/HR.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Kapan Pulang Cepat boleh dipakai untuk absen pulang?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Setelah statusnya <b>Disetujui</b>. Syarat ajukan: sudah absen masuk hari ini.
                                Setelah disetujui, absen pulang boleh sebelum genap 8 jam.
                            </p>
                        </details>
                        <details class="group py-2.5">
                            <summary class="text-[13px] font-semibold text-[#1c1917] cursor-pointer list-none flex items-center justify-between gap-3">
                                Apa bedanya izin terlambat dengan absen terlambat biasa?
                                <i data-lucide="chevron-down" class="text-[#a8a29e] shrink-0 group-open:rotate-180 transition-transform" style="width:16px;height:16px;"></i>
                            </summary>
                            <p class="text-[12.5px] text-[#57534e] leading-relaxed mt-2">
                                Izin Terlambat adalah surat izin dengan perkiraan jam datang (08.00–12.00)
                                yang disetujui atasan & HR. Catatan: izin ini <b>tidak otomatis</b> menghapus
                                catatan keterlambatan di laporan presensi.
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
