@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
         <div class="left">
            <a href="/pengajuan" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Cuti Tahunan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    @endphp

    {{-- Kartu Kuota --}}
    <div class="flex mt-3">
        <div class="w-full px-3">
            <x-admin.card class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Kuota Cuti Tahunan</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-[28px] font-bold text-[#1c1917] leading-none">{{ $sisaCuti }}</span>
                            <span class="text-[13px] text-[#78716c]">/ {{ $jatahCuti }} hari</span>
                        </div>
                        <div class="text-[11px] text-[#78716c] mt-1">Terpakai: {{ $terpakai }} hari</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-check" class="text-emerald-600" style="width:22px;height:22px;"></i>
                    </div>
                </div>
                <div class="mt-3 h-2 rounded-full bg-[#f0ece8] overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $jatahCuti > 0 ? round(($terpakai / $jatahCuti) * 100) : 0 }}%"></div>
                </div>
            </x-admin.card>
        </div>
    </div>

    @if ($datacuti->count() > 0)
        <div class="flex mt-3">
            <div class="w-full px-3">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="check-check" class="text-emerald-600" style="width:13px;height:13px;"></i> {{ $datacuti->count() }} Data</span>
                    </p>
                    <span class="text-[11px] font-medium text-[#78716c] bg-white border border-[#f0ece8] rounded-full px-2.5 py-1">{{ date('M Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="flex mt-3">
        <div class="w-full px-3">
            @forelse ($datacuti as $d)
                @php
                    $tanggalList = is_array($d->tanggal_cuti) ? $d->tanggal_cuti : (json_decode($d->tanggal_cuti, true) ?: []);
                    $tanggalDisplay = collect($tanggalList)
                        ->map(function ($t) use ($weekdayMap) {
                            $ts = strtotime($t);
                            $hari = $weekdayMap[date('l', $ts)] ?? date('l', $ts);
                            return $hari . ', ' . date('d M Y', $ts);
                        })
                        ->implode('<br>');
                    $tanggalPlain = collect($tanggalList)
                        ->map(fn ($t) => date('d M Y', strtotime($t)))
                        ->implode(', ');
                    $uploaded = $d->dikirim_tanggal instanceof \Carbon\Carbon
                        ? $d->dikirim_tanggal->format('d M Y H:i')
                        : date('d M Y H:i', strtotime($d->dikirim_tanggal));
                    $buktiUrl = !empty($d->bukti_file) ? \Illuminate\Support\Facades\Storage::disk('public')->url('uploads/cuti/' . $d->bukti_file) : null;
                @endphp
                <div class="presensi-card mb-2.5">
                    <div class="flex items-start gap-3">
                        <div class="presensi-icon-box icon-izin">
                            <i data-lucide="palmtree"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[14.5px] font-bold text-[#1c1917] tracking-tight leading-none">{{ $d->durasi_hari }} Hari Cuti</span>
                                <span class="presensi-badge bg-emerald-100 text-emerald-700 border-emerald-200">Terpakai {{ $d->durasi_hari }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <i data-lucide="calendar" class="text-[#a8a29e]" style="width:12px;height:12px;"></i>
                                <span class="text-[12px] font-medium text-[#78716c]">{!! $tanggalDisplay !!}</span>
                            </div>
                            @if (!empty($d->keterangan))
                                <div class="text-[12px] text-[#57534e] mt-1.5 leading-snug">{{ Str::limit($d->keterangan, 80) }}</div>
                            @endif
                            <div class="text-[11px] text-[#a8a29e] mt-1">Diupload {{ $uploaded }}</div>
                        </div>
                    </div>

                    <div class="presensi-divider"></div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if($buktiUrl)
                            <button type="button" class="file-pill js-preview" data-url="{{ $buktiUrl }}" data-filename="{{ basename($buktiUrl) }}" data-label="File Cuti — {{ $tanggalPlain }}">
                                <i data-lucide="paperclip"></i> File Cuti
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-[#f0f9ff] border border-[#bae6fd] text-[9px] font-bold text-[#0369a1] uppercase">{{ strtolower(pathinfo($d->bukti_file, PATHINFO_EXTENSION)) }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <x-admin.card class="p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#ecfdf5] border border-[#a7f3d0] flex items-center justify-center mx-auto text-[#059669]">
                        <i data-lucide="palmtree" class="text-[#059669]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Belum Ada Riwayat Cuti</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[28ch] mx-auto">Riwayat cuti tahunan yang sudah diupload akan muncul di sini.</p>
                    <a href="/cuti/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition"
                        @if ($sisaCuti <= 0) data-sisa-habis="1" @endif>
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Upload Cuti
                    </a>
                </x-admin.card>
            @endforelse
        </div>
    </div>

    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/cuti/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data"
            @if ($sisaCuti <= 0) data-sisa-habis="1" @endif>
            <i data-lucide="plus"></i>
        </a>
    </div>

    <script>
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-sisa-habis]');
            if (!el) return;
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Kuota Cuti Anda 0',
                text: 'Anda tidak dapat mengajukan cuti tahunan karena kuota cuti Anda sudah habis.',
                confirmButtonColor: '#7a5234',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endsection
