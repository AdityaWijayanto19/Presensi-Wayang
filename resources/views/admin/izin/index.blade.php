@extends('layouts.admin.app')

@section('content')
@section('page_title', 'Data Izin Karyawan')

    <x-admin.page-body>

        <x-admin.card>

            <div class="p-3">

                {{-- ================================================== --}}
                {{-- Filter --}}
                {{-- ================================================== --}}
                <form action="/panel/izin" method="GET">

                    {{-- Tanggal --}}
                    <x-admin.input
                        type="text"
                        name="tanggal"
                        id="tanggal"
                        placeholder="Cari Data Izin"
                        value="{{ Request('tanggal') }}"
                        autocomplete="off"
                        icon="calendar"
                    />

                    {{-- Filter Nama, Unit & Jenis --}}
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
                        <div class="col-span-12 sm:col-span-3">
                            <x-admin.select name="jenis_izin" placeholder="Semua Jenis Izin">
                                <option value="i" {{ Request('jenis_izin') == 'i' ? 'selected' : '' }}>Izin</option>
                                <option value="s" {{ Request('jenis_izin') == 's' ? 'selected' : '' }}>Sakit</option>
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.button variant="primary" icon="search" type="submit" block>Cari Data</x-admin.button>
                        </div>
                    </div>

                </form>

                {{-- ================================================== --}}
                {{-- Tabel Data Izin --}}
                {{-- ================================================== --}}
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No.</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Tanggal Izin</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jenis Izin</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @forelse ($dataizin as $d)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-1.5 text-xs text-slate-700">
                                        {{ ($dataizin->currentPage() - 1) * $dataizin->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">
                                        {{ date('d-m-Y', strtotime($d->tgl_izin)) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">{{ $d->nik }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->nama_lengkap }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->jabatan }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->perusahaan }}</td>
                                    <td class="px-2 py-1.5 text-xs text-slate-700">
                                        {{ $d->jenis_izin == 'i' ? 'Izin' : 'Sakit' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs">
                                        <div class="flex flex-wrap gap-1">
                                            <x-admin.button variant="success" icon="file-text" size="sm" href="/presensi/showfile/{{ $d->file }}" target="_blank" />
                                            @can('presensi-edit')
                                                <x-admin.button variant="edit" icon="square-pen" size="sm" class="edit-izin"
                                                    data-id="{{ $d->id }}" data-tgl_izin="{{ $d->tgl_izin }}" data-jenis_izin="{{ $d->jenis_izin }}" />
                                            @endcan
                                            @can('izin-delete')
                                                <form action="/presensi/dataizin/{{ $d->id }}/delete" method="POST" class="inline">
                                                    @csrf
                                                    <x-admin.button variant="danger" icon="trash-2" size="sm" type="submit" class="delete-confirm" />
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 px-3 py-2 text-sm">
                                        Data izin tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-2">
                    {{ $dataizin->appends(request()->all())->links() }}
                </div>

            </div>

        </x-admin.card>

    </x-admin.page-body>

    {{-- ================================================== --}}
    {{-- Modal Edit Izin --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-editizin" title="Edit Data Izin">
        <form id="formEditIzin" method="POST">
            @csrf
            <input type="hidden" name="izin_id" id="edit_izin_id">

            <x-admin.input
                type="date"
                name="tgl_izin"
                id="edit_tgl_izin"
                label="Tanggal Izin <span class='text-red-500'>*</span>"
                required
            />

            <x-admin.select name="jenis_izin" id="edit_jenis_izin" label="Jenis Izin <span class='text-red-500'>*</span>" required>
                <option value="i">Izin</option>
                <option value="s">Sakit</option>
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

            document.querySelector('select[name="jenis_izin"]').addEventListener('change', function() {
                this.closest('form').submit();
            });

            // Edit Izin Modal
            var tbody = document.querySelector('tbody');
            if (tbody) {
                tbody.addEventListener('click', function(e) {
                    var btn = e.target.closest('.edit-izin');
                    if (!btn) return;

                    var id = btn.dataset.id;
                    var tgl = btn.dataset.tgl_izin;
                    var jenis = btn.dataset.jenis_izin;

                    document.getElementById('edit_izin_id').value = id;
                    document.getElementById('edit_tgl_izin').value = tgl;
                    document.getElementById('edit_jenis_izin').value = jenis;
                    document.getElementById('formEditIzin').setAttribute('action', '/presensi/izin/' + id + '/update');
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editizin'));
                });
            }

            // Konfirmasi Hapus
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.delete-confirm');
                if (!btn) return;

                var form = btn.closest('form');
                e.preventDefault();

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
