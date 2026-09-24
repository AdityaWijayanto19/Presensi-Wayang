@extends('layouts.admin.app')

@section('content')
@section('page_title', 'Data Cuti Karyawan')

    <x-admin.page-body>

        <x-admin.card>

            <div class="p-3">

                {{-- ================================================== --}}
                {{-- Filter --}}
                {{-- ================================================== --}}
                <form action="/panel/cuti" method="GET">

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
                            <x-admin.input
                                name="nik"
                                placeholder="Cari NIK"
                                value="{{ Request('nik') }}"
                                autocomplete="off"
                            />
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.select name="unit" placeholder="Semua Unit" :autoSubmit="true">
                                @foreach ($unitperusahaan as $u)
                                    <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>{{ $u->perusahaan }}</option>
                                @endforeach
                            </x-admin.select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <x-admin.input
                                type="text"
                                name="tanggal"
                                id="tanggal"
                                placeholder="Filter Tanggal"
                                value="{{ Request('tanggal') }}"
                                autocomplete="off"
                                icon="calendar"
                            />
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <x-admin.button variant="primary" icon="search" type="submit" block>Cari</x-admin.button>
                        </div>
                    </div>

                </form>

                {{-- ================================================== --}}
                {{-- Tabel Data Cuti --}}
                {{-- ================================================== --}}
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No.</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Tanggal Cuti</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="cutiTableBody" class="divide-y divide-slate-200">
                            @include('admin.cuti._rows')
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div id="cutiPagination" class="mt-2">
                    {{ $datacuti->appends(request()->all())->links() }}
                </div>

            </div>

        </x-admin.card>

    </x-admin.page-body>

    {{-- ================================================== --}}
    {{-- Modal Edit Cuti --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-editcuti" title="Edit Data Cuti">
        <form id="formEditCuti" method="POST">
            @csrf
            <input type="hidden" name="cuti_id" id="edit_cuti_id">

            <x-admin.select name="durasi_hari" id="edit_durasi_hari" label="Durasi Cuti <span class='text-red-500'>*</span>" required>
                <option value="1">1 Hari</option>
                <option value="2">2 Hari</option>
                <option value="3">3 Hari</option>
            </x-admin.select>

            <div class="mb-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Cuti <span class="text-red-500">*</span></label>
                <div id="editTanggalCutiFields" class="space-y-2"></div>
            </div>

            <div class="mb-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Keterangan</label>
                <textarea name="keterangan" id="edit_keterangan" rows="3" maxlength="500"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Keterangan (opsional)"></textarea>
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

            try {
                var tanggalInput = document.querySelector('form[action="/panel/cuti"] input[name="tanggal"]');
                if (tanggalInput) tanggalInput.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            } catch (e) { console.warn('Filter init error:', e); }

            // Dynamic tanggal fields in edit modal
            var editDurasiSelect = document.getElementById('edit_durasi_hari');
            var editTanggalContainer = document.getElementById('editTanggalCutiFields');
            var editPickers = [];
            var editUsedDates = [];

            function destroyEditPickers() {
                editPickers.forEach(function(p) { try { p.destroy(); } catch (e) {} });
                editPickers = [];
            }

            function renderEditTanggalFields(count, values) {
                destroyEditPickers();
                editTanggalContainer.innerHTML = '';
                values = values || [];
                for (var i = 0; i < count; i++) {
                    var wrap = document.createElement('div');
                    wrap.innerHTML = '<input type="text" name="tanggal_cuti[]" class="edit-cuti-tanggal w-full px-3 py-2 text-sm border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Pilih Tanggal ' + (i + 1) + '" required value="' + (values[i] || '') + '">';
                    editTanggalContainer.appendChild(wrap);
                    var input = wrap.querySelector('input');
                    var fp = flatpickr(input, {
                        locale: "id",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j F Y",
                        allowInput: true,
                        disableMobile: true,
                        disable: (editUsedDates || []).slice()
                    });
                    editPickers.push(fp);
                }
            }

            editDurasiSelect.addEventListener('change', function() {
                var count = parseInt(this.value, 10) || 1;
                var existing = [];
                editTanggalContainer.querySelectorAll('.edit-cuti-tanggal').forEach(function(inp) {
                    existing.push(inp._flatpickr ? inp._flatpickr.input.value : inp.value);
                });
                renderEditTanggalFields(count, existing);
            });

            // Edit Cuti Modal
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-cuti');
                if (btn) {
                    var id = btn.dataset.id;
                    var durasi = btn.dataset.durasi;
                    var tanggal = [];
                    try { tanggal = JSON.parse(btn.dataset.tanggal || '[]'); } catch (err) {}
                    try { editUsedDates = JSON.parse(btn.dataset.usedDates || '[]'); } catch (err) { editUsedDates = []; }
                    var keterangan = btn.dataset.keterangan || '';

                    document.getElementById('edit_cuti_id').value = id;
                    window.dispatchEvent(new CustomEvent('set-value', {
                        detail: { name: 'durasi_hari', value: String(durasi) },
                        bubbles: true
                    }));
                    document.getElementById('edit_keterangan').value = keterangan;
                    document.getElementById('formEditCuti').setAttribute('action', '/cuti/' + id + '/update');

                    var count = parseInt(durasi, 10) || 1;
                    renderEditTanggalFields(count, tanggal);

                    window.dispatchEvent(new CustomEvent('open-modal-modal-editcuti'));
                }
            });

            // Submit validation edit cuti
            document.getElementById('formEditCuti').addEventListener('submit', function(e) {
                var vals = [];
                editTanggalContainer.querySelectorAll('.edit-cuti-tanggal').forEach(function(inp) {
                    var v = inp._flatpickr ? inp._flatpickr.input.value : inp.value;
                    if (v) vals.push(v);
                });
                if (new Set(vals).size !== vals.length) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', text: 'Tanggal cuti tidak boleh sama', confirmButtonColor: '#3085d6' });
                    return;
                }
                var dupes = vals.filter(function(t) { return (editUsedDates || []).indexOf(t) !== -1; });
                if (dupes.length) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', text: 'Tanggal ' + dupes.join(', ') + ' sudah pernah dipilih cuti lain.', confirmButtonColor: '#3085d6' });
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
                        text: "Kuota cuti karyawan akan dikembalikan!",
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
    </script>
@endpush
