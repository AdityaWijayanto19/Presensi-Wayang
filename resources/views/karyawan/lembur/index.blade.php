@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Riwayat Lembur</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $messagesuccess = Session::get('success');
        $messageerror = Session::get('error');
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        $statusLabels = [
            'pending_atasan' => 'Menunggu Persetujuan',
            'pending_admin' => 'Menunggu Persetujuan HR',
            'approved' => 'Selesai',
            'rejected' => 'Ditolak',
        ];
        $statusColors = [
            'pending_atasan' => 'bg-amber-100 text-amber-700 border-amber-200',
            'pending_admin' => 'bg-amber-100 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
        ];
    @endphp

    {{-- Alert --}}
    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if ($messagesuccess)
                <div class="flex items-center gap-2.5 bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[13px] font-medium rounded-xl py-2.5 px-3.5" id="alert-success">
                    <i data-lucide="circle-check" class="text-[#10b981] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messagesuccess }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="shrink-0 w-6 h-6 rounded-full bg-white border border-[#a7f3d0] flex items-center justify-center text-[#065f46]"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
                </div>
            @endif
            @if ($messageerror)
                <div class="flex items-center gap-2.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] font-medium rounded-xl py-2.5 px-3.5">
                    <i data-lucide="circle-alert" class="text-[#ef4444] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messageerror }}</span>
                </div>
            @endif
        </div>
    </div>

    @if ($datalembur->count() > 0)
        <div class="flex mt-3">
            <div class="w-full px-3">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="history" style="width:13px;height:13px;"></i> {{ $datalembur->count() }} Riwayat</span>
                    </p>
                    <span class="text-[11px] font-medium text-[#78716c] bg-white border border-[#f0ece8] rounded-full px-2.5 py-1">{{ date('M Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="flex mt-3">
        <div class="w-full px-3">
            @forelse ($datalembur as $d)
                @php
                    $ts = strtotime($d->tgl_lembur);
                    $weekday = $weekdayMap[date('l', $ts)] ?? date('l', $ts);
                    $displayDate = date('d M Y', $ts);
                    $status = $d->status instanceof \App\Enums\LemburStatus ? $d->status->value : $d->status;
                    $statusLabel = $statusLabels[$status] ?? $status;
                    $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    $durasi = $d->durasi_formatted;
                @endphp
                <div class="presensi-card mb-2.5">
                    <div class="flex items-start gap-3">
                        <div class="presensi-icon-box icon-lembur">
                            <i data-lucide="timer"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[14.5px] font-bold text-[#1c1917] tracking-tight leading-none">{{ $displayDate }}</span>
                                <span class="presensi-badge {{ $statusColor }} border text-[11px] font-medium px-2 py-0.5 rounded-full">
                                    {{ $statusLabel }}
                                </span>
                                @if ($durasi)
                                    <span class="presensi-badge badge-lembur">
                                        <i data-lucide="hourglass" style="width:11px;height:11px;"></i> {{ $durasi }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <i data-lucide="calendar" class="text-[#a8a29e]" style="width:12px;height:12px;"></i>
                                <span class="text-[12px] font-medium text-[#78716c]">{{ $weekday }}</span>
                            </div>
                            @if (!empty($d->keterangan))
                                <div class="mt-1 text-[12px] text-[#78716c] line-clamp-2">{{ $d->keterangan }}</div>
                            @endif
                            @if ($status === 'rejected' && !empty($d->rejected_reason))
                                <div class="mt-1 text-[11px] text-rose-500">Alasan: {{ $d->rejected_reason }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="presensi-divider"></div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if (!empty($d->pdf_form_path))
                            <a href="/presensi/showfilelembur/{{ basename($d->pdf_form_path) }}" target="_blank"
                               class="file-pill">
                                <i data-lucide="file"></i> Pengajuan
                            </a>
                        @endif

                        @if (!empty($d->laporan_file))
                            <a href="/presensi/showfilelembur/{{ basename($d->laporan_file) }}" target="_blank"
                               class="file-pill">
                                <i data-lucide="file-check"></i> Laporan
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <x-admin.card class="p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#f5f3ff] border border-[#ede9fe] flex items-center justify-center mx-auto text-[#7c3aed]">
                        <i data-lucide="timer" class="text-[#7c3aed]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Belum Ada Riwayat Lembur</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[26ch] mx-auto">Riwayat lembur yang sudah selesai akan tampil di sini.</p>
                    <a href="/lembur/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajukan Lembur
                    </a>
                </x-admin.card>
            @endforelse
        </div>
    </div>

    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/lembur/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data">
            <i data-lucide="plus"></i>
        </a>
    </div>

    <script>
        setTimeout(function () {
            let alert = document.getElementById('alert-success');
            if (alert) { alert.style.opacity = '0'; alert.style.transition = 'opacity 0.3s'; setTimeout(() => alert.style.display = 'none', 300); }
        }, 3000);
    </script>
@endsection
