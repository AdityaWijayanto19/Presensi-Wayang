@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Laporan Presensi')

<x-admin.page-body>

    <div class="max-w-xl mx-auto">
        <x-admin.card>

            <div class="p-4">
                <form action="/presensi/cetaklaporan" method="POST" x-data="laporanForm()">
                    @csrf

                    <div class="space-y-0">

                        {{-- Bulan --}}
                        <x-admin.select name="bulan" id="bulan" label="Bulan <span class='text-red-500'>*</span>"
                            required>
                            <option value="">Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                                    {{ $namabulan[$i] }}
                                </option>
                            @endfor
                        </x-admin.select>

                        {{-- Tahun --}}
                        @php
                            $tahunmulai = 2025;
                            $tahunskrg = date('Y');
                        @endphp
                        <x-admin.select name="tahun" id="tahun" label="Tahun <span class='text-red-500'>*</span>"
                            required>
                            <option value="">Tahun</option>
                            @for ($tahun = $tahunmulai; $tahun <= $tahunskrg; $tahun++)
                                <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endfor
                        </x-admin.select>

                        {{-- Cut-off Info --}}
                        <div class="mb-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Periode Cut-off</label>
                            <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                @php
                                    $curMonth = (int) date('m');
                                    $curYear = (int) date('Y');
                                    $startMonth = $curMonth - 1;
                                    $startYear = $curYear;
                                    if ($startMonth === 0) {
                                        $startMonth = 12;
                                        $startYear = $curYear - 1;
                                    }
                                @endphp
                                <span x-text="cutoffText">21 {{ $namabulan[$startMonth] }} {{ $startYear }} - 20 {{ $namabulan[$curMonth] }} {{ $curYear }}</span>
                            </div>
                        </div>

                        {{-- Tipe Export --}}
                        <div class="mb-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Tipe Export <span class='text-red-500'>*</span></label>
                            <div class="flex gap-4 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipe_export" value="perusahaan" x-model="tipeExport"
                                        class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Per Perusahaan</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipe_export" value="karyawan" x-model="tipeExport"
                                        class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Per Karyawan</span>
                                </label>
                            </div>
                        </div>

                        {{-- Unit Perusahaan --}}
                        <x-admin.select name="unit" id="unit"
                            label="Unit Perusahaan <span class='text-red-500'>*</span>" searchable required>
                            <option value="">Pilih Unit Perusahaan</option>
                            @foreach ($unit as $u)
                                <option value="{{ $u->unit }}">{{ $u->perusahaan }}</option>
                            @endforeach
                        </x-admin.select>

                        {{-- Karyawan (conditional) --}}
                        <div x-show="tipeExport === 'karyawan'" x-transition x-cloak>
                            <x-admin.select name="nik" id="nik"
                                label="Karyawan <span class='text-red-500'>*</span>" searchable
                                :required="true">
                                <option value="">Pilih Karyawan</option>
                            </x-admin.select>
                        </div>

                        {{-- Buttons --}}
                        <div class="mt-2 flex gap-2">
                            <x-admin.button variant="secondary" icon="eye" type="submit"
                                formaction="/presensi/previewlaporan" formtarget="_blank">Preview</x-admin.button>
                            <x-admin.button variant="primary" icon="download" type="submit" name="cetak"
                                class="flex-1">Download PDF</x-admin.button>
                        </div>

                    </div>

                </form>
            </div>

        </x-admin.card>
    </div>

</x-admin.page-body>

@endsection

@push('myscript')
<script>
    function laporanForm() {
        return {
            tipeExport: 'perusahaan',
            bulan: '{{ date("m") }}',
            tahun: '{{ date("Y") }}',

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

            init() {
                const self = this;
                const bulanInput = document.querySelector('input[name="bulan"]');
                const tahunInput = document.querySelector('input[name="tahun"]');
                if (bulanInput) {
                    bulanInput.addEventListener('change', function() { self.bulan = this.value; });
                    bulanInput.addEventListener('input', function() { self.bulan = this.value; });
                }
                if (tahunInput) {
                    tahunInput.addEventListener('change', function() { self.tahun = this.value; });
                    tahunInput.addEventListener('input', function() { self.tahun = this.value; });
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('unit').addEventListener('change', function() {
            var unit = this.value;

            fetch('/getkaryawanbyunit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({
                        unit: unit
                    })
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    var opts = [{
                        value: '',
                        label: 'Pilih Karyawan'
                    }];
                    res.forEach(function(item) {
                        opts.push({
                            value: item.nik,
                            label: item.nama_lengkap
                        });
                    });
                    window.dispatchEvent(
                        new CustomEvent('options-updated', {
                            detail: {
                                name: 'nik',
                                options: opts
                            }
                        })
                    );
                });
        });

        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
