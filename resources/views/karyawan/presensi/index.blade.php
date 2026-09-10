@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Histori Presensi</div>
        <div class="right"></div>
    </div>

@endsection

@section('content')

    {{-- Filter Histori --}}
    <div class="flex mt-[70px]">
        <div class="w-full px-2">

            <div class="flex flex-wrap -mx-2">
                <div class="w-full px-2">
                    <div class="form-group">
                        <select name="bulan" id="bulan" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Pilih Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                                    {{ $namabulan[$i] }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap -mx-2 mt-2">
                <div class="w-full px-2">
                    <div class="form-group">
                        <select name="tahun" id="tahun" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
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
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap -mx-2 mt-2">
                <div class="w-full px-2">
                    <div class="form-group">
                        <button class="btn btn-primary w-full" id="getdata">
                            <i data-lucide="search"></i>
                            Cari Data Presensi
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Hasil Histori --}}
    <div class="flex">
        <div class="w-full px-2" id="showhistori"></div>
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

        document.getElementById('getdata').addEventListener('click', function () {
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
        });

    });

</script>

@endpush
