@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Lembur Karyawan')

<x-admin.page-body>
    <x-admin.card>
        <div class="p-3">

            {{-- Filter --}}
            <form action="/panel/lembur" method="GET">
                <x-admin.input type="text" name="tanggal" id="tanggal" placeholder="Cari Tanggal Lembur"
                    value="{{ request('tanggal') }}" autocomplete="off" icon="calendar" />

                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12 sm:col-span-3">
                        <x-admin.input name="nama_karyawan" placeholder="Cari Nama"
                            value="{{ Request('nama_karyawan') }}" autocomplete="off" />
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.select name="unit" id="unit" searchable placeholder="Semua Unit">
                            @foreach ($unitperusahaan as $u)
                                <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>
                                    {{ $u->unit }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 sm:col-span-2">
                        <x-admin.select name="status" placeholder="Semua Status">
                            <option value="pending_atasan"
                                {{ Request('status') == 'pending_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                            <option value="pending_admin" {{ Request('status') == 'pending_admin' ? 'selected' : '' }}>
                                Menunggu HR</option>
                            <option value="approved" {{ Request('status') == 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ Request('status') == 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 sm:col-span-5">
                        <x-admin.button variant="primary" icon="search" type="submit" block>Cari Data</x-admin.button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto mt-2">
                <table class="min-w-full divide-y divide-slate-200 border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase w-10">No.</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Tanggal</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">NIK</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Nama Karyawan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Jabatan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Durasi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Pengajuan PDF</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Laporan PDF</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="lemburTableBody" class="divide-y divide-slate-100">
                        @include('admin.lembur._rows')
                    </tbody>
                </table>
            </div>

            <div id="lemburPagination" class="mt-2">
                {{ $datalembur->appends(request()->all())->links() }}
            </div>
        </div>
    </x-admin.card>
</x-admin.page-body>

{{-- Modal Edit Lembur --}}
<x-admin.modal id="modal-editlembur" title="Edit Data Lembur">
    <form id="formEditLembur" method="POST">
        @csrf
        <input type="hidden" name="lembur_id" id="edit_lembur_id">

        <x-admin.input type="date" name="tgl_lembur" id="edit_tgl_lembur"
            label="Tanggal Lembur <span class='text-red-500'>*</span>" required />

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

        try {
            flatpickr("#tanggal", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                allowInput: true,
                disableMobile: true
            });
            var tanggalInput = document.querySelector('input[name="tanggal"]');
            if (tanggalInput) tanggalInput.addEventListener('change', function() {
                this.closest('form').submit();
            });
            var unitSelect = document.querySelector('select[name="unit"]');
            if (unitSelect) unitSelect.addEventListener('change', function() {
                this.closest('form').submit();
            });
            var statusSelect = document.querySelector('select[name="status"]');
            if (statusSelect) statusSelect.addEventListener('change', function() {
                this.closest('form').submit();
            });
        } catch (e) { console.warn('Filter init error:', e); }

        // Edit Lembur Modal
        try {
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-lembur');
                if (btn) {
                    var id = btn.dataset.id;
                    var tgl = btn.dataset.tgl_lembur;
                    var durasi = btn.dataset.durasi;

                    document.getElementById('edit_lembur_id').value = id;
                    document.getElementById('edit_tgl_lembur').value = tgl;
                    document.getElementById('edit_durasi').value = durasi;
                    document.getElementById('formEditLembur').action = '/presensi/lembur/' + id + '/update';
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editlembur'));
                }
            });
        } catch (e) { console.warn('Edit handler error:', e); }

        // Reject, Reject Laporan, & Delete buttons
        try {
            var lemburTableBody = document.getElementById('lemburTableBody');
            if (lemburTableBody) {
                lemburTableBody.addEventListener('click', function(e) {
                    var btnReject = e.target.closest('.btn-reject-admin');
                    if (btnReject) {
                        e.preventDefault();
                        var id = btnReject.dataset.id;
                        Swal.fire({
                            title: 'Tolak Lembur?',
                            input: 'textarea',
                            inputPlaceholder: 'Alasan penolakan...',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48',
                            confirmButtonText: 'Tolak',
                            inputValidator: v => {
                                if (!v || v.trim().length < 5) return 'Minimal 5 karakter';
                            }
                        }).then(res => {
                            if (res.isConfirmed) {
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '/presensi/datalembur/' + id + '/reject';
                                var csrf = document.createElement('input');
                                csrf.type = 'hidden';
                                csrf.name = '_token';
                                csrf.value = '{{ csrf_token() }}';
                                var reason = document.createElement('input');
                                reason.type = 'hidden';
                                reason.name = 'rejected_reason';
                                reason.value = res.value;
                                form.appendChild(csrf);
                                form.appendChild(reason);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                        return;
                    }

                    var btnRejectLaporan = e.target.closest('.btn-reject-laporan-admin');
                    if (btnRejectLaporan) {
                        e.preventDefault();
                        var idL = btnRejectLaporan.dataset.id;
                        Swal.fire({
                            title: 'Tolak Laporan Lembur?',
                            input: 'textarea',
                            inputPlaceholder: 'Alasan penolakan laporan...',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48',
                            confirmButtonText: 'Tolak Laporan',
                            inputValidator: v => {
                                if (!v || v.trim().length < 5) return 'Minimal 5 karakter';
                            }
                        }).then(res => {
                            if (res.isConfirmed) {
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '/presensi/datalembur/' + idL + '/reject-laporan-admin';
                                var csrf = document.createElement('input');
                                csrf.type = 'hidden';
                                csrf.name = '_token';
                                csrf.value = '{{ csrf_token() }}';
                                var reason = document.createElement('input');
                                reason.type = 'hidden';
                                reason.name = 'rejected_reason';
                                reason.value = res.value;
                                form.appendChild(csrf);
                                form.appendChild(reason);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                        return;
                    }

                    var btnDelete = e.target.closest('.delete-confirm');
                    if (btnDelete) {
                        e.preventDefault();
                        var form = btnDelete.closest('form');
                        Swal.fire({
                            title: 'Yakin data ini akan dihapus?',
                            text: "Data lembur yang sudah dihapus tidak bisa dikembalikan!",
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
                        return;
                    }
                });
            }
        } catch (e) { console.warn('Action buttons error:', e); }

        // Realtime Polling
        try {
            (function() {
                let lastCheck = new Date().toISOString();

                function getCurrentFilters() {
                    var params = new URLSearchParams(window.location.search);
                    var filters = {};
                    if (params.get('nama_karyawan')) filters.nama_karyawan = params.get('nama_karyawan');
                    if (params.get('unit')) filters.unit = params.get('unit');
                    if (params.get('tanggal')) filters.tanggal = params.get('tanggal');
                    if (params.get('status')) filters.status = params.get('status');
                    if (params.get('page')) filters.page = params.get('page');
                    return filters;
                }

                function fetchTableData() {
                    var filters = getCurrentFilters();
                    var qs = new URLSearchParams(filters).toString();
                    fetch('/api/realtime/admin/lembur-data' + (qs ? '?' + qs : ''), {
                            credentials: 'same-origin'
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var tbody = document.getElementById('lemburTableBody');
                            var pagination = document.getElementById('lemburPagination');
                            if (tbody && data.html) tbody.innerHTML = data.html;
                            if (pagination && data.pagination) pagination.innerHTML = data.pagination;
                            if (window.lucide) lucide.createIcons();
                        }).catch(function() {});
                }

                var adminPollInterval = 5000;

                function pollAdminData() {
                    fetch('/api/realtime/admin/lembur-check?last_check=' + encodeURIComponent(lastCheck), {
                            credentials: 'same-origin'
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(check) {
                            if (check.updated_data) {
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

                var pagination = document.getElementById('lemburPagination');
                if (pagination) {
                    pagination.addEventListener('click', function(e) {
                        var link = e.target.closest('a');
                        if (!link) return;
                        e.preventDefault();
                        var apiUrl = link.href.replace('/panel/lembur', '/api/realtime/admin/lembur-data');
                        fetch(apiUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                var tbody = document.getElementById('lemburTableBody');
                                var pag = document.getElementById('lemburPagination');
                                if (tbody && data.html) tbody.innerHTML = data.html;
                                if (pag && data.pagination) pag.innerHTML = data.pagination;
                                window.history.pushState({}, '', link.href);
                                if (window.lucide) lucide.createIcons();
                            }).catch(function() {});
                    });
                }
            })();
        } catch (e) { console.warn('Realtime polling error:', e); }

        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
