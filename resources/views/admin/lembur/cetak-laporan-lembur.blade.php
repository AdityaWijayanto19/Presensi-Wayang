<form method="POST" action="/presensi/lembur/cetaklaporan" x-data="cetakLemburForm()"
    @submit="validasi() || $event.preventDefault()">
    @csrf

    <div class="grid grid-cols-12 gap-2">

        {{-- Bulan --}}
        <div class="col-span-12 sm:col-span-4">
            <x-admin.select name="bulan" id="cetakBulan" value="{{ date('m') }}"
                label="Bulan <span class='text-red-500'>*</span>">
                <option value="">Bulan</option>
                @php $namabulan = \App\Services\LaporanService::NAMA_BULAN; @endphp
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                        {{ $namabulan[$i] }}
                    </option>
                @endfor
            </x-admin.select>
        </div>

        {{-- Tahun --}}
        <div class="col-span-12 sm:col-span-4">
            <x-admin.select name="tahun" id="cetakTahun" value="{{ date('Y') }}"
                label="Tahun <span class='text-red-500'>*</span>">
                <option value="">Tahun</option>
                @for ($tahun = 2025; $tahun <= date('Y'); $tahun++)
                    <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                @endfor
            </x-admin.select>
        </div>

        {{-- Unit Perusahaan --}}
        <div class="col-span-12 sm:col-span-4">
            <x-admin.select name="unit" id="cetakUnit" searchable
                label="Perusahaan <span class='text-red-500'>*</span>">
                <option value="">Pilih Perusahaan</option>
                @foreach ($unitperusahaan as $u)
                    <option value="{{ $u->unit }}">{{ $u->perusahaan }}</option>
                @endforeach
            </x-admin.select>
        </div>

    </div>

    {{-- Cut-off Info --}}
    <div class="mb-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Periode Cut-off</label>
        <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
            <span x-text="cutoffText"></span>
        </div>
    </div>

    {{-- Pilih Karyawan --}}
    <div class="mb-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">
            Karyawan <span class="text-red-500">*</span>
            <span class="font-normal text-slate-400">— pilih minimal satu</span>
        </label>

        <div class="rounded-md border border-slate-200 overflow-hidden">
            <div class="flex flex-wrap items-center gap-3 px-3 py-2 bg-slate-50 border-b border-slate-200">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        :checked="allSelected" @change="toggleAll">
                    <span>Pilih Semua</span>
                </label>

                <span class="text-xs text-slate-500">
                    <span x-text="selected.length"></span> / <span x-text="daftar.length"></span> dipilih
                </span>

                <div class="ml-auto relative">
                    <input type="text" x-model="search" placeholder="Cari karyawan..."
                        class="w-44 sm:w-56 rounded-md border border-slate-300 text-xs pl-7 pr-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </div>
            </div>

            <div class="max-h-56 overflow-y-auto divide-y divide-slate-100">
                <template x-for="item in daftar" :key="item.nik">
                    <label class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer hover:bg-slate-50"
                        x-show="cocok(item)">
                        <input type="checkbox" name="niks[]" :value="item.nik" x-model="selected"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-slate-800 font-medium" x-text="item.nama_lengkap"></span>
                        <span class="text-slate-400" x-text="item.nik"></span>
                    </label>
                </template>

                <div x-show="loading" class="px-3 py-3 text-xs text-slate-400 text-center">
                    Memuat daftar karyawan...
                </div>

                <div x-show="!loading && daftar.length === 0" class="px-3 py-3 text-xs text-slate-400 text-center">
                    Pilih perusahaan terlebih dahulu
                </div>

                <div x-show="!loading && daftar.length > 0 && !adaHasilPencarian"
                    class="px-3 py-3 text-xs text-slate-400 text-center">
                    Karyawan tidak ditemukan
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="mt-2 flex gap-2">
        <x-admin.button variant="secondary" icon="eye" type="submit"
            formaction="/presensi/lembur/previewlaporan" formtarget="_blank">Preview</x-admin.button>
        <x-admin.button variant="primary" icon="download" type="submit"
            class="flex-1">Download PDF</x-admin.button>
    </div>

</form>

@push('myscript')
<script>
    function cetakLemburForm() {
        return {
            bulan: '{{ date("m") }}',
            tahun: '{{ date("Y") }}',
            unit: '',
            daftar: [],
            selected: [],
            search: '',
            loading: false,

            get cutoffText() {
                const namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const b = parseInt(this.bulan) || 1;
                const t = parseInt(this.tahun) || 2025;

                let startMonth = b - 1;
                let startYear = t;
                if (startMonth === 0) {
                    startMonth = 12;
                    startYear = t - 1;
                }

                return `21 ${namaBulan[startMonth]} ${startYear} - 20 ${namaBulan[b]} ${t}`;
            },

            cocok(item) {
                if (!this.search) return true;
                const q = this.search.toLowerCase();
                return item.nama_lengkap.toLowerCase().includes(q) ||
                    String(item.nik).toLowerCase().includes(q);
            },

            get adaHasilPencarian() {
                return this.daftar.some(item => this.cocok(item));
            },

            get allSelected() {
                return this.daftar.length > 0 && this.selected.length === this.daftar.length;
            },

            toggleAll() {
                this.selected = this.allSelected ? [] : this.daftar.map(item => item.nik);
            },

            validasi() {
                if (!this.bulan || !this.tahun || !this.unit) {
                    Swal.fire({
                        title: 'Filter belum lengkap',
                        text: 'Harap pilih bulan, tahun, dan perusahaan terlebih dahulu',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6',
                        backdrop: false
                    });
                    return false;
                }

                if (this.selected.length === 0) {
                    Swal.fire({
                        title: 'Karyawan belum dipilih',
                        text: 'Pilih minimal satu karyawan',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6',
                        backdrop: false
                    });
                    return false;
                }

                return true;
            },

            loadKaryawan(unit) {
                this.unit = unit || '';
                this.selected = [];

                if (!unit) {
                    this.daftar = [];
                    return;
                }

                this.loading = true;
                fetch('/getkaryawanbyunit', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: new URLSearchParams({ unit: unit })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        this.daftar = res.map(function(item) {
                            return { nik: item.nik, nama_lengkap: item.nama_lengkap };
                        });
                    }.bind(this))
                    .catch(function() { this.daftar = []; }.bind(this))
                    .finally(function() { this.loading = false; }.bind(this));
            },

            init() {
                const self = this;

                const bulanEl = document.getElementById('cetakBulan');
                const tahunEl = document.getElementById('cetakTahun');
                const unitEl = document.getElementById('cetakUnit');

                if (bulanEl && bulanEl.value) this.bulan = bulanEl.value;
                if (tahunEl && tahunEl.value) this.tahun = tahunEl.value;

                [
                    [bulanEl, 'bulan'],
                    [tahunEl, 'tahun']
                ].forEach(function(pair) {
                    const el = pair[0];
                    if (!el) return;
                    ['change', 'input'].forEach(function(evt) {
                        el.addEventListener(evt, function() {
                            self[pair[1]] = this.value;
                        });
                    });
                });

                if (unitEl) {
                    unitEl.addEventListener('change', function() {
                        self.loadKaryawan(this.value);
                    });

                    if (unitEl.value) this.loadKaryawan(unitEl.value);
                }
            }
        };
    }
</script>
@endpush
