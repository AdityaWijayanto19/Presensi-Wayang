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
                            <option value="unpaid" {{ Request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid
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
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase">Laporan</th>
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

        <x-admin.select name="status" id="edit_status" label="Status <span class='text-red-500'>*</span>" required>
            <option value="pending_atasan">Menunggu Persetujuan</option>
            <option value="pending_admin">Menunggu Persetujuan HR</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="unpaid">Unpaid</option>
        </x-admin.select>

        <x-admin.select name="durasi_jam" id="edit_durasi_jam" label="Durasi (Jam) <span class='text-red-500'>*</span>" required>
            <option value="1">1 Jam</option>
            <option value="1.5">1.5 Jam</option>
            <option value="2">2 Jam</option>
            <option value="2.5">2.5 Jam</option>
            <option value="3">3 Jam</option>
            <option value="3.5">3.5 Jam</option>
            <option value="4">4 Jam</option>
            <option value="4.5">4.5 Jam</option>
            <option value="5">5 Jam</option>
            <option value="5.5">Prorate</option>
        </x-admin.select>

        <div class="mt-2">
            <x-admin.button variant="primary" icon="save" type="submit" block>Simpan Perubahan</x-admin.button>
        </div>
    </form>
</x-admin.modal>

{{-- Modal Detail Lembur --}}
<x-admin.modal id="modal-detaillembur" title="Detail Lembur" size="lg">
    <div id="detailLemburContent" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Nama Karyawan</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-nama">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">NIK</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-nik">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jabatan</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-jabatan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Posisi</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-posisi">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Unit</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-unit">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Perusahaan</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-perusahaan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Atasan</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-atasan">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jabatan Atasan</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-jabatan-atasan">—</div>
            </div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal Lembur</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-tgl">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Rencana Waktu</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-rencana">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Durasi</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-durasi">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Waktu Mulai</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-mulai">—</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Waktu Selesai</div>
                <div class="text-xs font-medium text-slate-800" id="dtl-selesai">—</div>
            </div>
        </div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Keterangan</div>
            <div class="text-xs text-slate-700 bg-slate-50 rounded p-2 whitespace-pre-wrap" id="dtl-keterangan">—</div>
        </div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Deskripsi Laporan</div>
            <div class="text-xs text-slate-700 bg-slate-50 rounded p-2 whitespace-pre-wrap" id="dtl-laporan-deskripsi">—</div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Status Pengajuan</div>
                <div id="dtl-status"></div>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Status Laporan</div>
                <div id="dtl-laporan-status"></div>
            </div>
        </div>

        <div id="dtl-rejected-reason-wrap" class="hidden">
            <div class="text-[10px] font-semibold text-rose-400 uppercase tracking-wider mb-0.5">Alasan Penolakan</div>
            <div class="text-xs text-rose-600 bg-rose-50 rounded p-2" id="dtl-rejected-reason"></div>
        </div>

        <div id="dtl-laporan-rejected-reason-wrap" class="hidden">
            <div class="text-[10px] font-semibold text-rose-400 uppercase tracking-wider mb-0.5">Alasan Penolakan Laporan</div>
            <div class="text-xs text-rose-600 bg-rose-50 rounded p-2" id="dtl-laporan-rejected-reason"></div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Dokumen</div>
            <div class="flex flex-wrap gap-2">
                <div id="dtl-pdf-wrap" class="hidden">
                    <a id="dtl-pdf-link" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium hover:bg-blue-100 transition-colors">
                        <i data-lucide="file-text" style="width:12px;height:12px;"></i> Form Pengajuan Lembur
                    </a>
                </div>
                <div id="dtl-laporan-file-wrap" class="hidden">
                    <a id="dtl-laporan-file-link" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-medium hover:bg-emerald-100 transition-colors">
                        <i data-lucide="file-check" style="width:12px;height:12px;"></i> Laporan Lembur
                    </a>
                </div>
                <div id="dtl-no-dokumen" class="text-xs text-slate-400">Tidak ada dokumen</div>
            </div>
        </div>

        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Foto</div>
            <div class="flex flex-wrap gap-2">
                <div id="dtl-foto-mulai-wrap" class="hidden">
                    <div class="text-[10px] text-slate-400 mb-1">Foto Mulai</div>
                    <a id="dtl-foto-mulai-link" href="#" target="_blank" rel="noopener">
                        <img id="dtl-foto-mulai" src="" alt="Foto Mulai"
                            class="w-24 h-24 object-cover rounded-md border border-slate-200" />
                    </a>
                </div>
                <div id="dtl-foto-selesai-wrap" class="hidden">
                    <div class="text-[10px] text-slate-400 mb-1">Foto Selesai</div>
                    <a id="dtl-foto-selesai-link" href="#" target="_blank" rel="noopener">
                        <img id="dtl-foto-selesai" src="" alt="Foto Selesai"
                            class="w-24 h-24 object-cover rounded-md border border-slate-200" />
                    </a>
                </div>
            </div>
            <div id="dtl-gallery" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2"></div>
            <div id="dtl-no-foto" class="text-xs text-slate-400">Tidak ada foto</div>
        </div>
    </div>
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
                    var status = btn.dataset.status;
                    var durasiJam = btn.dataset.durasi_jam;

                    document.getElementById('edit_lembur_id').value = id;
                    document.getElementById('edit_tgl_lembur').value = tgl;
                    document.getElementById('edit_status').value = status || 'pending_atasan';
                    var durasiSelect = document.getElementById('edit_durasi_jam');
                    if (durasiSelect) {
                        var durasiVal = durasiJam && parseFloat(durasiJam) > 5 ? '5.5' : durasiJam;
                        durasiSelect.value = durasiVal || '1';
                    }
                    document.getElementById('formEditLembur').action = '/presensi/lembur/' + id + '/update';
                    window.dispatchEvent(new CustomEvent('open-modal-modal-editlembur'));
                }
            });
        } catch (e) { console.warn('Edit handler error:', e); }

        // Detail Lembur Modal
        try {
            window.addEventListener('open-modal-modal-detaillembur', function(e) {
                var btn = e.detail && e.detail.el ? e.detail.el : null;
                if (!btn) return;
                var s = function(k) { return btn.dataset[k] || '—'; };
                var isEmpty = function(v) { return !v || v === '—' || v === ''; };

                document.getElementById('dtl-nama').textContent = s('nama');
                document.getElementById('dtl-nik').textContent = s('nik');
                document.getElementById('dtl-jabatan').textContent = s('jabatan');
                document.getElementById('dtl-posisi').textContent = isEmpty(s('posisi')) ? '—' : s('posisi');
                document.getElementById('dtl-unit').textContent = s('unit');
                document.getElementById('dtl-perusahaan').textContent = s('perusahaan');
                document.getElementById('dtl-atasan').textContent = s('atasan');
                document.getElementById('dtl-jabatan-atasan').textContent = s('jabatanAtasan');
                document.getElementById('dtl-tgl').textContent = s('tglLembur');
                document.getElementById('dtl-rencana').textContent = isEmpty(s('rencanaWaktu')) ? '—' : s('rencanaWaktu');
                document.getElementById('dtl-durasi').textContent = isEmpty(s('durasi')) ? '—' : s('durasi');
                document.getElementById('dtl-mulai').textContent = s('waktuMulai');
                document.getElementById('dtl-selesai').textContent = s('waktuSelesai');
                document.getElementById('dtl-keterangan').textContent = isEmpty(s('keterangan')) ? '—' : s('keterangan');
                document.getElementById('dtl-laporan-deskripsi').textContent = isEmpty(s('laporanDeskripsi')) ? '—' : s('laporanDeskripsi');

                var statusKey = s('statusKey');
                var statusLabel = s('status');
                var lStatusKey = s('laporanStatusKey');
                var lStatusLabel = s('laporanStatus');
                var statusColors = {
                    'pending_atasan': 'bg-amber-100 text-amber-700 border border-amber-200',
                    'pending_admin': 'bg-amber-100 text-amber-700 border border-amber-200',
                    'approved': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                    'rejected': 'bg-rose-100 text-rose-700 border border-rose-200',
                    'unpaid': 'bg-gray-100 text-gray-700 border border-gray-200',
                };
                var badge = function(key, label) {
                    if (isEmpty(label) || isEmpty(key)) return '<span class="text-xs text-slate-400">—</span>';
                    return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold ' +
                        (statusColors[key] || 'bg-slate-100 text-slate-600 border border-slate-200') + '">' + label + '</span>';
                };
                document.getElementById('dtl-status').innerHTML = badge(statusKey, statusLabel);
                document.getElementById('dtl-laporan-status').innerHTML = badge(lStatusKey, lStatusLabel);

                var rejWrap = document.getElementById('dtl-rejected-reason-wrap');
                var rejReason = s('rejectedReason');
                if (!isEmpty(rejReason) && statusKey === 'rejected') {
                    document.getElementById('dtl-rejected-reason').textContent = rejReason;
                    rejWrap.classList.remove('hidden');
                } else {
                    rejWrap.classList.add('hidden');
                }

                var lRejWrap = document.getElementById('dtl-laporan-rejected-reason-wrap');
                var lRejReason = s('laporanRejectedReason');
                if (!isEmpty(lRejReason) && lStatusKey === 'rejected') {
                    document.getElementById('dtl-laporan-rejected-reason').textContent = lRejReason;
                    lRejWrap.classList.remove('hidden');
                } else {
                    lRejWrap.classList.add('hidden');
                }

                var pdfUrl = s('pdfUrl');
                var laporanUrl = s('laporanUrl');
                var pdfWrap = document.getElementById('dtl-pdf-wrap');
                var laporanWrap = document.getElementById('dtl-laporan-file-wrap');
                var noDok = document.getElementById('dtl-no-dokumen');
                if (!isEmpty(pdfUrl)) {
                    document.getElementById('dtl-pdf-link').href = pdfUrl;
                    pdfWrap.classList.remove('hidden');
                } else {
                    pdfWrap.classList.add('hidden');
                }
                if (!isEmpty(laporanUrl)) {
                    document.getElementById('dtl-laporan-file-link').href = laporanUrl;
                    laporanWrap.classList.remove('hidden');
                } else {
                    laporanWrap.classList.add('hidden');
                }
                noDok.classList.toggle('hidden', !isEmpty(pdfUrl) || !isEmpty(laporanUrl));

                var fotoMulai = s('fotoMulai');
                var fotoSelesai = s('fotoSelesai');
                var fmWrap = document.getElementById('dtl-foto-mulai-wrap');
                if (!isEmpty(fotoMulai)) {
                    document.getElementById('dtl-foto-mulai').src = fotoMulai;
                    document.getElementById('dtl-foto-mulai-link').href = fotoMulai;
                    fmWrap.classList.remove('hidden');
                } else {
                    fmWrap.classList.add('hidden');
                }
                var fsWrap = document.getElementById('dtl-foto-selesai-wrap');
                if (!isEmpty(fotoSelesai)) {
                    document.getElementById('dtl-foto-selesai').src = fotoSelesai;
                    document.getElementById('dtl-foto-selesai-link').href = fotoSelesai;
                    fsWrap.classList.remove('hidden');
                } else {
                    fsWrap.classList.add('hidden');
                }

                var gallery = [];
                try { gallery = JSON.parse(btn.dataset.gallery || '[]'); } catch (err) { gallery = []; }
                if (!Array.isArray(gallery)) gallery = [];
                var galleryEl = document.getElementById('dtl-gallery');
                galleryEl.innerHTML = gallery.map(function(item) {
                    return '<a href="' + item.src + '" target="_blank" rel="noopener" class="block group">' +
                        '<img src="' + item.src + '" alt="' + item.label + '" loading="lazy" ' +
                        'class="w-full h-24 object-cover rounded-md border border-slate-200 group-hover:border-blue-300 transition-colors" />' +
                        '<div class="text-[10px] text-slate-400 mt-0.5 truncate">' + item.label + '</div></a>';
                }).join('');

                var noFoto = document.getElementById('dtl-no-foto');
                noFoto.classList.toggle('hidden', gallery.length > 0 || !isEmpty(fotoMulai) || !isEmpty(fotoSelesai));

                if (window.lucide) lucide.createIcons();
            });
        } catch (e) { console.warn('Detail handler error:', e); }

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
                var lastHtml = '';
                var lastPagination = '';

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
                        .then(function(r) {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(function(data) {
                            var tbody = document.getElementById('lemburTableBody');
                            var pagination = document.getElementById('lemburPagination');
                            if (tbody && data.html && data.html !== lastHtml) {
                                tbody.innerHTML = data.html;
                                lastHtml = data.html;
                                if (window.lucide) lucide.createIcons();
                            }
                            if (pagination && data.pagination && data.pagination !== lastPagination) {
                                pagination.innerHTML = data.pagination;
                                lastPagination = data.pagination;
                            }
                        }).catch(function(err) { console.error('Lembur table fetch error:', err); });
                }

                setInterval(fetchTableData, 5000);

                var pagination = document.getElementById('lemburPagination');
                if (pagination) {
                    pagination.addEventListener('click', function(e) {
                        var link = e.target.closest('.pagination a, nav a');
                        if (!link) return;
                        e.preventDefault();
                        var apiUrl = link.href.replace('/panel/lembur', '/api/realtime/admin/lembur-data');
                        fetch(apiUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            })
                            .then(function(r) {
                                if (!r.ok) throw new Error('HTTP ' + r.status);
                                return r.json();
                            })
                            .then(function(data) {
                                var tbody = document.getElementById('lemburTableBody');
                                var pag = document.getElementById('lemburPagination');
                                if (tbody && data.html) {
                                    tbody.innerHTML = data.html;
                                    lastHtml = data.html;
                                }
                                if (pag && data.pagination) {
                                    pag.innerHTML = data.pagination;
                                    lastPagination = data.pagination;
                                }
                                window.history.pushState({}, '', link.href);
                                if (window.lucide) lucide.createIcons();
                            }).catch(function(err) { console.error('Lembur pagination fetch error:', err); });
                    });
                }

                fetchTableData();
            })();
        } catch (e) { console.warn('Realtime polling error:', e); }

        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
