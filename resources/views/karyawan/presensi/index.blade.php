@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Histori Presensi</div>
        <div class="right"></div>
    </div>

@endsection

@section('content')

    <div class="section">

        {{-- Filter Histori --}}
        <div class="mt-[70px]">
            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-2">
                <select name="bulan" id="bulan"
                    class="w-full min-w-0 h-10 sm:flex-1 rounded-md border border-slate-300 px-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Pilih Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                            {{ $namabulan[$i] }}
                        </option>
                    @endfor
                </select>

                <select name="tahun" id="tahun"
                    class="w-full min-w-0 h-10 sm:w-36 rounded-md border border-slate-300 px-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Pilih Tahun</option>
                    @php
                        $tahunmulai = 2025;
                        $tahunskrg = date('Y');
                    @endphp
                    @for ($tahun = $tahunmulai; $tahun <= $tahunskrg; $tahun++)
                        <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endfor
                </select>

                <button class="btn btn-primary w-full col-span-2 sm:w-auto sm:shrink-0 whitespace-nowrap" id="getdata">
                    <i data-lucide="search"></i>
                    Cari Data Presensi
                </button>
            </div>
        </div>

        {{-- Hasil Histori --}}
        <div class="mt-4" id="showhistori">
            @include('karyawan.presensi._rows', ['histori' => $histori])
        </div>

    </div>

@endsection

@push('myscript')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        document.addEventListener('click', function (e) {
            var img = e.target.closest('.foto-histori');
            if (img) {
                var foto = img.getAttribute('src');
                Swal.fire({
                    html: '<img src="' + foto + '" style="width:100%;height:auto;border-radius:12px;display:block;">',
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '390px',
                    padding: '10px',
                    background: 'transparent'
                });
            }
        });

        function loadHistori() {
            var bulan = document.getElementById('bulan').value;
            var tahun = document.getElementById('tahun').value;

            fetch('/gethistori', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: '_token={{ csrf_token() }}&bulan=' + encodeURIComponent(bulan) + '&tahun=' + encodeURIComponent(tahun)
            })
            .then(function (response) { return response.text(); })
            .then(function (respond) {
                document.getElementById('showhistori').innerHTML = respond;
            });
        }

        document.getElementById('getdata').addEventListener('click', loadHistori);
        document.getElementById('bulan').addEventListener('change', loadHistori);
        document.getElementById('tahun').addEventListener('change', loadHistori);

    });

</script>

@endpush
