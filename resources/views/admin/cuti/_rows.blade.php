@forelse ($datacuti as $d)
    @php
        $tanggalList = is_array($d->tanggal_cuti) ? $d->tanggal_cuti : (json_decode($d->tanggal_cuti, true) ?: []);
        $tanggalJson = json_encode(array_values($tanggalList));
        $usedDatesOther = \App\Services\CutiService::getUsedDates($d->nik, $d->id);
        $usedDatesJson = json_encode($usedDatesOther);
        $tanggalDisplay = collect($tanggalList)
            ->map(fn ($t) => date('d M Y', strtotime($t)))
            ->implode(', ');
        $keteranganValue = $d->keterangan ?? '';
        $buktiUrl = !empty($d->bukti_file) ? Storage::disk('public')->url('uploads/cuti/' . $d->bukti_file) : null;
        $karyawanData = $d->karyawan;
        $namaKaryawan = $karyawanData->nama_lengkap ?? '-';
        $jabatanKaryawan = $karyawanData->jabatan ?? '-';
        $posisiKaryawan = $karyawanData->posisi ?? '-';
        $unitKaryawan = $karyawanData->unit ?? '-';
        $perusahaanKaryawan = $karyawanData->unitperusahaan->perusahaan ?? '-';
        $jabatanBadge = match ($jabatanKaryawan) {
            'Direktur' => 'bg-red-50 text-red-700 border border-red-200',
            'GM' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'Manager' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            'SPV' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'Staff' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Intern' => 'bg-violet-50 text-violet-700 border border-violet-200',
            default => 'bg-slate-50 text-slate-600 border border-slate-200',
        };
        $dikirim = $d->dikirim_tanggal instanceof \Carbon\Carbon
            ? $d->dikirim_tanggal->format('d M Y H:i')
            : date('d M Y H:i', strtotime($d->dikirim_tanggal));
    @endphp
    <tr class="hover:bg-slate-50/50 transition-colors">
        <td class="px-2 py-2 text-xs text-slate-500 whitespace-nowrap">{{ ($datacuti->currentPage() - 1) * $datacuti->perPage() + $loop->iteration }}</td>
        <td class="px-2 py-2 text-xs whitespace-nowrap">
            <span class="font-medium text-slate-700">{{ $tanggalDisplay ?: '-' }}</span>
            <div class="text-slate-400 text-[10px] mt-0.5">Upload: {{ $dikirim }}</div>
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
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $d->durasi_hari }} Hari</span>
        </td>
        <td class="px-2 py-2 text-xs text-slate-600 max-w-[160px]">
            {{ $keteranganValue ? Str::limit($keteranganValue, 40) : '—' }}
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
                    @if ($buktiUrl)
                    <a href="{{ $buktiUrl }}" target="_blank" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2" @click="open = false">
                        <i data-lucide="paperclip" style="width:12px;height:12px;"></i> File Cuti
                    </a>
                    @endif
                    @can('cuti-edit')
                    <button type="button" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2 edit-cuti"
                        data-id="{{ $d->id }}"
                        data-durasi="{{ $d->durasi_hari }}"
                        data-tanggal="{{ $tanggalJson }}"
                        data-used-dates="{{ $usedDatesJson }}"
                        data-keterangan="{{ e($keteranganValue) }}"
                        @click="open = false">
                        <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                    </button>
                    @endcan
                    @can('cuti-delete')
                    <form action="/cuti/{{ $d->id }}/delete" method="POST" class="m-0">
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
        <td colspan="8" class="px-2 py-8 text-center text-xs text-slate-400">
            <div class="flex flex-col items-center gap-1">
                <i data-lucide="inbox" style="width:24px;height:24px;" class="text-slate-300"></i>
                <span>Data cuti tidak ditemukan</span>
            </div>
        </td>
    </tr>
@endforelse
