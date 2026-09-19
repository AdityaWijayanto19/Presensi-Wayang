@forelse ($datawfh as $d)
    @php
        $status = $d->status instanceof \App\Enums\WfhStatus ? $d->status->value : ($d->status ?? 'pending_atasan');
        $badgeClass = match ($status) {
            'pending_atasan' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'pending_admin' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'unpaid' => 'bg-slate-100 text-slate-600 border border-slate-200',
            default => 'bg-slate-100 text-slate-600 border border-slate-200',
        };
        $label = match ($status) {
            'pending_atasan' => 'Menunggu Atasan',
            'pending_admin' => 'Menunggu HR',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'unpaid' => 'Unpaid',
            default => $status,
        };
        $jabatanBadge = match($d->jabatan) {
            'Direktur' => 'bg-red-50 text-red-700 border border-red-200',
            'GM' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'Manager' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            'SPV' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'Staff' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Intern' => 'bg-violet-50 text-violet-700 border border-violet-200',
            default => 'bg-slate-50 text-slate-600 border border-slate-200',
        };
        $pdfUrl = !empty($d->pdf_form_path) ? Storage::url($d->pdf_form_path) : null;
        $laporanUrl = !empty($d->laporan_file) ? Storage::url($d->laporan_file) : null;
        $karyawanData = $d->karyawan;
        $atasanData = $d->atasan;
        $namaKaryawan = $karyawanData->nama_lengkap ?? '-';
        $jabatanKaryawan = $karyawanData->jabatan ?? '-';
        $posisiKaryawan = $karyawanData->posisi ?? '-';
        $unitKaryawan = $karyawanData->unit ?? '-';
        $perusahaanKaryawan = $karyawanData->unitperusahaan->perusahaan ?? '-';
        $atasanNama = $atasanData?->nama_lengkap ?? '—';
        $jabatanAtasan = $atasanData?->jabatan instanceof \App\Enums\Jabatan ? $atasanData?->jabatan->value : ($atasanData?->jabatan ?? '—');

        $lStatus = !empty($d->laporan_status) ? ($d->laporan_status instanceof \App\Enums\WfhStatus ? $d->laporan_status->value : $d->laporan_status) : null;
        $lBadgeClass = match ($lStatus) {
            'pending_atasan' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'pending_admin' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-slate-50 text-slate-600 border border-slate-200',
        };
        $lLabel = match ($lStatus) {
            'pending_atasan' => 'Menunggu Atasan',
            'pending_admin' => 'Menunggu HR',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $lStatus ? ('Lainnya') : null,
        };
    @endphp
    <tr class="hover:bg-slate-50/50 transition-colors">
        <td class="px-2 py-2 text-xs text-slate-500 whitespace-nowrap">{{ ($datawfh->currentPage() - 1) * $datawfh->perPage() + $loop->iteration }}</td>
        <td class="px-2 py-2 text-xs whitespace-nowrap">
            <span class="font-medium text-slate-700">{{ date('d M Y', strtotime($d->tgl_wfh)) }}</span>
        </td>
        <td class="px-2 py-2 text-xs">
            <div class="font-medium text-slate-800 leading-tight">{{ $namaKaryawan }}</div>
            <div class="text-slate-400 text-[10px] mt-0.5">{{ $d->nik }}</div>
        </td>
        <td class="px-2 py-2 text-xs">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $jabatanBadge }}">{{ $jabatanKaryawan }}</span>
            <div class="text-slate-500 text-[10px] mt-0.5">{{ $posisiKaryawan }}</div>
        </td>
        <td class="px-2 py-2 text-xs">
            <div class="text-slate-700">{{ $unitKaryawan }}</div>
            <div class="text-slate-400 text-[10px]">{{ $perusahaanKaryawan }}</div>
        </td>
        <td class="px-2 py-2 text-xs">
            <div class="text-slate-700">{{ $atasanNama }}</div>
            <div class="text-slate-400 text-[10px]">{{ $d->atasan_nik ? $jabatanAtasan : 'Langsung Admin' }}</div>
        </td>
        <td class="px-2 py-2 text-xs">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $badgeClass }}">{{ $label }}</span>
            @if ($status === 'pending_admin')
                @can('wfh-approve')
                    <div class="flex gap-1 mt-1.5">
                        <form action="/presensi/datawfh/{{ $d->id }}/approve" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                <i data-lucide="check" style="width:10px;height:10px;"></i> Setujui
                            </button>
                        </form>
                        <button type="button" class="btn-reject-admin inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500 text-white hover:bg-rose-600 transition-colors" data-id="{{ $d->id }}">
                            <i data-lucide="x" style="width:10px;height:10px;"></i> Tolak
                        </button>
                    </div>
                @endcan
            @endif
            @if ($status == 'rejected' && !empty($d->rejected_reason))
                <div class="text-rose-500 text-[10px] mt-1" title="{{ $d->rejected_reason }}">{{ Str::limit($d->rejected_reason, 30) }}</div>
            @endif
        </td>
        <td class="px-2 py-2 text-xs">
            @if ($lStatus)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $lBadgeClass }}">{{ $lLabel }}</span>
                @if ($lStatus === 'pending_admin')
                    @can('wfh-approve')
                        <div class="flex gap-1 mt-1.5">
                            <form action="/presensi/datawfh/{{ $d->id }}/approve-laporan-admin" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                    <i data-lucide="check" style="width:10px;height:10px;"></i> Setujui
                                </button>
                            </form>
                            <button type="button" class="btn-reject-laporan-admin inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500 text-white hover:bg-rose-600 transition-colors" data-id="{{ $d->id }}">
                                <i data-lucide="x" style="width:10px;height:10px;"></i> Tolak
                            </button>
                        </div>
                    @endcan
                @endif
                @if ($lStatus == 'rejected' && !empty($d->laporan_rejected_reason))
                    <div class="text-rose-500 text-[10px] mt-1" title="{{ $d->laporan_rejected_reason }}">{{ Str::limit($d->laporan_rejected_reason, 30) }}</div>
                @endif
            @else
                <span class="text-slate-400">—</span>
            @endif
        </td>
        <td class="px-2 py-2 text-xs whitespace-nowrap">
            <div x-data="{ open: false, posTop: 0, posLeft: 0 }"
                @keydown.escape.window="open = false"
                @click.outside="open = false">
                <button type="button" x-ref="trigger"
                    @click="open = !open; if(open) { $nextTick(() => { const r = $refs.trigger.getBoundingClientRect(); posTop = r.bottom + 4; posLeft = r.right - 160; }) }"
                    class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-slate-100 transition-colors text-slate-500 hover:text-slate-700">
                    <i data-lucide="ellipsis-vertical" style="width:14px;height:14px;"></i>
                </button>
                <div x-show="open" x-cloak x-ref="menu"
                    :style="`top: ${posTop}px; left: ${posLeft}px`"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="fixed z-[999] w-40 bg-white rounded-lg shadow-lg border border-slate-200 py-1">
                    <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2 detail-wfh-btn"
                        data-id="{{ $d->id }}"
                        data-nama="{{ $namaKaryawan }}"
                        data-nik="{{ $d->nik }}"
                        data-jabatan="{{ $jabatanKaryawan }}"
                        data-posisi="{{ $posisiKaryawan }}"
                        data-unit="{{ $unitKaryawan }}"
                        data-perusahaan="{{ $perusahaanKaryawan }}"
                        data-atasan="{{ $atasanNama }}"
                        data-jabatan-atasan="{{ $jabatanAtasan }}"
                        data-tgl-wfh="{{ $d->tgl_wfh instanceof \Carbon\Carbon ? $d->tgl_wfh->format('d M Y') : date('d M Y', strtotime($d->tgl_wfh)) }}"
                        data-tgl-wfh-raw="{{ $d->tgl_wfh instanceof \Carbon\Carbon ? $d->tgl_wfh->format('Y-m-d') : $d->tgl_wfh }}"
                        data-status="{{ $label }}"
                        data-status-key="{{ $status }}"
                        data-laporan-status="{{ $lLabel ?? '—' }}"
                        data-laporan-status-key="{{ $lStatus ?? '' }}"
                        data-deskripsi="{{ $d->deskripsi_pekerjaan ?? '' }}"
                        data-keterangan="{{ $d->keterangan ?? '' }}"
                        data-pdf-url="{{ $pdfUrl ?? '' }}"
                        data-laporan-url="{{ $laporanUrl ?? '' }}"
                        data-laporan-deskripsi="{{ $d->laporan_deskripsi ?? '' }}"
                        data-rejected-reason="{{ $d->rejected_reason ?? '' }}"
                        data-laporan-rejected-reason="{{ $d->laporan_rejected_reason ?? '' }}"
                        data-live-location="{{ $d->live_location ?? '' }}"
                        @click="open = false; window.dispatchEvent(new CustomEvent('open-modal-modal-detailwfh', { detail: { el: $el } }))">
                        <i data-lucide="eye" style="width:12px;height:12px;"></i> Detail
                    </button>
                    @can('presensi-edit')
                    <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2 edit-wfh"
                        data-id="{{ $d->id }}"
                        data-tgl_wfh="{{ $d->tgl_wfh instanceof \Carbon\Carbon ? $d->tgl_wfh->format('Y-m-d') : $d->tgl_wfh }}"
                        data-deskripsi="{{ $d->deskripsi_pekerjaan }}"
                        data-keterangan="{{ $d->keterangan }}"
                        data-status="{{ $status }}"
                        @click="open = false">
                        <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                    </button>
                    @endcan
                    @can('wfh-delete')
                    <form action="/presensi/datawfh/{{ $d->id }}/delete" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2 delete-confirm">
                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i> Hapus
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-2 py-8 text-center text-xs text-slate-400">
            <div class="flex flex-col items-center gap-1">
                <i data-lucide="inbox" style="width:24px;height:24px;" class="text-slate-300"></i>
                <span>Data WFH tidak ditemukan</span>
            </div>
        </td>
    </tr>
@endforelse
