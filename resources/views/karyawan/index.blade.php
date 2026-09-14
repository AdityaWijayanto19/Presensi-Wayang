@extends('layouts.presensi')

@php

    use Illuminate\Support\Facades\Storage;

    $user = Auth::guard('karyawan')->user();
    $nama = explode(' ', $user->nama_lengkap);
    $namaPendek = implode(' ', array_slice($nama, 0, 2));
    $pathFoto = Storage::url('uploads/karyawan/' . $user->foto);

@endphp


@section('content')

    {{-- HEADER USER — SVG Pattern (exact code as provided) --}}
    <div class="section overflow-hidden" id="user-section"
        style="height:220px;padding:20px;position:relative;margin-top:-60px;padding-top:80px;">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute inset-0 w-full h-full" aria-hidden="true">
            <defs>
                <pattern id="a" width="35.584" height="30.585" patternTransform="scale(2)"
                    patternUnits="userSpaceOnUse">
                    <rect width="100%" height="100%" fill="#795234" />
                    <path fill="#65452f"
                        d="M36.908 9.243c-5.014 0-7.266 3.575-7.266 7.117 0 3.376 2.45 5.726 5.959 5.726 1.307 0 2.45-.463 3.244-1.307.744-.811 1.125-1.903 1.042-3.095-.066-.811-.546-1.655-1.274-2.185-.596-.447-1.639-.894-3.162-.546a.87.87 0 0 0-.662 1.06c.1.48.58.777 1.06.661.695-.149 1.274-.066 1.705.249.364.265.546.645.562.893.05.679-.165 1.308-.579 1.755-.446.48-1.125.744-1.936.744-2.55 0-4.188-1.538-4.188-3.938 0-2.466 1.44-5.347 5.495-5.347 2.897 0 6.008 1.888 6.388 6.058.166 1.804.067 5.147-2.598 7.034a1 1 0 0 0-.142.122c-1.311.783-2.87 1.301-4.972 1.301-4.088 0-6.123-1.952-8.275-4.021-2.317-2.218-4.7-4.518-9.517-4.518-4.094 0-6.439 1.676-8.479 3.545.227-1.102.289-2.307.17-3.596-.496-5.263-4.567-7.662-8.159-7.662-5.015 0-7.265 3.574-7.265 7.116 0 3.377 2.45 5.727 5.958 5.727 1.307 0 2.449-.463 3.243-1.308.745-.81 1.126-1.903 1.043-3.095-.066-.81-.546-1.654-1.274-2.184-.596-.447-1.639-.894-3.161-.546a.87.87 0 0 0-.662 1.06.866.866 0 0 0 1.059.66c.695-.148 1.275-.065 1.705.25.364.264.546.645.563.893.05.679-.166 1.307-.58 1.754-.447.48-1.125.745-1.936.745-2.549 0-4.188-1.539-4.188-3.939 0-2.466 1.44-5.345 5.495-5.345 2.897 0 6.008 1.87 6.389 6.057.163 1.781.064 5.06-2.504 6.96-1.36.864-2.978 1.447-5.209 1.447-4.088 0-6.124-1.952-8.275-4.021-2.317-2.218-4.7-4.518-9.516-4.518v1.787c4.088 0 6.123 1.953 8.275 4.022 2.317 2.218 4.7 4.518 9.516 4.518 4.8 0 7.2-2.3 9.517-4.518 2.151-2.069 4.187-4.022 8.275-4.022s6.124 1.953 8.275 4.022c2.318 2.218 4.701 4.518 9.517 4.518 4.8 0 7.2-2.3 9.516-4.518 2.152-2.069 4.188-4.022 8.276-4.022s6.123 1.953 8.275 4.022c2.317 2.218 4.7 4.518 9.517 4.518v-1.788c-4.088 0-6.124-1.952-8.275-4.021-2.318-2.218-4.701-4.518-9.517-4.518-4.103 0-6.45 1.683-8.492 3.556.237-1.118.304-2.343.184-3.656-.497-5.263-4.568-7.663-8.16-7.663" />
                    <path fill="#65452f"
                        d="M23.42 41.086a.9.9 0 0 1-.729-.38.883.883 0 0 1 .215-1.242c2.665-1.887 2.764-5.23 2.599-7.034-.38-4.187-3.492-6.058-6.389-6.058-4.055 0-5.495 2.88-5.495 5.346 0 2.4 1.639 3.94 4.188 3.94.81 0 1.49-.265 1.936-.745.414-.447.63-1.076.58-1.755-.017-.248-.2-.629-.547-.893-.43-.315-1.026-.398-1.704-.249a.87.87 0 0 1-1.06-.662.87.87 0 0 1 .662-1.059c1.523-.348 2.566.1 3.161.546.729.53 1.209 1.374 1.275 2.185.083 1.191-.298 2.284-1.043 3.095-.794.844-1.936 1.307-3.244 1.307-3.508 0-5.958-2.35-5.958-5.726 0-3.542 2.25-7.117 7.266-7.117 3.591 0 7.663 2.4 8.16 7.663.347 3.79-.828 6.868-3.344 8.656a.82.82 0 0 1-.53.182zm0-30.585a.9.9 0 0 1-.729-.38.883.883 0 0 1 .215-1.242c2.665-1.887 2.764-5.23 2.599-7.034-.381-4.187-3.493-6.058-6.389-6.058-4.055 0-5.495 2.88-5.495 5.346 0 2.4 1.639 3.94 4.188 3.94.81 0 1.49-.266 1.936-.746.414-.446.629-1.075.58-1.754-.017-.248-.2-.629-.547-.894-.43-.314-1.026-.397-1.705-.248A.87.87 0 0 1 17.014.77a.87.87 0 0 1 .662-1.06c1.523-.347 2.566.1 3.161.547.729.53 1.209 1.374 1.275 2.185.083 1.191-.298 2.284-1.043 3.095-.794.844-1.936 1.307-3.244 1.307-3.508 0-5.958-2.35-5.958-5.726 0-3.542 2.25-7.117 7.266-7.117 3.591 0 7.663 2.4 8.16 7.663.347 3.79-.828 6.868-3.344 8.656a.82.82 0 0 1-.53.182zm29.956 1.572c-4.8 0-7.2-2.3-9.517-4.518-2.151-2.069-4.187-4.022-8.275-4.022S29.46 5.486 27.31 7.555c-2.317 2.218-4.7 4.518-9.517 4.518-4.8 0-7.2-2.3-9.516-4.518C6.124 5.486 4.088 3.533 0 3.533s-6.124 1.953-8.275 4.022c-2.317 2.218-4.7 4.518-9.517 4.518-4.8 0-7.2-2.3-9.516-4.518-2.152-2.069-4.188-4.022-8.276-4.022V1.746c4.8 0 7.2 2.3 9.517 4.518 2.152 2.069 4.187 4.022 8.275 4.022s6.124-1.953 8.276-4.022C-7.2 4.046-4.816 1.746 0 1.746c4.8 0 7.2 2.3 9.517 4.518 2.151 2.069 4.187 4.022 8.275 4.022s6.124-1.953 8.275-4.022c2.318-2.218 4.7-4.518 9.517-4.518 4.8 0 7.2 2.3 9.517 4.518 2.151 2.069 4.187 4.022 8.275 4.022s6.124-1.953 8.275-4.022c2.317-2.218 4.7-4.518 9.517-4.518v1.787c-4.088 0-6.124 1.953-8.275 4.022-2.317 2.234-4.717 4.518-9.517 4.518" />
                </pattern>
            </defs>
            <rect width="800%" height="800%" fill="url(#a)" transform="translate(0 -.17)" />
        </svg>

        <div class="absolute right-[15px] flex items-center gap-3 z-20">
            <a href="/notifications" id="btnNotif"
                class="relative text-white text-[22px] hover:text-[#bdb4b4] inline-flex items-center justify-center">
                <i data-lucide="bell"></i>
                <span id="notifBadge"
                    class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-[10px] min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1">0</span>
            </a>
            <a href="/settings" class="text-white text-[22px] no-underline hover:text-[#bdb4b4]">
                <i data-lucide="settings"></i>
            </a>
        </div>

        <div class="mt-5 flex relative z-10">
            <div class="avatar">
                @if ($user->foto != null)
                    <img src="{{ url($pathFoto) }}?v={{ time() }}" alt="avatar"
                        class="w-16 h-16 object-cover object-[center_15%] rounded-full">
                @else
                    <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}" alt="avatar"
                        class="w-16 h-16 object-cover object-[center_15%] rounded-full">
                @endif
            </div>
            <div class="ml-[30px] leading-[2px]">
                <h2 class="text-white" id="user-name">{{ $namaPendek }}</h2>
                <span class="text-white/80 text-xs truncate max-w-30 block" id="user-role"
                    title="{{ Auth::guard('karyawan')->user()->posisi }}">
                    {{ Auth::guard('karyawan')->user()->posisi }}
                </span>
            </div>
        </div>

    </div>

    {{-- REKAP PRESENSI --}}
    <div class="section px-4" id="presence-section"
        style="margin-top:-30px;width:100%;background-color:#e9ecef;border-radius:15px 15px 0 0;position:relative;z-index:2;">

        <br>

        <h3>Rekap Presensi Bulan {{ $namabulan[$bulanini] }} {{ $tahunini }}</h3>

        <div id="rekappresensi" class="mt-2">
            <div class="flex flex-wrap -mx-2">

                {{-- Hadir --}}
                <div class="w-1/2 sm:w-1/4 px-2 mb-2">
                    <div class="card text-center py-3 px-2 rounded-[10px] h-full relative overflow-hidden">
                        <div class="p-3">
                            <i data-lucide="person-standing" class="text-green-500 mb-1"
                                style="width:28px;height:28px;"></i>
                            <br>
                            <span class="text-center text-xs font-bold block mt-1 leading-[1.2]">Hadir</span>
                        </div>
                        @if ($rekappresensi->jmlhadir > 0)
                            <span id="rekap-hadir"
                                class="absolute bottom-0 left-0 bg-green-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg">
                                {{ $rekappresensi->jmlhadir }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- WFH --}}
                <div class="w-1/2 sm:w-1/4 px-2 mb-2">
                    <div class="card text-center py-3 px-2 rounded-[10px] h-full relative overflow-hidden">
                        <div class="p-3">
                            <i data-lucide="home" class="text-blue-500 mb-1" style="width:28px;height:28px;"></i>
                            <br>
                            <span class="text-center text-xs font-bold block mt-1 leading-[1.2]">WFH</span>
                        </div>
                        @if ($rekapwfh->jmlwfh > 0)
                            <span id="rekap-wfh"
                                class="absolute bottom-0 left-0 bg-blue-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg">
                                {{ $rekapwfh->jmlwfh }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Lembur --}}
                <div class="w-1/2 sm:w-1/4 px-2 mb-2">
                    <div class="card text-center py-3 px-2 rounded-[10px] h-full relative overflow-hidden">
                        <div class="p-3">
                            <i data-lucide="hourglass" class="text-yellow-500 mb-1" style="width:28px;height:28px;"></i>
                            <br>
                            <span class="text-center text-xs font-bold block mt-1 leading-[1.2]">Lembur</span>
                        </div>
                        @if (($rekaplembur->jmllembur ?? 0) > 0)
                            <span id="rekap-lembur"
                                class="absolute bottom-0 left-0 bg-yellow-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg">
                                {{ $rekaplembur->jmllembur }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Izin / Sakit --}}
                <div class="w-1/2 sm:w-1/4 px-2 mb-2">
                    <div class="card text-center py-3 px-2 rounded-[10px] h-full relative overflow-hidden">
                        <div class="p-3">
                            <i data-lucide="file-text" class="text-red-500 mb-1" style="width:28px;height:28px;"></i>
                            <br>
                            <span class="text-center text-xs font-bold block mt-1 leading-[1.2]">Izin / Sakit</span>
                        </div>
                        @if ($rekapizin->jmlizin > 0)
                            <span id="rekap-izin"
                                class="absolute bottom-0 left-0 bg-red-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg">
                                {{ $rekapizin->jmlizin }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- PRESENSI HARI INI --}}
        <div class="mt-6 sm:mt-8 mb-5">
            <div class="flex flex-wrap -mx-2">

                {{-- Presensi Masuk --}}
                <div class="w-1/2 px-2">
                    <div class="card text-white bg-gradient-to-br from-green-500 to-green-500/80">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] flex-shrink-0 flex items-center justify-center" id="presensi-foto-in-wrap">
                                    @if ($presensihariini != null)
                                        @php
                                            $path = Storage::url('uploads/absensi/' . $presensihariini->foto_in);
                                        @endphp
                                        <img src="{{ url($path) }}?v={{ time() }}" alt=""
                                            id="presensi-foto-in"
                                            class="w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] object-cover rounded-xl">
                                    @else
                                        <i data-lucide="camera" id="presensi-foto-in-placeholder" class="text-[26px] sm:text-[30px]"></i>
                                    @endif
                                </div>
                                <div class="leading-[1.3] min-w-0">
                                    <h4 class="text-white font-semibold text-sm sm:text-base mb-1">Masuk</h4>
                                    <span id="presensi-jam-in" class="text-[11px] sm:text-[13px] block break-words">
                                        {{ $presensihariini != null ? $presensihariini->jam_in : 'Belum Presensi' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Presensi Pulang --}}
                <div class="w-1/2 px-2">
                    <div class="card text-white bg-gradient-to-br from-red-600 to-red-600/80">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] flex-shrink-0 flex items-center justify-center" id="presensi-foto-out-wrap">
                                    @if ($presensihariini != null && $presensihariini->jam_out != null)
                                        @php
                                            $path = Storage::url('uploads/absensi/' . $presensihariini->foto_out);
                                        @endphp
                                        <img src="{{ url($path) }}?v={{ time() }}" alt=""
                                            id="presensi-foto-out"
                                            class="w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] object-cover rounded-xl">
                                    @else
                                        <i data-lucide="camera" id="presensi-foto-out-placeholder" class="text-[26px] sm:text-[30px]"></i>
                                    @endif
                                </div>
                                <div class="leading-[1.3] min-w-0">
                                    <h4 class="text-white font-semibold text-sm sm:text-base mb-1">Pulang</h4>
                                    <span id="presensi-jam-out" class="text-[11px] sm:text-[13px] block break-words">
                                        {{ $presensihariini != null && $presensihariini->jam_out != null ? $presensihariini->jam_out : 'Belum Presensi' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- WFH SAYA & PERLU PERSETUJUAN (Section Baru) --}}
        {{-- PENDING LAPORAN ATASAN --}}
        <div id="pendingLaporanSection">
            @if (isset($pendingLaporanAtasan) && $pendingLaporanAtasan->count() > 0)
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-violet-100 border border-violet-200 flex items-center justify-center text-violet-700"><i
                                    data-lucide="file-text"></i></span>
                            Laporan Perlu Persetujuan ({{ $pendingLaporanAtasan->count() }})
                        </h3>
                    </div>
                    @foreach ($pendingLaporanAtasan as $p)
                        <div class="card mb-2 border-l-4 border-l-violet-400 bg-violet-50/50">
                            <div class="card-body p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-[#1c1917]">{{ $p->nama_lengkap }} <span
                                                class="text-[11px] font-normal text-[#78716c]">• {{ $p->jabatan }} •
                                                {{ $p->posisi }}</span></div>
                                        <div class="text-[11px] text-[#78716c]">
                                            {{ date('d M Y', strtotime($p->tgl_wfh)) }} •
                                            {{ $p->unit }} ({{ $p->perusahaan }})</div>
                                        <div class="text-[11px] text-[#57534e] mt-1">Laporan WFH menunggu persetujuan Anda
                                        </div>
                                        @if (!empty($p->laporan_file))
                                            <div class="mt-1">
                                                <a href="{{ Storage::url($p->laporan_file) }}"
                                                    target="_blank"
                                                    class="text-[11px] text-sky-700 hover:underline">Form Laporan</a>
                                            </div>
                                        @elseif(!empty($p->laporan_deskripsi))
                                            <div class="mt-1">
                                                <button type="button"
                                                    class="text-[11px] text-sky-700 hover:underline cursor-pointer js-preview-laporan"
                                                    data-deskripsi="{{ $p->laporan_deskripsi }}"
                                                    data-tgl="{{ date('d M Y', strtotime($p->tgl_wfh)) }}"
                                                    data-label="Laporan WFH — {{ $p->nama_lengkap }}">Form
                                                    Laporan</button>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-1.5 shrink-0">
                                        <button type="button"
                                            class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] w-full btn-approve-laporan-atasan"
                                            data-id="{{ $p->id }}">Setujui</button>
                                        <button type="button"
                                            class="btn btn-sm bg-white border border-rose-200 text-rose-700 rounded-full px-3 py-1 text-[11px] w-full btn-reject-laporan-atasan-dynamic"
                                            data-id="{{ $p->id }}">Tolak</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div id="pendingAtasanSection">
            @if (isset($pendingAtasan) && $pendingAtasan->count() > 0)
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700"><i
                                    data-lucide="shield-check"></i></span>
                            Pengajuan Perlu Persetujuan
                        </h3>
                    </div>
                    @foreach ($pendingAtasan as $p)
                        <div class="card mb-2 border-l-4 border-l-amber-400 bg-amber-50/50">
                            <div class="card-body p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-[#1c1917]">{{ $p->karyawan->nama_lengkap ?? '-' }} <span
                                                class="text-[11px] font-normal text-[#78716c]">• {{ $p->karyawan->jabatan ?? '-' }} •
                                                {{ $p->karyawan->posisi ?? '-' }}</span></div>
                                        <div class="text-[11px] text-[#78716c]">
                                            {{ date('d M Y', strtotime($p->tgl_wfh)) }} •
                                            {{ $p->karyawan->unit ?? '-' }} ({{ $p->karyawan->unitperusahaan->perusahaan ?? '-' }})</div>
                                        <div class="text-[11px] text-[#57534e] mt-1 line-clamp-2">
                                            {{ Str::limit($p->deskripsi_pekerjaan, 70) }}</div>
                                    </div>
                                    <div class="flex flex-col gap-1.5 shrink-0">
                                        <button type="button"
                                            class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] w-full btn-approve-atasan"
                                            data-id="{{ $p->id }}">Setujui</button>
                                        <button type="button"
                                            class="btn btn-sm bg-white border border-rose-200 text-rose-700 rounded-full px-3 py-1 text-[11px] w-full btn-reject-atasan-dynamic"
                                            data-id="{{ $p->id }}">Tolak</button>
                                    </div>
                                </div>
                                <div class="flex">
                                    @php $pdfUrl = !empty($p->pdf_form_path) ? Storage::url($p->pdf_form_path) : (!empty($p->file_form) ? "/presensi/showfilewfh/{$p->file_form}" : null); @endphp
                                    @if ($pdfUrl)
                                        <button type="button"
                                            class="text-[11px] text-sky-700 hover:underline cursor-pointer"
                                            onclick="window.open('{{ $pdfUrl }}','_blank')">Form Pengajuan</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div id="perluTindakanSection">
            @if (isset($wfhSaya) && $wfhSaya->count() > 0)
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-sky-100 border border-sky-200 flex items-center justify-center text-sky-700"><i
                                    data-lucide="home"></i></span>
                            Perlu Tindakan
                        </h3>
                        <a href="/wfh" class="text-[11px] font-semibold text-sky-700">Lihat Semua</a>
                    </div>
                    @foreach ($wfhSaya as $w)
                        @php
                            $badge = match ($w->status) {
                                'pending_atasan' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'pending_admin' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'unpaid' => 'bg-gray-100 text-gray-700 border-gray-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                            };
                            $label = match ($w->status) {
                                'pending_atasan' => 'Menunggu Persetujuan',
                                'pending_admin' => 'Menunggu Persetujuan HR',
                                'approved' => empty($w->laporan_deskripsi) ? 'Menunggu Laporan' : 'Disetujui',
                                'rejected' => 'Ditolak',
                                'unpaid' => 'Unpaid',
                                default => $w->status,
                            };
                            $lStatus = $w->laporan_status ?? null;
                        @endphp
                        <div class="card mb-2">
                            <div class="card-body p-3 flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-bold text-[#1c1917]">
                                        {{ date('d M Y', strtotime($w->tgl_wfh)) }} <span
                                            class="ml-1 inline-flex items-center rounded-full border text-[10px] px-2 py-0.5 {{ $badge }}">{{ $label }}</span>
                                    </div>
                                    @if (!empty($w->keterangan))
                                        <div class="text-[11px] text-[#78716c] mt-0.5 italic">
                                            {{ Str::limit($w->keterangan, 50) }}</div>
                                    @endif
                                    <div class="text-[11px] text-[#78716c] mt-0.5">
                                        {{ Str::limit($w->deskripsi_pekerjaan, 50) }}</div>
                                    @if ($lStatus)
                                        @php
                                            $lBadge = match ($lStatus) {
                                                'pending_atasan' => 'bg-amber-100 text-amber-700',
                                                'pending_admin' => 'bg-blue-100 text-blue-700',
                                                'approved' => 'bg-emerald-100 text-emerald-700',
                                                'rejected' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                            $lLabel = match ($lStatus) {
                                                'pending_atasan' => 'Laporan: Menunggu Atasan',
                                                'pending_admin' => 'Laporan: Menunggu HR',
                                                'approved' => 'Laporan: Disetujui',
                                                'rejected' => 'Laporan: Ditolak',
                                                default => 'Laporan: ' . $lStatus,
                                            };
                                        @endphp
                                        <span
                                            class="ml-1 inline-flex items-center rounded-full border text-[10px] px-2 py-0.5 {{ $lBadge }}">{{ $lLabel }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0 ml-2">
                                    @if ($w->status === 'approved' && empty($w->laporan_deskripsi))
                                        <a href="/wfh/{{ $w->id }}/laporan"
                                            class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] font-semibold btn-laporan"
                                            data-jam-in="{{ $presensihariini->jam_in ?? '' }}"
                                            data-tgl-wfh="{{ date('Y-m-d', strtotime($w->tgl_wfh)) }}">Upload
                                            Laporan</a>
                                    @else
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Alert H-1 WFH --}}
        @if (isset($wfhBesok) && $wfhBesok)
            <div id="alertH1" class="bg-sky-50 border border-sky-200 rounded-2xl p-3 mt-6 flex gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500 flex items-center justify-center text-white shrink-0"><i
                        data-lucide="alarm-clock" style="width:18px;height:18px;"></i></div>
                <div class="flex-1">
                    <div class="text-[13px] font-bold text-sky-900">WFH Besok
                        ({{ date('d M Y', strtotime($wfhBesok->tgl_wfh)) }}) Sudah Disetujui</div>
                    <div class="text-[11px] text-sky-700">Jangan lupa absen sesuai jam kerja @if ($jamMasuk)
                            <b>{{ $jamMasuk }}</b>
                        @endif. Alert akan muncul 10 menit sebelum jam masuk.</div>
                    <div class="text-[11px] text-sky-600 mt-1" id="countdownH1"></div>
                </div>
            </div>
        @endif

        {{-- HISTORI PRESENSI --}}
        <div class="mt-2">
            <div class="tab-pane fade show active" id="pilled" role="tabpanel">
                <ul class="nav nav-tabs style1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                            Bulan Ini
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content mt-2 mb-24">
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <ul class="listview image-listview" id="histori-list">
                        @foreach ($historibulanini as $d)
                            @php
                                $path = Storage::url('uploads/absensi/' . $d->foto_in);
                            @endphp
                            <li>
                                <div class="item">
                                    <img src="{{ url($path) }}?v={{ time() }}" alt=""
                                        class="w-[35px] h-[35px] rounded-[10px] object-cover mr-3 border-2 border-white shadow-sm foto-histori-dashboard flex-shrink-0">
                                    <div class="in flex-wrap gap-1">
                                        <div class="w-full text-[13px]">
                                            {{ date('d-m-Y', strtotime($d->tgl_presensi)) }}
                                        </div>
                                        <span
                                            class="inline-flex items-center justify-center rounded-full text-white text-[10px] sm:text-xs px-2 py-0.5 {{ $d->terlambat > 0 ? 'bg-red-500' : 'bg-green-500' }}">
                                            {{ $d->jam_in }}
                                        </span>
                                        <span
                                            class="inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] sm:text-xs px-2 py-0.5">
                                            {{ $d->jam_out != null ? $d->jam_out : 'Belum Presensi' }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

    </div>



    {{-- PREVIEW FOTO --}}
    <script>
        document.querySelectorAll('.foto-presensi, .foto-histori-dashboard').forEach(function(foto) {
            foto.addEventListener('click', function() {
                Swal.fire({
                    html: `<img src="${this.src}" style="width:100%;height:100%;border-radius:12px;display:block;">`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '390px',
                    padding: '10px',
                    background: 'transparent'
                });
            });
        });
    </script>

    {{-- COUNTDOWN ALERT UPLOAD LAPORAN --}}
    <script>
        var SERVER_TODAY = '{{ now("Asia/Jakarta")->format("Y-m-d") }}';
        var SERVER_TIME = '{{ now("Asia/Jakarta")->format("H:i:s") }}';
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-laporan');
            if (!btn) return;

            var tglWfh = btn.getAttribute('data-tgl-wfh');
            var jamIn = btn.getAttribute('data-jam-in');
            var todayStr = SERVER_TODAY;

            if (tglWfh && tglWfh !== todayStr) {
                e.preventDefault();
                var partsWfh = tglWfh.split('-');
                var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ];
                var label = parseInt(partsWfh[2]) + ' ' + months[parseInt(partsWfh[1]) - 1] + ' ' + partsWfh[0];
                var partsToday = todayStr.split('-');
                var labelToday = parseInt(partsToday[2]) + ' ' + months[parseInt(partsToday[1]) - 1] + ' ' + partsToday[0];
                Swal.fire({
                    title: 'Tanggal Belum Sampai',
                    html: '<div style="text-align:left">' +
                        '<b>Laporan WFH</b> hanya bisa diupload pada <b>tanggal WFH</b> yang diajukan.' +
                        '<br><br>' +
                        '<table style="margin:0 auto;font-size:13px">' +
                        '<tr><td style="padding:2px 12px 2px 0;color:#78716c">Hari ini</td><td><b>' + labelToday + '</b></td></tr>' +
                        '<tr><td style="padding:2px 12px 2px 0;color:#78716c">Tanggal WFH</td><td><b>' + label + '</b></td></tr>' +
                        '</table>' +
                        '<br>Silakan upload laporan pada tanggal <b>' + label + '</b>.' +
                        '</div>',
                    icon: 'info',
                    confirmButtonColor: '#7a5344',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            if (!jamIn) {
                e.preventDefault();
                Swal.fire({
                    title: 'Belum Absen Masuk',
                    html: '<div style="text-align:left">' +
                        'Anda harus <b>absen masuk</b> terlebih dahulu sebelum bisa upload laporan WFH.' +
                        '<br><br>Alur upload laporan:' +
                        '<ol style="margin:8px 0 0 18px;text-align:left">' +
                        '<li>1. Absen masuk di hari WFH</li>' +
                        '<li>2. Tunggu minimal <b>7 jam</b> setelah absen masuk</li>' +
                        '<li>3. Baru bisa upload laporan</li>' +
                        '<li>4. Setelah upload laporan, bisa absen pulang</li>' +
                        '</ol>' +
                        '</div>',
                    icon: 'warning',
                    confirmButtonColor: '#7a5344',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            // Hitung selisih pakai SERVER_TIME, bukan new Date()
            var partsJamIn = jamIn.split(':');
            var partsServer = SERVER_TIME.split(':');
            var jamInDetik = parseInt(partsJamIn[0]) * 3600 + parseInt(partsJamIn[1]) * 60 + (parseInt(partsJamIn[2]) || 0);
            var serverDetik = parseInt(partsServer[0]) * 3600 + parseInt(partsServer[1]) * 60 + (parseInt(partsServer[2]) || 0);
            var selisihDetik = serverDetik - jamInDetik;
            var selisihJam = selisihDetik / 3600;
            if (selisihJam < 7) {
                e.preventDefault();
                var sisaDetik = Math.ceil(7 * 3600 - selisihDetik);
                var jam = Math.floor(sisaDetik / 3600);
                var menit = Math.floor((sisaDetik % 3600) / 60);
                var detik = sisaDetik % 60;
                var sisaWaktuStr = '';
                if (jam > 0) sisaWaktuStr += jam + ' jam ';
                if (menit > 0) sisaWaktuStr += menit + ' menit ';
                if (detik > 0 || sisaWaktuStr === '') sisaWaktuStr += detik + ' detik';
                Swal.fire({
                    title: 'Belum Bisa Upload Laporan',
                    html: '<div style="text-align:left">' +
                        'Laporan WFH hanya bisa diupload setelah <b>7 jam</b> absen masuk.' +
                        '<br><br><b>Sisa waktu: <span id="sisaWaktu">' + sisaWaktuStr.trim() + '</span></b>' +
                        '<br><br>Silakan tunggu hingga waktu yang tersisa habis.' +
                        '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#7a5234',
                    allowOutsideClick: false,
                    didOpen: function() {
                        var remaining = sisaDetik;
                        countdownInterval = setInterval(function() {
                            remaining--;
                            if (remaining <= 0) {
                                clearInterval(countdownInterval);
                                Swal.close();
                                return;
                            }
                            var h = Math.floor(remaining / 3600);
                            var m = Math.floor((remaining % 3600) / 60);
                            var s = remaining % 60;
                            var txt = '';
                            if (h > 0) txt += h + ' jam ';
                            if (m > 0) txt += m + ' menit ';
                            if (s > 0 || txt === '') txt += s + ' detik';
                            var el = document.getElementById('sisaWaktu');
                            if (el) el.textContent = txt.trim();
                        }, 1000);
                    },
                    willClose: function() {
                        clearInterval(countdownInterval);
                    }
                });
            }
        });
    </script>

    {{-- NOTIFIKASI & REALTIME POLLING + WEB PUSH + ALERT H-1 --}}
    <script>
        (function() {
            const CSRF = '{{ csrf_token() }}';
            let notifAudio = null;
            try {
                notifAudio = new Audio();
            } catch (e) {}

            // Request notification permission (cek preference dulu)
            if ('Notification' in window && Notification.permission === 'default') {
                fetch('/api/user/permissions', {
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(perms => {
                        if (perms.notifications) {
                            setTimeout(() => Notification.requestPermission(), 2000);
                        }
                    }).catch(() => {});
            }

            // === NOTIFICATION DROPDOWN ===
            const btn = document.getElementById('btnNotif');
            const dropdown = document.getElementById('notifDropdown');
            const closeBtn = document.getElementById('closeNotif');
            const badge = document.getElementById('notifBadge');
            if (btn && dropdown) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });
                if (closeBtn) closeBtn.addEventListener('click', () => dropdown.classList.add('hidden'));
                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && e.target !== btn) dropdown.classList.add('hidden');
                });
            }

            // Mark all as read when clicking bell
            if (btn) {
                btn.addEventListener('click', () => {
                    fetch('/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(() => {
                        if (badge) {
                            badge.classList.add('hidden');
                            badge.classList.remove('flex');
                        }
                        setTimeout(() => pollRealtime(), 500);
                    });
                });
            }

            // === REALTIME POLLING — SETIAP 5 DETIK ===
            let lastNotifCount = {{ count($notifications ?? []) }};
            let lastPendingAtasan = {{ $pendingAtasan->count() ?? 0 }};
            let lastPendingLaporan = {{ $pendingLaporanAtasan->count() ?? 0 }};
            let isPolling = false;
            let sectionHashes = {};
            let pollInterval = 5000;
            let pollTimer = null;

            function updateSection(el, newHtml, key) {
                if (!el) return;
                const newHash = newHtml.length + ':' + newHtml.substring(0, 200);
                if (sectionHashes[key] === newHash) return;
                sectionHashes[key] = newHash;
                el.innerHTML = newHtml;
                if (window.lucide) lucide.createIcons();
            }

            function esc(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            function pollRealtime() {
                if (isPolling) return;
                isPolling = true;
                fetch('/api/realtime/dashboard', {
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        // 1. Update notifikasi badge
                        if (badge) {
                            const unread = data.unread_count || 0;
                            if (unread > 0) {
                                badge.textContent = unread;
                                badge.classList.remove('hidden');
                                badge.classList.add('flex');
                            } else {
                                badge.classList.add('hidden');
                                badge.classList.remove('flex');
                            }
                        }

                        // 2. Web Push + Sound untuk notifikasi baru
                        if (data.notifications && data.notifications.length > lastNotifCount) {
                            const newNotifs = data.notifications.slice(0, data.notifications.length -
                                lastNotifCount);
                            newNotifs.forEach(n => {
                                if (n.data && n.data.message) {
                                    if (Notification.permission === 'granted') {
                                        new Notification('Presensi Digital', {
                                            body: n.data.message,
                                            icon: '/assets/img/login/logo_aplikasi.png',
                                            tag: 'presensi-' + n.id
                                        });
                                    }
                                }
                            });
                            lastNotifCount = data.notifications.length;
                        }

                        // 3. Update notif dropdown list
                        const list = document.getElementById('notifList');
                        if (list && data.notifications && data.notifications.length > 0) {
                            const notifHtml = data.notifications.map(n => {
                                const isUnread = !n.read_at;
                                return '<div class="p-3 hover:bg-[#fdf8f4] ' + (isUnread ?
                                        'bg-amber-50/50' : '') + '">' +
                                    '<div class="text-[12px] font-medium text-[#1c1917]">' + esc(n.data
                                        .message || 'Notifikasi') + '</div>' +
                                    '<div class="text-[11px] text-[#a8a29e] mt-1">' + esc(n.created_at) +
                                    '</div>' +
                                    '</div>';
                            }).join('');
                            updateSection(list, notifHtml, 'notifList');
                        } else if (list) {
                            updateSection(list,
                                '<div class="p-6 text-center text-[12px] text-[#a8a29e]">Tidak ada notifikasi</div>',
                                'notifList');
                        }

                        // 4. Update pending atasan + re-render cards
                        if (data.pendingAtasan) {
                            const count = data.pendingAtasan.length;
                            const section = document.getElementById('pendingAtasanSection');
                            if (section) {
                                if (count === 0) {
                                    updateSection(section, '', 'pendingAtasan');
                                } else {
                                    let html =
                                        '<div class="mt-6"><div class="flex items-center justify-between mb-2"><h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2"><span class="w-8 h-8 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700"><i data-lucide="shield-check"></i></span>Pengajuan Perlu Persetujuan (' +
                                        count + ')</h3></div>';
                                    data.pendingAtasan.forEach(function(p) {
                                        var k = p.karyawan || {};
                                        var up = k.unitperusahaan || {};
                                        html +=
                                            '<div class="card mb-2 border-l-4 border-l-amber-400 bg-amber-50/50"><div class="card-body p-3"><div class="flex items-start justify-between gap-3"><div class="flex-1 min-w-0"><div class="text-[13px] font-bold text-[#1c1917]">' +
                                            esc(k.nama_lengkap || '-') +
                                            ' <span class="text-[11px] font-normal text-[#78716c]">• ' + esc(k
                                                .jabatan || '-') + ' • ' + esc(k.posisi || '-') +
                                            '</span></div><div class="text-[11px] text-[#78716c]">' + esc((p
                                                .tgl_wfh || '').substring(0, 10)) + ' • ' + esc(k.unit || '-') + ' (' + esc(up
                                                .perusahaan || '-') +
                                            ')</div><div class="text-[11px] text-[#57534e] mt-1 line-clamp-2">' +
                                            esc((p.deskripsi_pekerjaan || '').substring(0, 70)) + '</div>' +
                                            '</div><div class="flex flex-col gap-1.5 shrink-0"><button type="button" class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] w-full btn-approve-atasan" data-id="' +
                                            p.id + '">Setujui</button><button type="button" class="btn btn-sm bg-white border border-rose-200 text-rose-700 rounded-full px-3 py-1 text-[11px] w-full btn-reject-atasan-dynamic" data-id="' +
                                            p.id + '">Tolak</button></div></div>' + (p.pdf_form_path ?
                                                '<div class="flex"><button type="button" class="text-[11px] text-sky-700 hover:underline cursor-pointer" onclick="window.open(\'/storage/' +
                                                esc(p.pdf_form_path) +
                                                '\',\'_blank\')">Form Pengajuan</button></div>' : '') +
                                            '</div></div>';
                                    });
                                    html += '</div>';
                                    updateSection(section, html, 'pendingAtasan');
                                }
                            }
                            if (count > lastPendingAtasan && count > 0) {
                                if (Notification.permission === 'granted') {
                                    new Notification('Persetujuan WFH', {
                                        body: 'Ada ' + count + ' WFH menunggu persetujuan Anda',
                                        icon: '/assets/img/login/logo_aplikasi.png'
                                    });
                                }
                            }
                            lastPendingAtasan = count;
                        }

                        // 5. Update pending laporan + re-render cards
                        if (data.pendingLaporanAtasan) {
                            const count = data.pendingLaporanAtasan.length;
                            const section = document.getElementById('pendingLaporanSection');
                            if (section) {
                                if (count === 0) {
                                    updateSection(section, '', 'pendingLaporan');
                                } else {
                                    let html =
                                        '<div class="mt-6"><div class="flex items-center justify-between mb-2"><h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2"><span class="w-8 h-8 rounded-xl bg-violet-100 border border-violet-200 flex items-center justify-center text-violet-700"><i data-lucide="file-text"></i></span>Laporan Perlu Persetujuan (' +
                                        count + ')</h3></div>';
                                    data.pendingLaporanAtasan.forEach(function(p) {
                                        var k = p.karyawan || {};
                                        var up = k.unitperusahaan || {};
                                        var previewBtn = '';
                                        if (p.laporan_file) {
                                            var laporanUrl = '/storage/' + p.laporan_file;
                                            previewBtn =
                                                '<div class="mt-1"><a href="' + esc(laporanUrl) + '" target="_blank" class="text-[11px] text-sky-700 hover:underline">Form Laporan</a></div>';
                                        } else if (p.laporan_deskripsi) {
                                            previewBtn =
                                                '<div class="mt-1"><button type="button" class="text-[11px] text-sky-700 hover:underline cursor-pointer js-preview-laporan" data-deskripsi="' +
                                                esc(p.laporan_deskripsi || '') +
                                                '" data-tgl="' + esc((p.tgl_wfh || '').substring(0, 10)) +
                                                '" data-label="Laporan WFH — ' + esc(k.nama_lengkap || '-') +
                                                '">Form Laporan</button></div>';
                                        }
                                        html +=
                                            '<div class="card mb-2 border-l-4 border-l-violet-400 bg-violet-50/50"><div class="card-body p-3"><div class="flex items-start justify-between gap-3"><div class="flex-1 min-w-0"><div class="text-[13px] font-bold text-[#1c1917]">' +
                                            esc(k.nama_lengkap || '-') +
                                            ' <span class="text-[11px] font-normal text-[#78716c]">• ' + esc(k
                                                .jabatan || '-') + ' • ' + esc(k.posisi || '-') +
                                            '</span></div><div class="text-[11px] text-[#78716c]">' + esc((p
                                                .tgl_wfh || '').substring(0, 10)) + ' • ' + esc(k.unit || '-') + ' (' + esc(up
                                                .perusahaan || '-') +
                                            ')</div><div class="text-[11px] text-[#57534e] mt-1">Laporan WFH menunggu persetujuan Anda</div>' +
                                            previewBtn +
                                            '</div><div class="flex flex-col gap-1.5 shrink-0"><button type="button" class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] w-full btn-approve-laporan-atasan" data-id="' +
                                            p.id + '">Setujui</button><button type="button" class="btn btn-sm bg-white border border-rose-200 text-rose-700 rounded-full px-3 py-1 text-[11px] w-full btn-reject-laporan-atasan-dynamic" data-id="' +
                                            p.id + '">Tolak</button></div></div></div></div>';
                                    });
                                    html += '</div>';
                                    updateSection(section, html, 'pendingLaporan');
                                }
                            }
                            if (count > lastPendingLaporan && count > 0) {
                                if (Notification.permission === 'granted') {
                                    new Notification('Persetujuan Laporan WFH', {
                                        body: 'Ada ' + count + ' laporan WFH menunggu persetujuan Anda',
                                        icon: '/assets/img/login/logo_aplikasi.png'
                                    });
                                }
                            }
                            lastPendingLaporan = count;
                        }

                        // 6. Update presensi jam in/out + foto
                        if (data.presensi) {
                            var p = data.presensi;
                            var jamInEl = document.getElementById('presensi-jam-in');
                            var jamOutEl = document.getElementById('presensi-jam-out');
                            if (jamInEl) jamInEl.textContent = p.jam_in || 'Belum Presensi';
                            if (jamOutEl) jamOutEl.textContent = p.jam_out || 'Belum Presensi';

                            var fotoInWrap = document.getElementById('presensi-foto-in-wrap');
                            if (p.foto_in && fotoInWrap) {
                                var existingFotoIn = document.getElementById('presensi-foto-in');
                                if (!existingFotoIn) {
                                    var placeholder = document.getElementById('presensi-foto-in-placeholder');
                                    if (placeholder) placeholder.remove();
                                    var img = document.createElement('img');
                                    img.id = 'presensi-foto-in';
                                    img.alt = '';
                                    img.className = 'w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] object-cover rounded-xl';
                                    img.src = '/storage/uploads/absensi/' + p.foto_in + '?v=' + Date.now();
                                    fotoInWrap.appendChild(img);
                                } else if (existingFotoIn.src.indexOf(p.foto_in) === -1) {
                                    existingFotoIn.src = '/storage/uploads/absensi/' + p.foto_in + '?v=' + Date.now();
                                }
                            }

                            var fotoOutWrap = document.getElementById('presensi-foto-out-wrap');
                            if (p.foto_out && fotoOutWrap) {
                                var existingFotoOut = document.getElementById('presensi-foto-out');
                                if (!existingFotoOut) {
                                    var placeholderOut = document.getElementById('presensi-foto-out-placeholder');
                                    if (placeholderOut) placeholderOut.remove();
                                    var imgOut = document.createElement('img');
                                    imgOut.id = 'presensi-foto-out';
                                    imgOut.alt = '';
                                    imgOut.className = 'w-[44px] h-[44px] sm:w-[50px] sm:h-[50px] object-cover rounded-xl';
                                    imgOut.src = '/storage/uploads/absensi/' + p.foto_out + '?v=' + Date.now();
                                    fotoOutWrap.appendChild(imgOut);
                                } else if (existingFotoOut.src.indexOf(p.foto_out) === -1) {
                                    existingFotoOut.src = '/storage/uploads/absensi/' + p.foto_out + '?v=' + Date.now();
                                }
                            }
                        }

                        // 6b. Update rekap counters
                        if (data.rekap) {
                            var r = data.rekap;
                            var hadirEl = document.getElementById('rekap-hadir');
                            if (r.hadir > 0) {
                                if (hadirEl) { hadirEl.textContent = r.hadir; }
                                else {
                                    var card = document.querySelector('#rekappresensi .w-1\\/2:first-child .card');
                                    if (card && !document.getElementById('rekap-hadir')) {
                                        var span = document.createElement('span');
                                        span.id = 'rekap-hadir';
                                        span.className = 'absolute bottom-0 left-0 bg-green-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg';
                                        span.textContent = r.hadir;
                                        card.appendChild(span);
                                    }
                                }
                            } else if (hadirEl) { hadirEl.remove(); }

                            var wfhEl = document.getElementById('rekap-wfh');
                            if (r.wfh > 0) {
                                if (wfhEl) { wfhEl.textContent = r.wfh; }
                                else {
                                    var cardWfh = document.querySelector('#rekappresensi .w-1\\/2:nth-child(2) .card');
                                    if (cardWfh && !document.getElementById('rekap-wfh')) {
                                        var spanWfh = document.createElement('span');
                                        spanWfh.id = 'rekap-wfh';
                                        spanWfh.className = 'absolute bottom-0 left-0 bg-blue-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg';
                                        spanWfh.textContent = r.wfh;
                                        cardWfh.appendChild(spanWfh);
                                    }
                                }
                            } else if (wfhEl) { wfhEl.remove(); }

                            var lemburEl = document.getElementById('rekap-lembur');
                            if (r.lembur > 0) {
                                if (lemburEl) { lemburEl.textContent = r.lembur; }
                                else {
                                    var cardLembur = document.querySelector('#rekappresensi .w-1\\/2:nth-child(3) .card');
                                    if (cardLembur && !document.getElementById('rekap-lembur')) {
                                        var spanLembur = document.createElement('span');
                                        spanLembur.id = 'rekap-lembur';
                                        spanLembur.className = 'absolute bottom-0 left-0 bg-yellow-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg';
                                        spanLembur.textContent = r.lembur;
                                        cardLembur.appendChild(spanLembur);
                                    }
                                }
                            } else if (lemburEl) { lemburEl.remove(); }

                            var izinEl = document.getElementById('rekap-izin');
                            if (r.izin > 0) {
                                if (izinEl) { izinEl.textContent = r.izin; }
                                else {
                                    var cardIzin = document.querySelector('#rekappresensi .w-1\\/2:nth-child(4) .card');
                                    if (cardIzin && !document.getElementById('rekap-izin')) {
                                        var spanIzin = document.createElement('span');
                                        spanIzin.id = 'rekap-izin';
                                        spanIzin.className = 'absolute bottom-0 left-0 bg-red-500/90 text-white text-base font-bold px-2.5 py-0.5 rounded-tr-lg';
                                        spanIzin.textContent = r.izin;
                                        cardIzin.appendChild(spanIzin);
                                    }
                                }
                            } else if (izinEl) { izinEl.remove(); }
                        }

                        // 6c. Update histori list
                        if (data.histori && data.histori.length > 0) {
                            var historiList = document.getElementById('histori-list');
                            if (historiList) {
                                var hHtml = '';
                                data.histori.forEach(function(d) {
                                    var tgl = (d.tgl_presensi || '').substring(0, 10);
                                    var tglParts = tgl.split('-');
                                    var tglFormatted = tglParts[2] + '-' + tglParts[1] + '-' + tglParts[0];
                                    var terlambat = d.terlambat > 0;
                                    hHtml += '<li><div class="item">' +
                                        '<img src="/storage/uploads/absensi/' + esc(d.foto_in || '') + '?v=' + Date.now() + '" alt="" ' +
                                        'class="w-[35px] h-[35px] rounded-[10px] object-cover mr-3 border-2 border-white shadow-sm foto-histori-dashboard flex-shrink-0">' +
                                        '<div class="in flex-wrap gap-1">' +
                                        '<div class="w-full text-[13px]">' + tglFormatted + '</div>' +
                                        '<span class="inline-flex items-center justify-center rounded-full text-white text-[10px] sm:text-xs px-2 py-0.5 ' + (terlambat ? 'bg-red-500' : 'bg-green-500') + '">' + esc(d.jam_in) + '</span>' +
                                        '<span class="inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] sm:text-xs px-2 py-0.5">' + esc(d.jam_out || 'Belum Presensi') + '</span>' +
                                        '</div></div></li>';
                                });
                                historiList.innerHTML = hHtml;
                                historiList.querySelectorAll('.foto-histori-dashboard').forEach(function(foto) {
                                    foto.addEventListener('click', function() {
                                        Swal.fire({
                                            html: '<img src="' + this.src + '" style="width:100%;height:100%;border-radius:12px;display:block;">',
                                            showConfirmButton: false,
                                            showCloseButton: true,
                                            width: '390px',
                                            padding: '10px',
                                            background: 'transparent'
                                        });
                                    });
                                });
                            }
                        }

                        // 7. Update Perlu Tindakan (WFH Saya)
                        if (data.wfhSaya) {
                            const section = document.getElementById('perluTindakanSection');
                            if (section) {
                                if (data.wfhSaya.length === 0) {
                                    section.innerHTML = '';
                                } else {
                                    let badgeMap = {
                                        'pending_atasan': ['bg-amber-100 text-amber-700 border-amber-200',
                                            'Menunggu Persetujuan'
                                        ],
                                        'pending_admin': ['bg-amber-100 text-amber-700 border-amber-200',
                                            'Menunggu Persetujuan HR'
                                        ],
                                        'approved': ['bg-emerald-100 text-emerald-700 border-emerald-200',
                                            ''
                                        ],
                                        'rejected': ['bg-rose-100 text-rose-700 border-rose-200', 'Ditolak'],
                                        'unpaid': ['bg-gray-100 text-gray-700 border-gray-200', 'Unpaid']
                                    };
                                    let lBadgeMap = {
                                        'pending_atasan': 'bg-amber-100 text-amber-700',
                                        'pending_admin': 'bg-blue-100 text-blue-700',
                                        'approved': 'bg-emerald-100 text-emerald-700',
                                        'rejected': 'bg-rose-100 text-rose-700'
                                    };
                                    let lLabelMap = {
                                        'pending_atasan': 'Laporan: Menunggu Atasan',
                                        'pending_admin': 'Laporan: Menunggu HR',
                                        'approved': 'Laporan: Disetujui',
                                        'rejected': 'Laporan: Ditolak'
                                    };
                                    let html =
                                        '<div class="mt-6"><div class="flex items-center justify-between mb-2"><h3 class="text-[15px] font-bold text-[#1c1917] flex items-center gap-2"><span class="w-8 h-8 rounded-xl bg-sky-100 border border-sky-200 flex items-center justify-center text-sky-700"><i data-lucide="home"></i></span>Perlu Tindakan</h3><a href="/wfh" class="text-[11px] font-semibold text-sky-700">Lihat Semua</a></div>';
                                    data.wfhSaya.forEach(function(w) {
                                        var b = badgeMap[w.status] || [
                                            'bg-gray-100 text-gray-700 border-gray-200', w.status
                                        ];
                                        if (w.status === 'approved' && !w.laporan_deskripsi) {
                                            b = [b[0], 'Menunggu Laporan'];
                                        } else if (w.status === 'approved') {
                                            b = [b[0], 'Disetujui'];
                                        }
                                        var tglParts = (w.tgl_wfh || '').substring(0, 10).split('-');
                                        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                        var dateStr = parseInt(tglParts[2]) + ' ' + (months[parseInt(tglParts[1]) - 1] || '') + ' ' + tglParts[0];
                                        var keterangan = w.keterangan ?
                                            '<div class="text-[11px] text-[#78716c] mt-0.5 italic">' + esc((w
                                                .keterangan.length > 50 ? w.keterangan.substring(0, 50) +
                                                '...' : w.keterangan)) + '</div>' : '';
                                        var deskripsi = w.deskripsi_pekerjaan ?
                                            '<div class="text-[11px] text-[#78716c] mt-0.5">' + esc((w
                                                .deskripsi_pekerjaan.length > 50 ? w.deskripsi_pekerjaan
                                                .substring(0, 50) + '...' : w.deskripsi_pekerjaan)) +
                                            '</div>' : '';
                                        var laporanBadge = '';
                                        if (w.laporan_status) {
                                            var lb = lBadgeMap[w.laporan_status] ||
                                                'bg-gray-100 text-gray-700';
                                            var ll = lLabelMap[w.laporan_status] || 'Laporan: ' + w
                                                .laporan_status;
                                            laporanBadge =
                                                '<span class="ml-1 inline-flex items-center rounded-full border text-[10px] px-2 py-0.5 ' +
                                                lb + '">' + esc(ll) + '</span>';
                                        }
                                        var actionBtn = '';
                                        if (w.status === 'approved' && !w.laporan_deskripsi) {
                                            actionBtn = '<a href="/wfh/' + w.id +
                                                '/laporan" class="btn btn-sm bg-emerald-500 text-white rounded-full px-3 py-1 text-[11px] font-semibold btn-laporan" data-jam-in="' +
                                                esc(data.presensi && data.presensi.jam_in ? data.presensi
                                                    .jam_in : '') + '" data-tgl-wfh="' + esc((w.tgl_wfh || '').substring(0, 10)) +
                                                '">Upload Laporan</a>';
                                        }
                                        html +=
                                            '<div class="card mb-2"><div class="card-body p-3 flex items-center justify-between"><div class="flex-1 min-w-0"><div class="text-[13px] font-bold text-[#1c1917]">' +
                                            dateStr +
                                            ' <span class="ml-1 inline-flex items-center rounded-full border text-[10px] px-2 py-0.5 ' +
                                            b[0] + '">' + esc(b[1]) + '</span></div>' + keterangan + deskripsi +
                                            laporanBadge +
                                            '</div><div class="flex items-center gap-2 shrink-0 ml-2">' +
                                            actionBtn + '</div></div></div>';
                                    });
                                    html += '</div>';
                                    updateSection(section, html, 'wfhSaya');
                                }
                            }
                        }

                        isPolling = false;
                        pollInterval = 5000;
                    }).catch(() => {
                        isPolling = false;
                        pollInterval = Math.min(pollInterval * 2, 30000);
                    });
            }

            // Poll dengan backoff
            function startPoll() {
                setTimeout(function() {
                    pollRealtime();
                    startPoll();
                }, pollInterval);
            }
            pollRealtime();
            startPoll();

            // Trigger poll segera saat user kembali ke tab (iOS PWA fix)
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    pollRealtime();
                }
            });

            // === EVENT DELEGATION: Approve + Reject buttons (Blade & realtime cards) ===
            document.addEventListener('click', function(e) {
                // Approve WFH Atasan
                var btnApprove = e.target.closest('.btn-approve-atasan');
                if (btnApprove) {
                    e.preventDefault();
                    var id = btnApprove.dataset.id;
                    Swal.fire({
                        title: 'Setujui WFH?',
                        text: 'Pengajuan akan diteruskan ke HR.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal'
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            btnApprove.disabled = true;
                            btnApprove.textContent = 'Memproses...';
                            fetch('/wfh/' + id + '/approve-atasan', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            }).then(function(resp) { return resp.json(); }).then(function(data) {
                                if (data.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                                    setTimeout(function() { pollRealtime(); }, 300);
                                } else {
                                    btnApprove.disabled = false;
                                    btnApprove.textContent = 'Setujui';
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                                }
                            }).catch(function() {
                                btnApprove.disabled = false;
                                btnApprove.textContent = 'Setujui';
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                            });
                        }
                    });
                    return;
                }

                // Approve Laporan Atasan
                var btnApproveLaporan = e.target.closest('.btn-approve-laporan-atasan');
                if (btnApproveLaporan) {
                    e.preventDefault();
                    var idLap = btnApproveLaporan.dataset.id;
                    Swal.fire({
                        title: 'Setujui Laporan?',
                        text: 'Laporan WFH akan diteruskan ke Admin.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal'
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            btnApproveLaporan.disabled = true;
                            btnApproveLaporan.textContent = 'Memproses...';
                            fetch('/wfh/' + idLap + '/approve-laporan-atasan', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            }).then(function(resp) { return resp.json(); }).then(function(data) {
                                if (data.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                                    setTimeout(function() { pollRealtime(); }, 300);
                                } else {
                                    btnApproveLaporan.disabled = false;
                                    btnApproveLaporan.textContent = 'Setujui';
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                                }
                            }).catch(function() {
                                btnApproveLaporan.disabled = false;
                                btnApproveLaporan.textContent = 'Setujui';
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                            });
                        }
                    });
                    return;
                }

                // Reject WFH Atasan
                var btn = e.target.closest('.btn-reject-atasan-dynamic');
                if (btn) {
                    e.preventDefault();
                    var id = btn.dataset.id;
                    Swal.fire({
                        title: 'Tolak WFH?',
                        input: 'textarea',
                        inputPlaceholder: 'Alasan penolakan...',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Tolak',
                        inputValidator: function(v) {
                            if (!v || v.trim().length < 5) return 'Minimal 5 karakter';
                        }
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            btn.disabled = true;
                            btn.textContent = 'Memproses...';
                            fetch('/wfh/' + id + '/reject-atasan', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                credentials: 'same-origin',
                                body: JSON.stringify({ rejected_reason: r.value })
                            }).then(function(resp) { return resp.json(); }).then(function(data) {
                                if (data.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                                    setTimeout(function() { pollRealtime(); }, 300);
                                } else {
                                    btn.disabled = false;
                                    btn.textContent = 'Tolak';
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                                }
                            }).catch(function() {
                                btn.disabled = false;
                                btn.textContent = 'Tolak';
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                            });
                        }
                    });
                }

                // Reject Laporan Atasan
                var btnL = e.target.closest('.btn-reject-laporan-atasan-dynamic');
                if (btnL) {
                    e.preventDefault();
                    var idL = btnL.dataset.id;
                    Swal.fire({
                        title: 'Tolak Laporan WFH?',
                        input: 'textarea',
                        inputPlaceholder: 'Alasan penolakan...',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Tolak',
                        inputValidator: function(v) {
                            if (!v || v.trim().length < 5) return 'Minimal 5 karakter';
                        }
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            btnL.disabled = true;
                            btnL.textContent = 'Memproses...';
                            fetch('/wfh/' + idL + '/reject-laporan-atasan', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                                credentials: 'same-origin',
                                body: JSON.stringify({ rejected_reason: r.value })
                            }).then(function(resp) { return resp.json(); }).then(function(data) {
                                if (data.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                                    setTimeout(function() { pollRealtime(); }, 300);
                                } else {
                                    btnL.disabled = false;
                                    btnL.textContent = 'Tolak';
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                                }
                            }).catch(function() {
                                btnL.disabled = false;
                                btnL.textContent = 'Tolak';
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                            });
                        }
                    });
                }
            });

            // === ALERT H-1 : 10 menit sebelum jam masuk ===
            @if (isset($wfhBesok) && $wfhBesok)
                (function() {
                    var countdownEl = document.getElementById('countdownH1');
                    @if (isset($jamMasuk) && $jamMasuk)
                        var jamMasuk = "{{ $jamMasuk }}";
                        var tglBesok = "{{ date('Y-m-d', strtotime($wfhBesok->tgl_wfh)) }}";

                        var SERVER_EPOCH_MS = {{ now('Asia/Jakarta')->timestamp * 1000 }};
                        var CLIENT_LOAD_MS = Date.now();

                        function serverNowMs() {
                            return SERVER_EPOCH_MS + (Date.now() - CLIENT_LOAD_MS);
                        }

                        function checkH1() {
                            var nowMs = serverNowMs();
                            var jamParts = jamMasuk.split(':');
                            var tglParts = tglBesok.split('-');
                            var targetMs = new Date(parseInt(tglParts[0]), parseInt(tglParts[1]) - 1, parseInt(tglParts[2]), parseInt(jamParts[0]), parseInt(jamParts[1]), parseInt(jamParts[2] || 0)).getTime();
                            var alertMs = targetMs - 10 * 60 * 1000;
                            var diff = alertMs - nowMs;
                            if (countdownEl) {
                                if (diff > 0) {
                                    var hrs = Math.floor(diff / 3600000);
                                    var mins = Math.floor((diff % 3600000) / 60000);
                                    var secs = Math.floor((diff % 60000) / 1000);
                                    countdownEl.textContent = 'Alert dalam ' + hrs + 'j ' + mins + 'm ' + secs + 's';
                                } else if (diff > -600000) {
                                    countdownEl.textContent = 'Waktunya absen!';
                                    if (!window._h1AlertShown) {
                                        window._h1AlertShown = true;
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Pengingat Absen WFH',
                                            text: 'WFH besok sudah disetujui. Jangan lupa absen 10 menit sebelum jam masuk ({{ $jamMasuk }})!',
                                            confirmButtonColor: '#7a5234'
                                        });
                                        if (Notification.permission === 'granted') {
                                            new Notification('Pengingat Absen WFH Besok', {
                                                body: 'Jangan lupa absen 10 menit sebelum {{ $jamMasuk }}',
                                                icon: '/assets/img/login/logo_aplikasi.png'
                                            });
                                        }
                                    }
                                } else {
                                    countdownEl.textContent = 'Sudah melewati jam masuk.';
                                }
                            }
                        }
                        setInterval(checkH1, 1000);
                        checkH1();
                    @else
                        if (countdownEl) {
                            countdownEl.textContent = 'Jam masuk belum ditentukan.';
                        }
                    @endif
                })();
            @endif
        })();
    </script>

@endsection
