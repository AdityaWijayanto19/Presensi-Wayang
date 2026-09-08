@forelse ($datawfh as $d)
    @php
        $status = $d->status ?? 'pending_atasan';
        $badgeClass = match ($status) {
            'pending_atasan' => 'bg-yellow-500 text-white',
            'pending_admin' => 'bg-cyan-500 text-white',
            'approved' => 'bg-green-500 text-white',
            'rejected' => 'bg-red-500 text-white',
            'unpaid' => 'bg-slate-500 text-white',
            default => 'bg-slate-500 text-white',
        };
        $label = match ($status) {
            'pending_atasan' => 'Menunggu Atasan',
            'pending_admin' => 'Menunggu Admin',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'unpaid' => 'Unpaid',
            default => $status,
        };
        $jabatanBadge = match($d->jabatan) {
            'Direktur' => 'bg-red-100 text-red-700',
            'GM' => 'bg-yellow-100 text-yellow-700',
            'Manager' => 'bg-cyan-100 text-cyan-700',
            'SPV' => 'bg-blue-100 text-blue-700',
            'Staff' => 'bg-green-100 text-green-700',
            'Intern' => 'bg-cyan-100 text-cyan-700',
            default => 'bg-slate-100 text-slate-600',
        };
        $pdfUrl = !empty($d->pdf_form_path) ? Storage::url($d->pdf_form_path) : null;
    @endphp
    <tr class="hover:bg-slate-50">
        <td class="px-3 py-2 text-sm text-slate-600">{{ ($datawfh->currentPage() - 1) * $datawfh->perPage() + $loop->iteration }}</td>
        <td class="px-3 py-2 text-sm">
            {{ date('d-m-Y', strtotime($d->tgl_wfh)) }}
            <br><span class="text-xs text-slate-500">{{ $d->live_location ?? '-' }}</span>
        </td>
        <td class="px-3 py-2 text-sm">
            <span class="text-xs text-slate-500">{{ $d->nik }}</span><br>
            {{ $d->nama_lengkap }}
        </td>
        <td class="px-3 py-2 text-sm">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $jabatanBadge }}">{{ $d->jabatan ?? '-' }}</span>
            <br>{{ $d->posisi }}
        </td>
        <td class="px-3 py-2 text-sm">
            {{ $d->perusahaan }}<br><span class="text-xs text-slate-500">{{ $d->unit }}</span>
        </td>
        <td class="px-3 py-2 text-sm">
            {{ $d->atasan_nama ?? '—' }}<br>
            <span class="text-xs text-slate-500">{{ $d->atasan_jabatan ?? ($d->atasan_nik ? $d->atasan_nik : 'Langsung Admin') }}</span>
        </td>
        <td class="px-3 py-2 text-sm">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $label }}</span>
            @if ($status == 'rejected' && !empty($d->rejected_reason))
                <br><span class="text-xs text-red-600">{{ Str::limit($d->rejected_reason, 30) }}</span>
            @endif
            @if (!empty($d->keterangan))
                <br><span class="text-xs text-cyan-600"><b>Ket:</b> {{ Str::limit($d->keterangan, 40) }}</span>
            @endif
            <br><span class="text-xs text-slate-500">{{ Str::limit($d->deskripsi_pekerjaan, 40) }}</span>
        </td>
        <td class="px-3 py-2 text-sm">
            @if ($pdfUrl)
                <button type="button" class="px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors js-preview-admin"
                    data-url="{{ $pdfUrl }}" data-filename="{{ basename($pdfUrl) }}"
                    data-label="Form WFH — {{ $d->nama_lengkap }} {{ date('d-m-Y', strtotime($d->tgl_wfh)) }}">Preview</button>
            @else
                <span class="text-slate-400">—</span>
            @endif
        </td>
        <td class="px-3 py-2 text-sm">
            @if (!empty($d->laporan_file))
                <button type="button" class="px-2 py-1 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors js-preview-admin"
                    data-url="{{ Storage::url($d->laporan_file) }}" data-filename="{{ basename($d->laporan_file) }}"
                    data-label="Laporan — {{ $d->nama_lengkap }}">Preview</button>
            @elseif(!empty($d->laporan_deskripsi))
                <span class="text-xs text-slate-500">{{ Str::limit($d->laporan_deskripsi, 30) }}</span>
            @else
                <span class="text-slate-400">—</span>
            @endif
        </td>
        <td class="px-3 py-2 text-sm">
            <div class="flex flex-col gap-1.5 items-start">
                @if ($status === 'pending_admin')
                    @can('wfh-approve')
                    <div class="flex gap-1.5">
                        <form action="/presensi/datawfh/{{ $d->id }}/approve" method="POST">
                            @csrf
                            <button type="submit" class="px-2 py-1 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors" title="Setujui">✓ Setujui</button>
                        </form>
                        <button type="button" class="px-2 py-1 text-xs font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition-colors btn-reject-admin" data-id="{{ $d->id }}">Tolak</button>
                    </div>
                    @endcan
                @elseif($status === 'approved')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Pengajuan: Disetujui</span>
                @elseif($status === 'rejected')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">WFH: Ditolak</span>
                @elseif($status === 'unpaid')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">WFH: Unpaid</span>
                @endif
                @if (!empty($d->laporan_status))
                    @php
                        $lStatus = $d->laporan_status;
                        $lBadgeClass = match ($lStatus) {
                            'pending_atasan' => 'bg-yellow-100 text-yellow-700',
                            'pending_admin' => 'bg-blue-100 text-blue-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                        $lLabel = match ($lStatus) {
                            'pending_atasan' => 'Laporan: Menunggu Atasan',
                            'pending_admin' => 'Laporan: Menunggu Admin',
                            'approved' => 'Laporan: Disetujui',
                            'rejected' => 'Laporan: Ditolak',
                            default => 'Laporan: ' . $lStatus,
                        };
                    @endphp
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lBadgeClass }}">{{ $lLabel }}</span>
                    </div>
                    @if ($lStatus == 'pending_admin')
                        @can('wfh-approve')
                        <div class="flex gap-1.5">
                            <form action="/presensi/datawfh/{{ $d->id }}/approve-laporan-admin" method="POST">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">✓ Setujui Laporan</button>
                            </form>
                            <button type="button" class="px-2 py-1 text-xs font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition-colors btn-reject-laporan-admin" data-id="{{ $d->id }}">Tolak Laporan</button>
                        </div>
                        @endcan
                    @endif
                    @if ($lStatus == 'rejected' && !empty($d->laporan_rejected_reason))
                        <span class="text-xs text-red-600">{{ Str::limit($d->laporan_rejected_reason, 40) }}</span>
                    @endif
                @endif
                @can('presensi-edit')
                <button type="button" class="px-2 py-1 text-xs font-medium text-white bg-cyan-500 rounded-md hover:bg-cyan-600 transition-colors edit-wfh"
                    data-id="{{ $d->id }}" data-tgl_wfh="{{ $d->tgl_wfh }}"
                    data-deskripsi="{{ $d->deskripsi_pekerjaan }}" data-keterangan="{{ $d->keterangan }}">Edit</button>
                @endcan
                @can('wfh-delete')
                <form action="/presensi/datawfh/{{ $d->id }}/delete" method="POST">
                    @csrf
                    <button type="submit" class="px-2 py-1 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors delete-confirm">Hapus</button>
                </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="px-3 py-6 text-center text-sm text-slate-500">Data WFH tidak ditemukan</td>
    </tr>
@endforelse
