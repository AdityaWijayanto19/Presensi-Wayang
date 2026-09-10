@extends('layouts.admin.app')

@section('content')

    @section('page_title', 'Laporan Presensi')

    <x-admin.page-body>

        <div class="max-w-xl mx-auto">
            <x-admin.card>

                <div class="p-4">
                    <form action="/presensi/cetaklaporan" method="POST">
                        @csrf

                        <div class="space-y-0">

                            {{-- Bulan --}}
                            <x-admin.select name="bulan" id="bulan" label="Bulan <span class='text-red-500'>*</span>" required>
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
                            <x-admin.select name="tahun" id="tahun" label="Tahun <span class='text-red-500'>*</span>" required>
                                <option value="">Tahun</option>
                                @for ($tahun = $tahunmulai; $tahun <= $tahunskrg; $tahun++)
                                    <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endfor
                            </x-admin.select>

                            {{-- Unit Perusahaan --}}
                            <x-admin.select name="unit" id="unit" label="Unit Perusahaan <span class='text-red-500'>*</span>" searchable required>
                                <option value="">Pilih Unit Perusahaan</option>
                                @foreach ($unit as $u)
                                    <option value="{{ $u->unit }}">{{ $u->perusahaan }}</option>
                                @endforeach
                            </x-admin.select>

                            {{-- Karyawan --}}
                            <x-admin.select name="nik" id="nik" label="Karyawan <span class='text-red-500'>*</span>" searchable required>
                                <option value="">Pilih Karyawan</option>
                            </x-admin.select>

                            {{-- Buttons --}}
                            <div class="mt-2 flex gap-2">
                                <x-admin.button variant="secondary" icon="eye" type="submit" formaction="/presensi/previewlaporan" formtarget="_blank">Preview</x-admin.button>
                                <x-admin.button variant="primary" icon="download" type="submit" name="cetak" class="flex-1">Download PDF</x-admin.button>
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
                nik.dispatchEvent(new CustomEvent('options-updated', { bubbles: true }));
            });
        });

        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
