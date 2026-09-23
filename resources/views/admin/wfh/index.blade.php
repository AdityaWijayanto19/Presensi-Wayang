@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data WFH Karyawan')

<x-admin.page-body>
    <x-admin.card>
        <div class="p-3">

            {{-- Filter --}}
            <form action="/panel/wfh" method="GET">
                <x-admin.input type="text" name="tanggal" id="tanggal" placeholder="Cari Tanggal WFH"
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
                            <option value="unpaid" {{ Request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
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
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Karyawan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Jabatan / Posisi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Atasan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Pengajuan WFH</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Laporan WFH</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="wfhTableBody" class="divide-y divide-slate-100">
                        @include('admin.wfh._rows')
                    </tbody>
                </table>
            </div>

            <div id="wfhPagination" class="mt-2">
                {{ $datawfh->appends(request()->all())->links() }}
            </div>
        </div>
    </x-admin.card>
</x-admin.page-body>

{{-- Modal Edit WFH --}}
<x-admin.modal id="modal-editwfh" title="Edit Data WFH">
    <form id="formEditWfh" method="POST">
        @csrf
        <input type="hidden" name="wfh_id" id="edit_wfh_id">

        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Status WFH <span class="text-red-500">*</span></label>
            <select name="status" id="edit_status"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors">
                <option value="pending_atasan">Menunggu Atasan</option>
                <option value="pending_admin">Menunggu HR</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>

        <x-admin.input type="date" name="tgl_wfh" id="edit_tgl_wfh"
            label="Tanggal WFH <span class='text-red-500'>*</span>" required />

        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi Pekerjaan <span
                    class="text-red-500">*</span></label>
            <textarea name="deskripsi_pekerjaan" id="edit_deskripsi"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
                rows="3" required></textarea>
        </div>

        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Keterangan</label>
            <textarea name="keterangan" id="edit_keterangan"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
                rows="2"></textarea>
        </div>

        <div class="mt-2">
            <x-admin.button variant="primary" icon="save" type="submit" block>Simpan Perubahan</x-admin.button>
        </div>
    </form>
</x-admin.modal>

{{-- Modal Detail WFH --}}
<x-admin.modal id="modal-detailwfh" title="Detail WFH" size="lg">
    <div id="detailContent" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Nama Karyawan</div>
                <div class="text-xs font-medium text-slate-800" id="dt-nama">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">NIK</div>
                <div class="text-xs font-medium text-slate-800" id="dt-nik">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jabatan</div>
                <div class="text-xs font-medium text-slate-800" id="dt-jabatan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Posisi</div>
                <div class="text-xs font-medium text-slate-800" id="dt-posisi">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Unit</div>
                <div class="text-xs font-medium text-slate-800" id="dt-unit">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Perusahaan</div>
                <div class="text-xs font-medium text-slate-800" id="dt-perusahaan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Atasan</div>
                <div class="text-xs font-medium text-slate-800" id="dt-atasan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jabatan Atasan</div>
                <div class="text-xs font-medium text-slate-800" id="dt-jabatan-atasan">—</div>
            </div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal WFH</div>
                <div class="text-xs font-medium text-slate-800" id="dt-tgl-wfh">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Live Location</div>
                <div class="text-xs font-medium text-slate-800" id="dt-location">—</div>
            </div>
        </div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Deskripsi Pekerjaan</div>
            <div class="text-xs text-slate-700 bg-slate-50 rounded p-2 whitespace-pre-wrap" id="dt-deskripsi">—</div>
        </div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Keterangan</div>
            <div class="text-xs text-slate-700 bg-slate-50 rounded p-2 whitespace-pre-wrap" id="dt-keterangan">—</div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Status Pengajuan</div>
                <div id="dt-status"></div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Status Laporan</div>
                <div id="dt-laporan-status"></div>
            </div>
        </div>

        <div id="dt-rejected-reason-wrap" class="hidden">
            <div class="text-[10px] font-semibold text-rose-400 uppercase tracking-wider mb-0.5">Alasan Penolakan</div>
            <div class="text-xs text-rose-600 bg-rose-50 rounded p-2" id="dt-rejected-reason"></div>
        </div>

        <div id="dt-laporan-rejected-reason-wrap" class="hidden">
            <div class="text-[10px] font-semibold text-rose-400 uppercase tracking-wider mb-0.5">Alasan Penolakan Laporan</div>
            <div class="text-xs text-rose-600 bg-rose-50 rounded p-2" id="dt-laporan-rejected-reason"></div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Dokumen</div>
            <div class="flex flex-wrap gap-2">
                <div id="dt-pdf-wrap" class="hidden">
                    <a id="dt-pdf-link" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium hover:bg-blue-100 transition-colors">
                        <i data-lucide="file-text" style="width:12px;height:12px;"></i> Form Pengajuan WFH
                    </a>
                </div>
                <div id="dt-laporan-file-wrap" class="hidden">
                    <a id="dt-laporan-file-link" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-medium hover:bg-emerald-100 transition-colors">
                        <i data-lucide="clipboard" style="width:12px;height:12px;"></i> Laporan WFH
                    </a>
                </div>
                <div id="dt-no-dokumen" class="text-xs text-slate-400">Tidak ada dokumen</div>
            </div>
        </div>
    </div>
</x-admin.modal>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Flatpickr & filter inputs
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

        // Admin preview — open in new tab
        try {
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.js-preview-admin');
                if (!btn) return;
                e.preventDefault();
                var url = btn.dataset.url;
                if (url) window.open(url, '_blank');
            });
        } catch (e) { console.warn('Preview handler error:', e); }

        // Edit WFH Modal
        try {
            document.addEventListener('click', function(e) {
                var btnEdit = e.target.closest('.edit-wfh');
                if (btnEdit) {
                    var id = btnEdit.dataset.id;
                    var tgl = btnEdit.dataset.tgl_wfh;
                    var deskripsi = btnEdit.dataset.deskripsi;
                    var keterangan = btnEdit.dataset.keterangan || '';
                    var status = btnEdit.dataset.status || 'pending_atasan';

                    document.getElementById('edit_wfh_id').value = id;
                    document.getElementById('edit_tgl_wfh').value = tgl;
                    document.getElementById('edit_deskripsi').value = deskripsi;
                    document.getElementById('edit_keterangan').value = keterangan;
                    document.getElementById('edit_status').value = status;
                    document.getElementById('formEditWfh').action = '/presensi/wfh/' + id + '/update';
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editwfh'));
                }
            });
        } catch (e) { console.warn('Edit handler error:', e); }

        // Detail WFH Modal
        try {
            window.addEventListener('open-modal-modal-detailwfh', function(e) {
                var btn = e.detail && e.detail.el ? e.detail.el : null;
                if (!btn) return;
                var s = function(k) { return btn.dataset[k] || '—'; };
                var isEmpty = function(v) { return !v || v === '—' || v === ''; };

                document.getElementById('dt-nama').textContent = s('nama');
                document.getElementById('dt-nik').textContent = s('nik');
                document.getElementById('dt-jabatan').textContent = s('jabatan');
                document.getElementById('dt-posisi').textContent = s('posisi');
                document.getElementById('dt-unit').textContent = s('unit');
                document.getElementById('dt-perusahaan').textContent = s('perusahaan');
                document.getElementById('dt-atasan').textContent = s('atasan');
                document.getElementById('dt-jabatan-atasan').textContent = s('jabatanAtasan');
                document.getElementById('dt-tgl-wfh').textContent = s('tglWfh');
                document.getElementById('dt-location').textContent = isEmpty(s('liveLocation')) ? '—' : s('liveLocation');
                document.getElementById('dt-deskripsi').textContent = isEmpty(s('deskripsi')) ? '—' : s('deskripsi');
                document.getElementById('dt-keterangan').textContent = isEmpty(s('keterangan')) ? '—' : s('keterangan');

                var statusKey = s('statusKey');
                var statusLabel = s('status');
                var statusColors = {
                    'pending_atasan': 'bg-amber-50 text-amber-700 border border-amber-200',
                    'pending_admin': 'bg-blue-50 text-blue-700 border border-blue-200',
                    'approved': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    'rejected': 'bg-rose-50 text-rose-700 border border-rose-200',
                    'unpaid': 'bg-slate-100 text-slate-600 border border-slate-200',
                };
                document.getElementById('dt-status').innerHTML = '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold ' + (statusColors[statusKey] || 'bg-slate-100 text-slate-600 border border-slate-200') + '">' + statusLabel + '</span>';

                var lStatusKey = s('laporanStatusKey');
                var lStatusLabel = s('laporanStatus');
                document.getElementById('dt-laporan-status').innerHTML = (lStatusKey && lStatusLabel && lStatusLabel !== '—')
                    ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold ' + (statusColors[lStatusKey] || 'bg-slate-100 text-slate-600 border border-slate-200') + '">' + lStatusLabel + '</span>'
                    : '<span class="text-xs text-slate-400">—</span>';

                var rejWrap = document.getElementById('dt-rejected-reason-wrap');
                var rejReason = s('rejectedReason');
                if (!isEmpty(rejReason) && statusKey === 'rejected') {
                    document.getElementById('dt-rejected-reason').textContent = rejReason;
                    rejWrap.classList.remove('hidden');
                } else {
                    rejWrap.classList.add('hidden');
                }

                var lRejWrap = document.getElementById('dt-laporan-rejected-reason-wrap');
                var lRejReason = s('laporanRejectedReason');
                if (!isEmpty(lRejReason) && lStatusKey === 'rejected') {
                    document.getElementById('dt-laporan-rejected-reason').textContent = lRejReason;
                    lRejWrap.classList.remove('hidden');
                } else {
                    lRejWrap.classList.add('hidden');
                }

                var pdfUrl = s('pdfUrl');
                var laporanUrl = s('laporanUrl');
                var pdfWrap = document.getElementById('dt-pdf-wrap');
                var laporanWrap = document.getElementById('dt-laporan-file-wrap');
                var noDok = document.getElementById('dt-no-dokumen');
                if (!isEmpty(pdfUrl)) {
                    document.getElementById('dt-pdf-link').href = pdfUrl;
                    pdfWrap.classList.remove('hidden');
                } else {
                    pdfWrap.classList.add('hidden');
                }
                if (!isEmpty(laporanUrl)) {
                    document.getElementById('dt-laporan-file-link').href = laporanUrl;
                    laporanWrap.classList.remove('hidden');
                } else {
                    laporanWrap.classList.add('hidden');
                }
                noDok.classList.toggle('hidden', !isEmpty(pdfUrl) || !isEmpty(laporanUrl));

                if (window.lucide) lucide.createIcons();
            });
        } catch (e) { console.warn('Detail handler error:', e); }

        // Reject, Reject Laporan, & Delete buttons
        try {
            var wfhTableBody = document.getElementById('wfhTableBody');
            if (wfhTableBody) {
                wfhTableBody.addEventListener('click', function(e) {
                    var btnReject = e.target.closest('.btn-reject-admin');
                    if (btnReject) {
                        e.preventDefault();
                        var id = btnReject.dataset.id;
                        Swal.fire({
                            title: 'Tolak WFH?',
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
                                form.action = '/presensi/datawfh/' + id + '/reject';
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
                            title: 'Tolak Laporan WFH?',
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
                                form.action = '/presensi/datawfh/' + idL + '/reject-laporan-admin';
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
                            text: "Data WFH yang sudah dihapus tidak bisa dikembalikan!",
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
                    fetch('/api/realtime/admin/wfh-data' + (qs ? '?' + qs : ''), {
                            credentials: 'same-origin'
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var tbody = document.getElementById('wfhTableBody');
                            var pagination = document.getElementById('wfhPagination');
                            if (tbody && data.html) tbody.innerHTML = data.html;
                            if (pagination && data.pagination) pagination.innerHTML = data.pagination;
                            if (window.lucide) lucide.createIcons();
                        }).catch(function() {});
                }

                var adminPollInterval = 5000;
                var lastWfhId = 0;

                function pollAdminData() {
                    var checkUrl = '/api/realtime/admin/wfh-check?last_check=' + encodeURIComponent(lastCheck);
                    if (lastWfhId) {
                        checkUrl += '&last_id=' + lastWfhId;
                    }
                    fetch(checkUrl, {
                            credentials: 'same-origin'
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(check) {
                            if (check.latest_id) {
                                lastWfhId = check.latest_id;
                            }
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

                var pagination = document.getElementById('wfhPagination');
                if (pagination) {
                    pagination.addEventListener('click', function(e) {
                        var link = e.target.closest('a');
                        if (!link) return;
                        e.preventDefault();
                        var apiUrl = link.href.replace('/panel/wfh', '/api/realtime/admin/wfh-data');
                        fetch(apiUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                var tbody = document.getElementById('wfhTableBody');
                                var pag = document.getElementById('wfhPagination');
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
