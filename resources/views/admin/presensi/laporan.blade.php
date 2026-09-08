@extends('layouts.admin.app')

@section('content')

    <x-app.page-header title="Laporan Presensi" pretitle="WAG - Presensi Digital" />

    <x-app.page-body>

        <div class="flex justify-center">

            <div class="col-span-12 lg:col-span-10">

                <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">

                    <div class="p-3">

                        <form action="/presensi/cetaklaporan" method="POST">

                            @csrf

                            {{-- ================================================== --}}
                            {{-- Bulan --}}
                            {{-- ================================================== --}}
                            <div class="flex justify-center">

                                <div class="col-span-12">

                                    <div class="space-y-1">

                                        <select name="bulan"
                                            id="bulan"
                                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                            <option value="">
                                                Bulan
                                            </option>

                                            @for ($i = 1; $i <= 12; $i++)

                                                <option value="{{ $i }}"
                                                    {{ date('m') == $i ? 'selected' : '' }}>

                                                    {{ $namabulan[$i] }}

                                                </option>

                                            @endfor

                                        </select>

                                    </div>

                                </div>

                            </div>

                            {{-- ================================================== --}}
                            {{-- Tahun --}}
                            {{-- ================================================== --}}
                            <div class="grid grid-cols-12 gap-2 mt-2">

                                <div class="col-span-12">

                                    <div class="space-y-1">

                                        <select name="tahun"
                                            id="tahun"
                                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                            <option value="">
                                                Tahun
                                            </option>

                                            @php
                                                $tahunmulai = 2025;
                                                $tahunskrg = date('Y');
                                            @endphp

                                            @for ($tahun = $tahunmulai; $tahun <= $tahunskrg; $tahun++)

                                                <option value="{{ $tahun }}"
                                                    {{ date('Y') == $tahun ? 'selected' : '' }}>

                                                    {{ $tahun }}

                                                </option>

                                            @endfor

                                        </select>

                                    </div>

                                </div>

                            </div>

                            {{-- ================================================== --}}
                            {{-- Unit Perusahaan --}}
                            {{-- ================================================== --}}
                            <div class="grid grid-cols-12 gap-2 mt-2">

                                <div class="col-span-12">

                                    <div class="space-y-1">

                                        <select name="unit"
                                            id="unit"
                                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                            <option value="">
                                                Pilih Unit Perusahaan
                                            </option>

                                            @foreach ($unit as $u)

                                                <option value="{{ $u->unit }}">
                                                    {{ $u->perusahaan }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                </div>

                            </div>

                            {{-- ================================================== --}}
                            {{-- Karyawan --}}
                            {{-- ================================================== --}}
                            <div class="grid grid-cols-12 gap-2 mt-2">

                                <div class="col-span-12">

                                    <div class="space-y-1">

                                        <select name="nik"
                                            id="nik"
                                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                            <option value="">
                                                Pilih Karyawan
                                            </option>

                                        </select>

                                    </div>

                                </div>

                            </div>

                            {{-- ================================================== --}}
                            {{-- Button Download --}}
                            {{-- ================================================== --}}
                            <div class="grid grid-cols-12 gap-2 mt-2">

                                <div class="col-span-12">

                                    <div class="space-y-1">

                                        <button type="submit"
                                            name="cetak"
                                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18"
                                                height="18"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="w-4 h-4">

                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                <path d="M7 15a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2l0 -4" />

                                            </svg>

                                            Download PDF!

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </x-app.page-body>

@endsection

@push('myscript')

<script>

    $(function () {

        // ==================================================
        // Get Karyawan Berdasarkan Unit
        // ==================================================
        $("#unit").change(function () {

            var unit = $(this).val();

            $.ajax({

                type: 'POST',

                url: '/getkaryawanbyunit',

                data: {
                    _token: "{{ csrf_token() }}",
                    unit: unit
                },

                cache: false,

                success: function (res) {

                    $("#nik").empty();

                    $("#nik").append(
                        '<option value="">Pilih Karyawan</option>'
                    );

                    $.each(res, function (index, item) {

                        $("#nik").append(
                            '<option value="' + item.nik + '">' +
                            item.nama_lengkap +
                            '</option>'
                        );

                    });

                }

            });

        });

    });

</script>

@endpush
