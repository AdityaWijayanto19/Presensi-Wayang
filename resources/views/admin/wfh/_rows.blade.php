@forelse ($datawfh as $d)
    @php
        $status = $d->status instanceof \App\Enums\WfhStatus ? $d->status->value : ($d->status ?? 'pending_atasan');
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
            'pending_admin' => 'Menunggu HR',
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
        $karyawanData = $d->karyawan;
        $atasanData = $d->atasan;
        $namaKaryawan = $karyawanData->nama_lengkap ?? '-';
        $jabatanKaryawan = $karyawanData->jabatan ?? '-';
        $posisiKaryawan = $karyawanData->posisi ?? '-';
        $unitKaryawan = $karyawanData->unit ?? '-';
        $perusahaanKaryawan = $karyawanData->unitperusahaan->perusahaan ?? '-';
        $atasanNama = $atasanData->nama_lengkap ?? '—';
        $jabatanAtasan = $atasanData->jabatan instanceof \App\Enums\Jabatan ? $atasanData->jabatan->value : ($atasanData->jabatan ?? '—');
    @endphp
    <tr class="hover:bg-slate-50">
        <td class="px-2 py-1.5 text-xs text-slate-600">{{ ($datawfh->currentPage() - 1) * $datawfh->perPage() + $loop->iteration }}</td>
        <td class="px-2 py-1.5 text-xs">
            {{ date('d-m-Y', strtotime($d->tgl_wfh)) }}
            <br><span class="text-xs text-slate-500">{{ $d->live_location ?? '-' }}</span>
        </td>
        <td class="px-2 py-1.5 text-xs">
            <span class="text-xs text-slate-500">{{ $d->nik }}</span><br>
            {{ $namaKaryawan }}
        </td>
        <td class="px-2 py-1.5 text-xs">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $jabatanBadge }}">{{ $jabatanKaryawan }}</span>
            <br>{{ $posisiKaryawan }}
        </td>
        <td class="px-2 py-1.5 text-xs">
            {{ $perusahaanKaryawan }}<br><span class="text-xs text-slate-500">{{ $unitKaryawan }}</span>
        </td>
        <td class="px-2 py-1.5 text-xs">
            {{ $atasanNama }}<br>
            <span class="text-xs text-slate-500">{{ $d->atasan_nik ? $jabatanAtasan : 'Langsung Admin' }}</span>
        </td>
        <td class="px-2 py-1.5 text-xs">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $label }}</span>
            @if ($status == 'rejected' && !empty($d->rejected_reason))
                <br><span class="text-xs text-red-600">{{ Str::limit($d->rejected_reason, 30) }}</span>
            @endif
            @if (!empty($d->keterangan))
                <br><span class="text-xs text-cyan-600"><b>Ket:</b> {{ Str::limit($d->keterangan, 40) }}</span>
            @endif
            <br><span class="text-xs text-slate-500">{{ Str::limit($d->deskripsi_pekerjaan, 40) }}</span>
        </td>
        <td class="px-2 py-1.5 text-xs">
            @if ($pdfUrl)
                <x-admin.button variant="primary" size="sm" class="js-preview-admin"
                    data-url="{{ $pdfUrl }}" data-filename="{{ basename($pdfUrl) }}"
                    data-label="Form WFH — {{ $namaKaryawan }} {{ date('d-m-Y', strtotime($d->tgl_wfh)) }}">Preview</x-admin.button>
            @else
                <span class="text-slate-400">—</span>
            @endif
        </td>
        <td class="px-2 py-1.5 text-xs">
            @if (!empty($d->laporan_file))
                <x-admin.button variant="success" size="sm" class="js-preview-admin"
                    data-url="{{ Storage::url($d->laporan_file) }}" data-filename="{{ basename($d->laporan_file) }}"
                    data-label="Laporan — {{ $namaKaryawan }}">Preview</x-admin.button>
            @elseif(!empty($d->laporan_deskripsi))
                <span class="text-xs text-slate-500">{{ Str::limit($d->laporan_deskripsi, 30) }}</span>
            @else
                <span class="text-slate-400">—</span>
            @endif
        </td>
        <td class="px-2 py-1.5 text-xs">
            <div class="flex flex-col gap-1.5 items-start">
                @if ($status === 'pending_admin')
                    @can('wfh-approve')
                    <div class="flex gap-1.5">
                        <form action="/presensi/datawfh/{{ $d->id }}/approve" method="POST">
                            @csrf
                            <x-admin.button variant="success" size="sm" type="submit" title="Setujui">✓ Setujui</x-admin.button>
                        </form>
                        <x-admin.button variant="warning" size="sm" class="btn-reject-admin" data-id="{{ $d->id }}">Tolak</x-admin.button>
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
                            'pending_admin' => 'Laporan: Menunggu HR',
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
                                <x-admin.button variant="success" size="sm" type="submit">✓ Setujui Laporan</x-admin.button>
                            </form>
                            <x-admin.button variant="warning" size="sm" class="btn-reject-laporan-admin" data-id="{{ $d->id }}">Tolak Laporan</x-admin.button>
                        </div>
                        @endcan
                    @endif
                    @if ($lStatus == 'rejected' && !empty($d->laporan_rejected_reason))
                        <span class="text-xs text-red-600">{{ Str::limit($d->laporan_rejected_reason, 40) }}</span>
                    @endif
                @endif
                @can('presensi-edit')
                <x-admin.button variant="edit" size="sm" class="edit-wfh"
                    data-id="{{ $d->id }}" data-tgl_wfh="{{ $d->tgl_wfh }}"
                    data-deskripsi="{{ $d->deskripsi_pekerjaan }}" data-keterangan="{{ $d->keterangan }}">Edit</x-admin.button>
                @endcan
                @can('wfh-delete')
                <form action="/presensi/datawfh/{{ $d->id }}/delete" method="POST">
                    @csrf
                    <x-admin.button variant="danger" size="sm" type="submit" class="delete-confirm">Hapus</x-admin.button>
                </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="px-2 py-6 text-center text-xs text-slate-500">Data WFH tidak ditemukan</td>
    </tr>
@endforelse
