<style>
    #map { height: 250px; }
</style>

@php
    $labelFlag = fn ($f) => \App\Models\Presensi::FLAG_LABELS[$f] ?? $f;
@endphp

<div class="mb-2 flex flex-wrap gap-1">
    @if ($presensi->dicurigaiManipulasi())
        <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 text-[10px] px-2 py-0.5 font-medium"
            title="Indikasi manipulasi lokasi">
            Anomali: {{ collect($presensi->flags())->map($labelFlag)->implode(', ') }}
        </span>
    @endif

    @if ($presensi->lokasi_out_jarak !== null)
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-medium">
            Jarak ke kantor: {{ $presensi->lokasi_out_jarak }} m
        </span>
    @endif

    @if ($presensi->lokasi_out_akurasi !== null)
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-medium">
            Akurasi &plusmn;{{ rtrim(rtrim(number_format($presensi->lokasi_out_akurasi, 2, '.', ','), '0'), '.') }} m
        </span>
    @endif

    @if ($presensi->gps_fix_out !== null)
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-medium">
            Fix: {{ $presensi->gps_fix_out }}
        </span>
    @endif

    @if ($presensi->gps_durasi_out_ms !== null)
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-medium">
            Pantau: {{ (int) round($presensi->gps_durasi_out_ms / 1000) }} dtk
        </span>
    @endif

    @if ($presensi->ip_out)
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-medium">
            IP: {{ $presensi->ip_out }}
        </span>
    @endif
</div>

<div id="map"
     data-lokasi="{{ $presensi->lokasi_out }}"
     data-label="Lokasi Pulang - {{ $presensi->karyawan->nama_lengkap ?? '' }}"
     data-color="blue">
</div>
