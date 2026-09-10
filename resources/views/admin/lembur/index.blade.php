@extends('layouts.admin.app')

@section('content')
@section('page_title', 'Data Lembur Karyawan')

    <x-admin.page-body>

        <x-admin.card>

            <div class="p-3">

                {{-- ================================================== --}}
                {{-- Filter --}}
                {{-- ================================================== --}}
                <form action="/panel/lembur" method="GET">

                    {{-- Tanggal --}}
                    <x-admin.input
                        type="text"
                        name="tanggal"
                        id="tanggal"
                        placeholder="Cari Tanggal Lembur"
                        value="{{ Request('tanggal') ?? date('Y-m-d') }}"
                        autocomplete="off"
                        icon="calendar"
                    />

                    {{-- Filter Nama & Unit --}}
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-4">
                            <x-admin.input
                                name="nama_karyawan"
                                placeholder="Cari Nama Karyawan"
                                value="{{ Request('nama_karyawan') }}"
                                autocomplete="off"
                            />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <x-admin.select name="unit" placeholder="Semua Unit">
                                @foreach ($unitperusahaan as $u)
                                    <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>{{ $u->perusahaan }}</option>
                                @endforeach
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-5">
                            <x-admin.button variant="primary" icon="search" type="submit" block>Cari Data</x-admin.button>
                        </div>
                    </div>

                </form>

                {{-- ================================================== --}}
                {{-- Tabel Data Lembur --}}
                {{-- ================================================== --}}
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No.</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Tanggal Lembur</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Form Lembur</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Laporan Lembur</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @forelse ($datalembur as $d)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-1.5 text-xs text-slate-700">
                                        {{ ($datalembur->currentPage() - 1) * $datalembur->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">
                                        {{ date('d-m-Y', strtotime($d->tgl_lembur)) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">{{ $d->nik }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->nama_lengkap }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->jabatan }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->perusahaan }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">{{ $d->durasi }}</td>
                                    <td class="px-2 py-1.5 text-xs">
                                        <x-admin.button variant="primary" icon="file-text" size="sm" href="/presensi/showfilelembur/{{ $d->file_form }}" target="_blank" />
                                    </td>
                                    <td class="px-2 py-1.5 text-xs">
                                        <x-admin.button variant="success" icon="file-check" size="sm" href="/presensi/showfilelembur/{{ $d->file_laporan }}" target="_blank" />
                                    </td>
                                    <td class="px-2 py-1.5 text-xs">
                                        <div class="flex flex-wrap gap-1">
                                            @can('presensi-edit')
                                                <x-admin.button variant="edit" icon="square-pen" size="sm" class="edit-lembur"
                                                    data-id="{{ $d->id }}" data-tgl_lembur="{{ $d->tgl_lembur }}" data-durasi="{{ $d->durasi }}" />
                                            @endcan
                                            @can('lembur-delete')
                                                <form action="/presensi/datalembur/{{ $d->id }}/delete" method="POST" class="inline">
                                                    @csrf
                                                    <x-admin.button variant="danger" icon="trash-2" size="sm" type="submit" class="delete-confirm" />
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-slate-500 px-2 py-1.5 text-xs">
                                        Data lembur tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-2">
                    {{ $datalembur->appends(request()->all())->links() }}
                </div>

            </div>

        </x-admin.card>

    </x-admin.page-body>

    {{-- ================================================== --}}
    {{-- Modal Edit Lembur --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-editlembur" title="Edit Data Lembur">
        <form id="formEditLembur" method="POST">
            @csrf
            <input type="hidden" name="lembur_id" id="edit_lembur_id">

            <x-admin.input
                type="date"
                name="tgl_lembur"
                id="edit_tgl_lembur"
                label="Tanggal Lembur <span class='text-red-500'>*</span>"
                required
            />

            <x-admin.select name="durasi" id="edit_durasi" label="Durasi (Jam) <span class='text-red-500'>*</span>" required>
                <option value="1">1 Jam</option>
                <option value="2">2 Jam</option>
                <option value="3">3 Jam</option>
                <option value="4">4 Jam</option>
                <option value="5">5 Jam</option>
            </x-admin.select>

            <div class="mt-2">
                <x-admin.button variant="primary" icon="save" type="submit" block>Simpan Perubahan</x-admin.button>
            </div>
        </form>
    </x-admin.modal>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            flatpickr("#tanggal", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                allowInput: true,
                disableMobile: "true"
            });

            document.querySelector('input[name="tanggal"]').addEventListener('change', function() {
                this.closest('form').submit();
            });

            document.querySelector('select[name="unit"]').addEventListener('change', function() {
                this.closest('form').submit();
            });

            // Edit Lembur Modal
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-lembur');
                if (!btn) return;

                var id = btn.dataset.id;
                var tgl = btn.dataset.tgl_lembur;
                var durasi = btn.dataset.durasi;

                document.getElementById('edit_lembur_id').value = id;
                document.getElementById('edit_tgl_lembur').value = tgl;
                document.getElementById('edit_durasi').value = durasi;
                document.getElementById('formEditLembur').setAttribute('action', '/presensi/lembur/' + id + '/update');
                window.dispatchEvent(new CustomEvent('open-modal-modal-editlembur'));
            });

            // Konfirmasi Hapus
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.delete-confirm');
                if (!btn) return;
                e.preventDefault();

                var form = btn.closest('form');

                Swal.fire({
                    title: 'Yakin data ini akan dihapus?',
                    text: "Data yang sudah dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Hapus Data',
                    backdrop: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            if (window.lucide) lucide.createIcons();
        });
    </script>
@endpush
