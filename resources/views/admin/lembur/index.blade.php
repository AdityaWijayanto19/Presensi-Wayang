@extends('layouts.admin.app')

@section('content')
@section('page_title', 'Data Lembur Karyawan')

    <x-admin.page-body>

        <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <div class="mb-2">

                <form action="/panel/lembur" method="GET">

                    {{-- ========================= --}}
                    {{-- Filter Tanggal --}}
                    {{-- ========================= --}}
                    <div class="mb-2">

                        <div class="relative">

                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                                <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                    <path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M15 3v4" />
                                    <path d="M7 3v4" />
                                    <path d="M3 11h16" />
                                    <path d="M18 16.496v1.504l1 1" />

                                </svg>

                            </span>

                            <input type="text" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 pl-8 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" id="tanggal" name="tanggal"
                                autocomplete="off" placeholder="Cari Tanggal Lembur"
                                value="{{ Request('tanggal') ?? date('Y-m-d') }}">

                        </div>

                    </div>

                    {{-- ========================= --}}
                    {{-- Filter Nama & Unit --}}
                    {{-- ========================= --}}
                    <div class="grid grid-cols-12 gap-2">

                        <div class="col-span-12 sm:col-span-4">

                            <input type="text" name="nama_karyawan" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Cari Nama Karyawan" value="{{ Request('nama_karyawan') }}"
                                autocomplete="off">

                        </div>

                        <div class="col-span-12 sm:col-span-3">

                            <select name="unit" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">

                                <option value="">
                                    Semua Unit
                                </option>

                                @foreach ($unitperusahaan as $u)
                                    <option {{ Request('unit') == $u->unit ? 'selected' : '' }}
                                        value="{{ $u->unit }}">

                                        {{ $u->perusahaan }}

                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-span-12 sm:col-span-5">

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">

                                Cari Data

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            {{-- ================================================== --}}
            {{-- Tabel Data Lembur --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">
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

                                    <a href="/presensi/showfilelembur/{{ $d->file_form }}" target="_blank"
                                        class="inline-flex items-center gap-1 bg-blue-600 text-white px-2 py-1 rounded-md hover:bg-blue-700 transition-colors text-xs font-medium">

                                        Lihat Form

                                    </a>

                                </td>

                                <td class="px-2 py-1.5 text-xs">

                                    <a href="/presensi/showfilelembur/{{ $d->file_laporan }}" target="_blank"
                                        class="inline-flex items-center gap-1 bg-green-600 text-white px-2 py-1 rounded-md hover:bg-green-700 transition-colors text-xs font-medium">

                                        Lihat Laporan

                                    </a>

                                </td>

                                <td class="px-2 py-1.5 text-xs">

                                    @can('presensi-edit')
                                    <button type="button"
                                        class="inline-flex items-center gap-1 bg-cyan-500 text-white px-2 py-1 rounded-md hover:bg-cyan-600 transition-colors text-xs font-medium edit-lembur"
                                        data-id="{{ $d->id }}"
                                        data-tgl_lembur="{{ $d->tgl_lembur }}"
                                        data-durasi="{{ $d->durasi }}">

                                        <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                            height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">

                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3"/>
                                            <path d="M16 5l3 3"/>

                                        </svg>

                                    </button>
                                    @endcan

                                    @can('lembur-delete')
                                    <form action="/presensi/datalembur/{{ $d->id }}/delete"
                                        method="POST" class="inline">

                                        @csrf

                                        <button type="submit" class="inline-flex items-center gap-1 bg-red-600 text-white px-2 py-1 rounded-md hover:bg-red-700 transition-colors text-xs font-medium delete-confirm">

                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">

                                                <path d="M4 7h16" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12" />
                                                <path d="M9 7v-3h6v3" />

                                            </svg>

                                        </button>

                                    </form>
                                    @endcan

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

            {{-- ================================================== --}}
            {{-- Pagination --}}
            {{-- ================================================== --}}
            <div class="mt-2">

                {{ $datalembur->appends(request()->all())->links() }}

            </div>

        </div>

    </x-admin.page-body>

    {{-- ================================================== --}}
    {{-- Modal Edit Lembur --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-editlembur" title="Edit Data Lembur">
        <form id="formEditLembur" method="POST">
            @csrf
            <input type="hidden" name="lembur_id" id="edit_lembur_id">

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Tanggal Lembur</label>
                <input type="date" name="tgl_lembur" id="edit_tgl_lembur" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Durasi (Jam)</label>
                <select name="durasi" id="edit_durasi" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                    <option value="1">1 Jam</option>
                    <option value="2">2 Jam</option>
                    <option value="3">3 Jam</option>
                    <option value="4">4 Jam</option>
                    <option value="5">5 Jam</option>
                </select>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">Simpan Perubahan</button>
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

            // ==================================================
            // Edit Lembur Modal
            // ==================================================
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

        });

        // ==================================================
        // Konfirmasi Hapus
        // ==================================================
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

            }).then((result) => {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        });
    </script>
@endpush
