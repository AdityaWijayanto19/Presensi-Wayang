@extends('layouts.admin.app')

@section('content')

    {{-- ================================================== --}}
    {{-- Page Header --}}
    {{-- ================================================== --}}
    <x-app.page-header title="Monitoring Presensi" pretitle="WAG - Presensi Digital" />

    {{-- ================================================== --}}
    {{-- Page Body --}}
    {{-- ================================================== --}}
    <x-app.page-body>

        <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <div>

                {{-- ========================= --}}
                {{-- Tanggal --}}
                {{-- ========================= --}}
                <div class="relative mb-2">

                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">

                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                            <path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M15 3v4" />
                            <path d="M7 3v4" />
                            <path d="M3 11h16" />
                            <path d="M18 16.496v1.504l1 1" />

                        </svg>

                    </span>

                    <input type="text"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 pl-8 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        id="tanggal"
                        name="tanggal"
                        placeholder="Pilih Tanggal Presensi"
                        autocomplete="off"
                        value="{{ date('Y-m-d') }}">

                </div>

                {{-- ========================= --}}
                {{-- Filter Pencarian --}}
                {{-- ========================= --}}
                <div class="grid grid-cols-12 gap-2 mb-2">

                    <div class="col-span-12 md:col-span-5">

                        <input type="text"
                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="nama_karyawan"
                            placeholder="Cari Nama Karyawan"
                            autocomplete="off">

                    </div>

                    <div class="col-span-12 md:col-span-5">

                        <select class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="unit">

                            <option value="">
                                Semua Unit
                            </option>

                            @foreach ($unitperusahaan as $u)

                                <option value="{{ $u->unit }}">
                                    {{ $u->unit }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-span-12 md:col-span-2">

                        <button type="button"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                            id="btnCari">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round">

                                <circle cx="10.5" cy="10.5" r="7.5" />
                                <line x1="21" y1="21" x2="15.8" y2="15.8" />

                            </svg>

                            Cari Data

                        </button>

                    </div>

                </div>

            </div>

            {{-- ================================================== --}}
            {{-- Tabel Monitoring --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead>

                        <tr>

                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No.</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Masuk</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Foto Masuk</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pulang</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Foto Pulang</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Keterangan Presensi</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-2.5 py-1.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Lembur</th>

                        </tr>

                    </thead>

                    <tbody id="loadpresensi" class="divide-y divide-slate-200">

                    </tbody>

                </table>

            </div>

        </div>

    </x-app.page-body>

    {{-- ================================================== --}}
    {{-- Modal Peta --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-tampilkanpeta" title="Lokasi Presensi Karyawan">
        <div id="loadmap">

            {{-- Map akan dimuat menggunakan AJAX --}}

        </div>
    </x-app.modal>

    {{-- ================================================== --}}
    {{-- Modal Edit Presensi --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-editpresensi" title="Edit Data Presensi">
        <form id="formEditPresensi" method="POST">
            @csrf
            <input type="hidden" name="presensi_id" id="edit_presensi_id">

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Jam Masuk</label>
                <input type="time" name="jam_in" id="edit_jam_in" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" step="1" required>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Jam Pulang</label>
                <input type="time" name="jam_out" id="edit_jam_out" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" step="1">
                <small class="text-slate-500 text-sm">Kosongkan jika belum presensi pulang</small>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">Simpan Perubahan</button>
        </form>
    </x-app.modal>

@endsection

@push('myscript')

<script>

    $(function () {

         flatpickr("#tanggal", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                allowInput: true,
                disableMobile: "true"
            });

        // ==================================================
        // Load Data Presensi
        // ==================================================
        function loadpresensi() {

            var tanggal = $('#tanggal').val();
            var nama_karyawan = $('#nama_karyawan').val();
            var unit = $('#unit').val();

            $.ajax({

                type: 'POST',

                url: '/getpresensi',

                data: {
                    _token: "{{ csrf_token() }}",
                    tanggal: tanggal,
                    nama_karyawan: nama_karyawan,
                    unit: unit
                },

                cache: false,

                success: function (respond) {

                    $("#loadpresensi").html(respond);

                }

            });

        }

        // ==================================================
        // Filter Berdasarkan Tanggal
        // ==================================================
        $("#tanggal").change(function () {

            loadpresensi();

        });

        // ==================================================
        // Button Cari
        // ==================================================
        $("#btnCari").click(function () {

            loadpresensi();

        });

        // ==================================================
        // Load Pertama Kali
        // ==================================================
        loadpresensi();

        // ==================================================
        // Preview Foto Presensi
        // ==================================================
        $(document).on('click', '.foto-monitoring', function () {

            Swal.fire({

                imageUrl: $(this).attr('src'),
                imageAlt: 'Foto Presensi',
                showConfirmButton: false,
                showCloseButton: true,
                width: '480px',
                backdrop: false

            });

        });

        // ==================================================
        // Edit Presensi Modal
        // ==================================================
        $(document).on('click', '.edit-presensi', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var jamIn = $(this).data('jam_in');
            var jamOut = $(this).data('jam_out');

            $('#edit_presensi_id').val(id);
            $('#edit_jam_in').val(jamIn || '');
            $('#edit_jam_out').val(jamOut || '');
            $('#formEditPresensi').attr('action', '/presensi/' + id + '/update');
            window.dispatchEvent(new CustomEvent('open-modal-modal-editpresensi'));
        });

    });

</script>

@endpush