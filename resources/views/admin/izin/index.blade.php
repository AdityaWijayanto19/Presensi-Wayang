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

                    {{-- Filter Nama, Unit, Jenis & Status --}}
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-3">
                            <x-admin.input
                                name="nama_karyawan"
                                placeholder="Cari Nama Karyawan"
                                value="{{ Request('nama_karyawan') }}"
                                autocomplete="off"
                            />
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.select name="unit" placeholder="Semua Unit">
                                @foreach ($unitperusahaan as $u)
                                    <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>{{ $u->perusahaan }}</option>
                                @endforeach
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.select name="jenis_izin" placeholder="Semua Jenis">
                                <option value="tidak_masuk" {{ Request('jenis_izin') == 'tidak_masuk' ? 'selected' : '' }}>Izin Tidak Masuk</option>
                                <option value="terlambat" {{ Request('jenis_izin') == 'terlambat' ? 'selected' : '' }}>Izin Terlambat</option>
                                <option value="pulang_cepat" {{ Request('jenis_izin') == 'pulang_cepat' ? 'selected' : '' }}>Izin Pulang Cepat</option>
                                <option value="sakit" {{ Request('jenis_izin') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.select name="status" placeholder="Semua Status">
                                <option value="pending_atasan" {{ Request('status') == 'pending_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                                <option value="pending_admin" {{ Request('status') == 'pending_admin' ? 'selected' : '' }}>Menunggu HR</option>
                                <option value="approved" {{ Request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ Request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-1">
                            <x-admin.button variant="primary" icon="search" type="submit" block>Cari</x-admin.button>
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
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="izinTableBody" class="divide-y divide-slate-200">
                            @include('admin.izin._rows')
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div id="izinPagination" class="mt-2">
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

            <x-admin.select name="jenis_izin" id="edit_jenis_izin" label="Kategori Izin <span class='text-red-500'>*</span>" required>
                <option value="tidak_masuk">Izin Tidak Masuk</option>
                <option value="terlambat">Izin Terlambat</option>
                <option value="pulang_cepat">Izin Pulang Cepat</option>
                <option value="sakit">Sakit</option>
            </x-admin.select>

            <div class="mt-2">
                <x-admin.button variant="primary" icon="save" type="submit" block>Simpan Perubahan</x-admin.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- ================================================== --}}
    {{-- Modal Reject Izin --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-reject-izin" title="Tolak Izin">
        <form id="formRejectIzin" method="POST">
            @csrf
            <input type="hidden" name="izin_id" id="reject_izin_id">

            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="rejected_reason" id="reject_reason" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan alasan penolakan (minimal 5 karakter)" required minlength="5" maxlength="500"></textarea>
            </div>

            <div class="mt-2">
                <x-admin.button variant="danger" icon="x" type="submit" block>Tolak Izin</x-admin.button>
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

            try {
                var tanggalInput = document.querySelector('input[name="tanggal"]');
                if (tanggalInput) tanggalInput.addEventListener('change', function() {
                    this.closest('form').submit();
                });
                var unitSelect = document.querySelector('select[name="unit"]');
                if (unitSelect) unitSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
                var jenisSelect = document.querySelector('select[name="jenis_izin"]');
                if (jenisSelect) jenisSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
                var statusSelect = document.querySelector('select[name="status"]');
                if (statusSelect) statusSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            } catch (e) { console.warn('Filter init error:', e); }

            // Edit Izin Modal
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-izin');
                if (btn) {
                    var id = btn.dataset.id;
                    var tgl = btn.dataset.tgl_izin;
                    var jenis = btn.dataset.jenis_izin;

                    document.getElementById('edit_izin_id').value = id;
                    document.getElementById('edit_tgl_izin').value = tgl;
                    document.getElementById('edit_jenis_izin').value = jenis;
                    document.getElementById('formEditIzin').setAttribute('action', '/presensi/izin/' + id + '/update');
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editizin'));
                }

                // Reject Izin
                var rejectBtn = e.target.closest('.btn-reject-izin');
                if (rejectBtn) {
                    var rejectId = rejectBtn.dataset.id;
                    document.getElementById('reject_izin_id').value = rejectId;
                    document.getElementById('formRejectIzin').setAttribute('action', '/presensi/dataizin/' + rejectId + '/reject');
                    window.dispatchEvent(new CustomEvent('open-modal-modal-reject-izin'));
                }
            });

            // Konfirmasi Hapus
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.delete-confirm');
                if (btn) {
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
                }
            });

            if (window.lucide) lucide.createIcons();
        });

        // Realtime Polling
        (function() {
            var lastCheck = new Date().toISOString();

            function getCurrentFilters() {
                var params = new URLSearchParams(window.location.search);
                var filters = {};
                if (params.get('nama_karyawan')) filters.nama_karyawan = params.get('nama_karyawan');
                if (params.get('unit')) filters.unit = params.get('unit');
                if (params.get('tanggal')) filters.tanggal = params.get('tanggal');
                if (params.get('jenis_izin')) filters.jenis_izin = params.get('jenis_izin');
                if (params.get('status')) filters.status = params.get('status');
                if (params.get('page')) filters.page = params.get('page');
                return filters;
            }

            function fetchTableData() {
                var filters = getCurrentFilters();
                var qs = new URLSearchParams(filters).toString();
                fetch('/api/realtime/admin/izin-data' + (qs ? '?' + qs : ''), {
                        credentials: 'same-origin'
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var tbody = document.getElementById('izinTableBody');
                        var pagination = document.getElementById('izinPagination');
                        if (tbody && data.html) tbody.innerHTML = data.html;
                        if (pagination && data.pagination) pagination.innerHTML = data.pagination;
                        if (window.lucide) lucide.createIcons();
                    }).catch(function() {});
            }

            var adminPollInterval = 5000;

            function pollAdminData() {
                fetch('/api/realtime/admin/izin-check?last_check=' + encodeURIComponent(lastCheck), {
                        credentials: 'same-origin'
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(check) {
                        if (check.updated_data || check.new_data) {
                            lastCheck = new Date().toISOString();
                            fetchTableData();
                        }
                        adminPollInterval = 5000;
                    }).catch(function() {
                        adminPollInterval = Math.min(adminPollInterval * 2, 30000);
                    });
            }

            function startAdminPoll() {
                setTimeout(function() {
                    pollAdminData();
                    startAdminPoll();
                }, adminPollInterval);
            }
            pollAdminData();
            startAdminPoll();

            // Pagination via AJAX
            var pagination = document.getElementById('izinPagination');
            if (pagination) {
                pagination.addEventListener('click', function(e) {
                    var link = e.target.closest('a');
                    if (!link) return;
                    e.preventDefault();
                    var apiUrl = link.href.replace('/panel/izin', '/api/realtime/admin/izin-data');
                    fetch(apiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var tbody = document.getElementById('izinTableBody');
                            var pag = document.getElementById('izinPagination');
                            if (tbody && data.html) tbody.innerHTML = data.html;
                            if (pag && data.pagination) pag.innerHTML = data.pagination;
                            window.history.pushState({}, '', link.href);
                            if (window.lucide) lucide.createIcons();
                        }).catch(function() {});
                });
            }
        })();
    </script>
@endpush
