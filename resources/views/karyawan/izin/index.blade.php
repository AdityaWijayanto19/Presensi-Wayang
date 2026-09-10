@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Data Izin / Sakit</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $messagesuccess = Session::get('success');
        $messageerror = Session::get('error');
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
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

    {{-- Header Info --}}
    @if ($dataizin->count() > 0)
        <div class="flex mt-3">
            <div class="w-full px-3">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" style="width:13px;height:13px;"></i> {{ $dataizin->count() }} Data</span>
                        <span class="mx-1.5 text-[#e7e5e4]">•</span>
                        <span class="text-[#78716c]">Terbaru di atas</span>
                    </p>
                    <span class="text-[11px] font-medium text-[#78716c] bg-white border border-[#f0ece8] rounded-full px-2.5 py-1">{{ date('M Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Data Izin — Redesigned Cards --}}
    <div class="flex mt-3">
        <div class="w-full px-3">
            @forelse ($dataizin as $d)
                @php
                    $ts = strtotime($d->tgl_izin);
                    $weekday = $weekdayMap[date('l', $ts)] ?? date('l', $ts);
                    $displayDate = date('d M Y', $ts);
                    $isIzin = $d->jenis_izin === 'i';
                    $ext = strtolower(pathinfo($d->file, PATHINFO_EXTENSION));
                    $label = $isIzin ? 'Izin' : 'Sakit';
                @endphp
                <div class="presensi-card mb-2.5">
                    {{-- Top Row --}}
                    <div class="flex items-start gap-3">
                        <div class="presensi-icon-box {{ $isIzin ? 'icon-izin' : 'icon-sakit' }}">
                            @if ($isIzin)
                                <i data-lucide="file-text"></i>
                            @else
                                <i data-lucide="cross"></i>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[14.5px] font-bold text-[#1c1917] tracking-tight leading-none">{{ $displayDate }}</span>
                                <span class="presensi-badge {{ $isIzin ? 'badge-izin' : 'badge-sakit' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isIzin ? 'bg-amber-500' : 'bg-rose-500' }}"></span>
                                    {{ $label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <i data-lucide="calendar" class="text-[#a8a29e]" style="width:12px;height:12px;"></i>
                                <span class="text-[12px] font-medium text-[#78716c]">{{ $weekday }}</span>
                                <span class="w-1 h-1 rounded-full bg-[#e7e5e4]"></span>
                                <span class="text-[11px] text-[#a8a29e]">Diajukan</span>
                            </div>
                        </div>

                        <form action="/izin/{{ $d->id }}" method="POST" class="form-delete shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-card" aria-label="Hapus">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </form>
                    </div>

                    <div class="presensi-divider"></div>

                    {{-- Bottom Row --}}
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button"
                                class="file-pill js-preview"
                                data-url="/presensi/showfile/{{ $d->file }}"
                                data-filename="{{ $d->file }}"
                                data-label="Dokumen {{ $label }} — {{ $displayDate }}">
                                <i data-lucide="eye"></i>
                                Dokumen {{ $label }}
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-[#f5f5f4] border border-[#e7e5e4] text-[9px] font-bold tracking-wide text-[#57534e] uppercase">{{ $ext }}</span>
                            </button>
                        </div>
                        <span class="presensi-badge badge-uploaded">
                            <i data-lucide="circle-check" style="width:12px;height:12px;"></i> Uploaded
                        </span>
                    </div>
                </div>
            @empty
                <x-admin.card class="p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#fdf8f4] border border-[#f0ece8] flex items-center justify-center mx-auto">
                        <i data-lucide="file-text" class="text-[#d6c7b8]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Belum Ada Data Izin</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[26ch] mx-auto">Data izin / sakit yang kamu ajukan akan muncul di sini. Tap tombol di bawah untuk mengajukan baru.</p>
                    <a href="/izin/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajukan Izin / Sakit
                    </a>
                </x-admin.card>
            @endforelse
        </div>
    </div>

    {{-- Floating Action Button --}}
    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/izin/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data">
            <i data-lucide="plus"></i>
        </a>
    </div>

    <script>
        setTimeout(function () {
            let alert = document.getElementById('alert-success');
            if (alert) { alert.style.opacity = '0'; alert.style.transition = 'opacity 0.3s'; setTimeout(() => alert.style.display = 'none', 300); }
        }, 3000);

        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Data?',
                    text: 'Data izin / sakit akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#7a5234',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) { form.submit(); }
                });
            });
        });
    </script>
@endsection
