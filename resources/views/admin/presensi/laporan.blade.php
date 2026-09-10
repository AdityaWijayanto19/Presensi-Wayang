@extends('layouts.admin.app')

@section('content')

    @section('page_title', 'Laporan Presensi')

    <x-admin.page-body>

        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">

                <form action="/presensi/cetaklaporan" method="POST">
                    @csrf

                    <div class="space-y-3">

                        {{-- Bulan --}}
                        <select name="bulan" id="bulan"
                            class="w-full rounded border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                                    {{ $namabulan[$i] }}
                                </option>
                            @endfor
                        </select>

                        {{-- Tahun --}}
                        @php
                            $tahunmulai = 2025;
                            $tahunskrg = date('Y');
                        @endphp
                        <select name="tahun" id="tahun"
                            class="w-full rounded border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Tahun</option>
                            @for ($tahun = $tahunmulai; $tahun <= $tahunskrg; $tahun++)
                                <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endfor
                        </select>

                        {{-- Unit Perusahaan --}}
                        <select name="unit" id="unit"
                            class="w-full rounded border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Pilih Unit Perusahaan</option>
                            @foreach ($unit as $u)
                                <option value="{{ $u->unit }}">{{ $u->perusahaan }}</option>
                            @endforeach
                        </select>

                        {{-- Karyawan --}}
                        <select name="nik" id="nik"
                            class="w-full rounded border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Pilih Karyawan</option>
                        </select>

                        {{-- Button Download --}}
                        <button type="submit" name="cetak"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2"/>
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"/>
                                <path d="M7 15a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2l0 -4"/>
                            </svg>
                            Download PDF
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </x-admin.page-body>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('unit').addEventListener('change', function () {
            var unit = this.value;

            fetch('/getkaryawanbyunit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new URLSearchParams({ unit: unit })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var nik = document.getElementById('nik');
                nik.innerHTML = '<option value="">Pilih Karyawan</option>';
                res.forEach(function (item) {
                    nik.innerHTML += '<option value="' + item.nik + '">' + item.nama_lengkap + '</option>';
                });
            });
        });
    });
</script>
@endpush
