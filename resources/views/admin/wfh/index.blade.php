@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data WFH Karyawan')

<x-admin.page-body>
    <x-admin.card>
        <div class="p-3">

            {{-- Filter --}}
            <form action="/panel/wfh" method="GET">
                <x-admin.input
                    type="text"
                    name="tanggal"
                    id="tanggal"
                    placeholder="Cari Tanggal WFH"
                    value="{{ request('tanggal') }}"
                    autocomplete="off"
                    icon="calendar"
                />

                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12 sm:col-span-3">
                        <x-admin.input
                            name="nama_karyawan"
                            placeholder="Cari Nama"
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
                        <x-admin.select name="status" placeholder="Semua Status">
                            <option value="pending_atasan" {{ Request('status') == 'pending_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                            <option value="pending_admin" {{ Request('status') == 'pending_admin' ? 'selected' : '' }}>Menunggu Admin</option>
                            <option value="approved" {{ Request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ Request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
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
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">No.</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Tanggal</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">NIK / Nama</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Jabatan / Posisi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Atasan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Pengajuan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Laporan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase" style="min-width:200px">Actions</th>
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

{{-- Preview Modal --}}
<div id="adminPreviewBackdrop"
    style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(20,12,6,0.55); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:20px; width:100%; max-width:640px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f0ece8;">
            <div>
                <div id="adminModalTitle" style="font-size:14px; font-weight:700;">Preview</div>
                <div id="adminModalSubtitle" style="font-size:11px; color:#a8a29e;"></div>
            </div>
            <button type="button" id="adminModalClose"
                style="width:36px; height:36px; border-radius:999px; background:#f5f5f4; border:1px solid #e7e5e4;">x</button>
        </div>
        <div id="adminModalBody" style="flex:1; overflow:auto; background:#fafaf9; min-height:320px; display:flex; flex-direction:column;">
            <div style="flex:1; display:flex; align-items:center; justify-content:center; padding:32px;">Memuat...</div>
        </div>
        <div style="display:flex; gap:8px; padding:14px 20px; border-top:1px solid #f0ece8; justify-content:flex-end;">
            <a id="adminModalDownload" href="#" download class="px-4 py-2 text-sm font-medium border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">Download</a>
            <a id="adminModalOpenTab" href="#" target="_blank" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Buka di Tab Baru</a>
        </div>
    </div>
</div>

{{-- Modal Edit WFH --}}
<x-admin.modal id="modal-editwfh" title="Edit Data WFH">
    <form id="formEditWfh" method="POST">
        @csrf
        <input type="hidden" name="wfh_id" id="edit_wfh_id">

        <x-admin.input
            type="date"
            name="tgl_wfh"
            id="edit_tgl_wfh"
            label="Tanggal WFH <span class='text-red-500'>*</span>"
            required
        />

        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
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
            document.querySelector('select[name="status"]').addEventListener('change', function() {
                this.closest('form').submit();
            });

            // Admin preview
            const backdrop = document.getElementById('adminPreviewBackdrop');
            const body = document.getElementById('adminModalBody');
            const titleEl = document.getElementById('adminModalTitle');
            const subtitleEl = document.getElementById('adminModalSubtitle');
            const downloadEl = document.getElementById('adminModalDownload');
            const openTabEl = document.getElementById('adminModalOpenTab');
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-preview-admin');
                if (!btn) return;
                e.preventDefault();
                const url = btn.dataset.url;
                const filename = btn.dataset.filename || '';
                const label = btn.dataset.label || filename;
                titleEl.textContent = label;
                subtitleEl.textContent = filename;
                downloadEl.href = url;
                openTabEl.href = url;
                backdrop.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                const ext = (filename.split('.').pop() || '').toLowerCase();
                body.innerHTML = '';
                if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    const img = document.createElement('img');
                    img.src = url;
                    img.style.width = '100%';
                    img.style.height = 'auto';
                    img.style.objectFit = 'contain';
                    body.appendChild(img);
                } else if (ext === 'pdf') {
                    const iframe = document.createElement('iframe');
                    iframe.src = url;
                    iframe.style.width = '100%';
                    iframe.style.height = '480px';
                    iframe.style.border = '0';
                    body.appendChild(iframe);
                } else {
                    body.innerHTML = '<div style="padding:32px; text-align:center;"><p>Preview tidak tersedia untuk .' + ext + '</p><p><a href="' + url + '" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">Buka di Tab Baru</a></p></div>';
                }
            });
            document.getElementById('adminModalClose').addEventListener('click', () => {
                backdrop.style.display = 'none';
                document.body.style.overflow = '';
            });
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    backdrop.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });

            // Edit WFH Modal
            document.addEventListener('click', function(e) {
                var btnEdit = e.target.closest('.edit-wfh');
                if (btnEdit) {
                    var id = btnEdit.dataset.id;
                    var tgl = btnEdit.dataset.tgl_wfh;
                    var deskripsi = btnEdit.dataset.deskripsi;
                    var keterangan = btnEdit.dataset.keterangan || '';

                    document.getElementById('edit_wfh_id').value = id;
                    document.getElementById('edit_tgl_wfh').value = tgl;
                    document.getElementById('edit_deskripsi').value = deskripsi;
                    document.getElementById('edit_keterangan').value = keterangan;
                    document.getElementById('formEditWfh').action = '/presensi/wfh/' + id + '/update';
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editwfh'));
                }
            });

            // Reject buttons
            document.getElementById('wfhTableBody').addEventListener('click', function(e) {
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

            // Realtime Polling
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
                    fetch('/api/realtime/admin/wfh-data' + (qs ? '?' + qs : ''), { credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var tbody = document.getElementById('wfhTableBody');
                            var pagination = document.getElementById('wfhPagination');
                            if (tbody && data.html) tbody.innerHTML = data.html;
                            if (pagination && data.pagination) pagination.innerHTML = data.pagination;
                        }).catch(function() {});
                }

                function pollAdminData() {
                    fetch('/api/realtime/admin/wfh-check?last_check=' + encodeURIComponent(lastCheck), { credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(check) {
                            if (check.updated_data || check.new_data) {
                                lastCheck = new Date().toISOString();
                                fetchTableData();
                            }
                        }).catch(function() {});
                }

                pollAdminData();
                setInterval(pollAdminData, 5000);

                document.getElementById('wfhPagination').addEventListener('click', function(e) {
                    var link = e.target.closest('a');
                    if (!link) return;
                    e.preventDefault();
                    var apiUrl = link.href.replace('/panel/wfh', '/api/realtime/admin/wfh-data');
                    fetch(apiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var tbody = document.getElementById('wfhTableBody');
                            var pagination = document.getElementById('wfhPagination');
                            if (tbody && data.html) tbody.innerHTML = data.html;
                            if (pagination && data.pagination) pagination.innerHTML = data.pagination;
                            window.history.pushState({}, '', link.href);
                        }).catch(function() {});
                });
            })();

            if (window.lucide) lucide.createIcons();
        });
    </script>
@endpush
