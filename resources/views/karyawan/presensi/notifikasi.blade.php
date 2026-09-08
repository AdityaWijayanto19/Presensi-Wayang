@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Notifikasi</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="flex mt-[60px] pb-6">
        <div class="w-full px-3">

            @forelse($grouped ?? [] as $group)
                {{-- Date Header --}}
                <div class="flex items-center gap-3 mt-5 mb-3 px-1">
                    <div class="flex-1 h-px bg-[#e7e5e4]"></div>
                    <span class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase whitespace-nowrap">
                        <i data-lucide="calendar" class="align-middle" style="width:12px;height:12px;"></i>
                        {{ $group['label'] }}
                    </span>
                    <div class="flex-1 h-px bg-[#e7e5e4]"></div>
                </div>

                {{-- Notification Items --}}
                <div class="bg-white rounded-2xl border border-[#f0ece8] shadow-sm divide-y divide-[#f5f0eb] overflow-hidden">
                    @foreach($group['items'] as $n)
                        <div
                            class="p-3.5 hover:bg-[#fdf8f4] transition-colors duration-150 flex items-start justify-between gap-3 {{ is_null($n->read_at) ? 'bg-amber-50/50' : '' }}">
                            <div class="flex-1">
                                <!-- Pesan Notifikasi -->
                                <div class="text-[13px] font-medium text-[#1c1917] leading-snug">
                                    {{ $n->data['message'] ?? 'Notifikasi' }}
                                </div>
                                <!-- Waktu -->
                                <div class="text-[11px] text-[#a8a29e] mt-1.5 flex items-center gap-1">
                                    <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                    <span>{{ $n->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <!-- Indikator Belum Dibaca -->
                            @if (is_null($n->read_at))
                                <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 flex-shrink-0"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @empty
                <!-- Tampilan Jika Kosong -->
                <div class="bg-white rounded-2xl border border-[#f0ece8] shadow-sm p-8 mt-6 text-center">
                    <i data-lucide="bell-off" class="text-[#a8a29e] mb-2" style="width:32px;height:32px;"></i>
                    <div class="text-[13px] font-medium text-[#1c1917]">Belum ada notifikasi</div>
                    <p class="text-[11px] text-[#a8a29e] mt-1">Semua pemberitahuan terbaru akan muncul di sini.</p>
                </div>
            @endforelse

        </div>
    </div>
@endsection

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var unreadDots = document.querySelectorAll('.bg-amber-500');
    if (unreadDots.length > 0) {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).then(function() {
            document.querySelectorAll('.bg-amber-500').forEach(function(el) { el.remove(); });
            document.querySelectorAll('.bg-amber-50\\/50').forEach(function(el) { el.classList.remove('bg-amber-50/50'); });
        });
    }
});
</script>
@endpush
