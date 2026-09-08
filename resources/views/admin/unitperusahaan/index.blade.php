@extends('layouts.admin.app')

@section('content')

<x-app.page-header title="Data Unit Perusahaan" pretitle="WAG - Presensi Digital" />

<x-app.page-body>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">
        <div class="p-3">

            {{-- =====================================================
                 ALERT
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2">

                <div class="col-span-12">

                    @if (Session::get('success'))

                        <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-md text-sm">
                            {{ Session::get('success') }}
                        </div>

                    @endif

                    @if (Session::get('error'))

                        <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-md text-sm">
                            {{ Session::get('error') }}
                        </div>

                    @endif

                </div>

            </div>



            {{-- =====================================================
                 BUTTON TAMBAH DATA
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2">

                <div class="col-span-12">

                    @can('unit-create')
                    <a href="#"
                       class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                       id="btnTambahunitperusahaan">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="18"
                             height="18"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">

                            <path stroke="none"
                                  d="M0 0h24v24H0z"
                                  fill="none"/>

                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>

                            <path d="M16 19h6"/>

                            <path d="M19 16v6"/>

                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4"/>

                        </svg>

                        Tambah Data Unit Perusahaan

                    </a>
                    @endcan

                </div>

            </div>



            {{-- =====================================================
                 TABEL DATA
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2 mt-2">

                <div class="col-span-12">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 border border-slate-200">

                            <thead class="bg-slate-50">

                                <tr>

                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Perusahaan</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>

                                </tr>

                            </thead>

                            <tbody class="bg-white divide-y divide-slate-200">

                                @foreach ($unitperusahaan as $u)

                                    <tr class="hover:bg-slate-50">

                                        <td class="px-3 py-2 text-sm text-slate-700">{{ $loop->iteration }}</td>

                                        <td class="px-3 py-2 text-sm text-slate-700">{{ $u->unit }}</td>

                                        <td class="px-3 py-2 text-sm text-slate-700">{{ $u->perusahaan }}</td>

                                        <td class="px-3 py-2 text-sm text-slate-700">
                                            {{ $u->jam_masuk ? date('H:i', strtotime($u->jam_masuk)) : '-' }}
                                        </td>

                                        <td class="px-3 py-2 text-sm">

                                            {{-- Edit --}}
                                            @can('unit-edit')
                                            <a href="#"
                                               class="edit bg-cyan-500 text-white px-2 py-1 rounded-md hover:bg-cyan-600 transition-colors text-xs font-medium inline-flex items-center gap-1"
                                               unit="{{ $u->unit }}">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     width="18"
                                                     height="18"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="2"
                                                     stroke-linecap="round"
                                                     stroke-linejoin="round">

                                                    <path stroke="none"
                                                          d="M0 0h24v24H0z"
                                                          fill="none"/>

                                                    <path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3"/>

                                                    <path d="M16 5l3 3"/>

                                                    <path d="M9 7.07a7 7 0 0 0 1 13.93a7 7 0 0 0 6.929 -6"/>

                                                </svg>

                                            </a>
                                            @endcan

                                            {{-- Delete --}}
                                            @can('unit-delete')
                                            <form action="/unitperusahaan/{{ $u->unit }}/delete"
                                                  method="POST"
                                                  class="inline">

                                                @csrf

                                                <button type="submit"
                                                        class="delete-confirm bg-red-600 text-white px-2 py-1 rounded-md hover:bg-red-700 transition-colors text-xs font-medium inline-flex items-center gap-1">

                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         width="18"
                                                         height="18"
                                                         viewBox="0 0 24 24"
                                                         fill="none"
                                                         stroke="currentColor"
                                                         stroke-width="2"
                                                         stroke-linecap="round"
                                                         stroke-linejoin="round">

                                                        <path stroke="none"
                                                              d="M0 0h24v24H0z"
                                                              fill="none"/>

                                                        <line x1="4" y1="7" x2="20" y2="7"/>

                                                        <line x1="10" y1="11" x2="10" y2="17"/>

                                                        <line x1="14" y1="11" x2="14" y2="17"/>

                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>

                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>

                                                    </svg>

                                                </button>

                                            </form>
                                            @endcan

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- =====================================================
         MODAL TAMBAH DATA UNIT PERUSAHAAN
    ===================================================== --}}

    <x-app.modal id="modal-inputunitperusahaan" title="Tambah Data Unit Perusahaan">

        <form action="/unitperusahaan/store"
              method="POST"
              id="formUnitperusahaan"
              enctype="multipart/form-data">

            @csrf


            {{-- =====================================================
                 NAMA UNIT
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2">

                <div class="col-span-12">

                    <div class="relative mb-2">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                             <svg xmlns="http://www.w3.org/2000/svg"
                                 width="18"
                                 height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                 <path stroke="none"
                                       d="M0 0h24v24H0z"
                                       fill="none"/>

                                 <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8"/>

                                 <path d="M13 7l0 .01"/>

                                 <path d="M17 7l0 .01"/>

                                 <path d="M17 11l0 .01"/>

                                 <path d="M17 15l0 .01"/>

                             </svg>

                        </span>

                        <input type="text"
                               name="unit"
                               id="unit"
                               class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Nama Unit"
                               autocomplete="off">

                    </div>

                </div>

            </div>



            {{-- =====================================================
                 NAMA PERUSAHAAN
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2">

                <div class="col-span-12">

                    <div class="relative mb-2">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                             <svg xmlns="http://www.w3.org/2000/svg"
                                 width="18"
                                 height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                 <path stroke="none"
                                       d="M0 0h24v24H0z"
                                       fill="none"/>

                                 <path d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15"/>

                                 <path d="M16 8h2c1 0 2 1 2 2v11"/>

                                 <path d="M3 21h18"/>

                                 <path d="M10 12v.01"/>

                                 <path d="M10 16v.01"/>

                                 <path d="M10 8v.01"/>

                                 <path d="M7 12v.01"/>

                                 <path d="M7 16v.01"/>

                                 <path d="M7 8v.01"/>

                                 <path d="M17 12v.01"/>

                                 <path d="M17 16v.01"/>

                             </svg>

                        </span>

                        <input type="text"
                               name="perusahaan"
                               id="perusahaan"
                               class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Nama Perusahaan"
                               autocomplete="off">

                    </div>

                </div>

            </div>



            {{-- =====================================================
                 JAM MASUK
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2">

                <div class="col-span-12">

                    <div class="relative mb-2">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                             <svg xmlns="http://www.w3.org/2000/svg"
                                 width="18"
                                 height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                 <path stroke="none"
                                       d="M0 0h24v24H0z"
                                       fill="none"/>

                                 <path d="M20.975 11.33a9 9 0 1 0 -5.717 9.06"/>

                                <path d="M12 7v5l2 2"/>

                                <path d="M19 22v.01"/>

                                <path d="M19 19a2.003 2.003 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483"/>

                            </svg>

                        </span>

                        <input type="time"
                               name="jam_masuk"
                               id="jam_masuk"
                               class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               required>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                 BUTTON SIMPAN
            ===================================================== --}}

            <div class="grid grid-cols-12 gap-2 mt-2">

                <div class="col-span-12">

                    <div class="space-y-1">

                        <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                             <svg xmlns="http://www.w3.org/2000/svg"
                                 width="18"
                                 height="18"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <path stroke="none"
                                      d="M0 0h24v24H0z"
                                      fill="none"/>

                                <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12"/>

                                <path d="M13 8l3 3l-3 3"/>

                                <path d="M16 11h-8"/>

                            </svg>

                            Simpan

                        </button>

                    </div>

                </div>

            </div>

        </form>

    </x-app.modal>

    {{-- =====================================================
         MODAL EDIT DATA UNIT PERUSAHAAN
    ===================================================== --}}

    <x-app.modal id="modal-editunitperusahaan" title="Edit Data Unit Perusahaan">

        <div id="loadeditform">

            {{-- Form Edit akan dimuat melalui AJAX --}}

        </div>

    </x-app.modal>

</x-app.page-body>

@endsection



@push('myscript')

<script>

$(function () {

    /*
    |--------------------------------------------------------------------------
    | Modal Tambah Data
    |--------------------------------------------------------------------------
    */

    $("#btnTambahunitperusahaan").click(function () {

        window.dispatchEvent(new CustomEvent('open-modal-modal-inputunitperusahaan'));

    });



    /*
    |--------------------------------------------------------------------------
    | Modal Edit Data
    |--------------------------------------------------------------------------
    */

    $(".edit").click(function () {

        let unit = $(this).attr("unit");

        $.ajax({

            type: "POST",

            url: "/unitperusahaan/edit",

            cache: false,

            data: {

                _token: "{{ csrf_token() }}",

                unit: unit

            },

            success: function (respond) {

                $("#loadeditform").html(respond);

            }

        });

        window.dispatchEvent(new CustomEvent('open-modal-modal-editunitperusahaan'));

    });



    /*
    |--------------------------------------------------------------------------
    | Konfirmasi Hapus Data
    |--------------------------------------------------------------------------
    */

    $(".delete-confirm").click(function (e) {

        let form = $(this).closest("form");

        e.preventDefault();

        Swal.fire({

            title: "Yakin data ini akan dihapus?",

            text: "Data yang sudah dihapus tidak bisa dikembalikan!",

            icon: "warning",

            showCancelButton: true,

            confirmButtonColor: "#3085d6",

            cancelButtonColor: "#d33",

            confirmButtonText: "Hapus Data",

            backdrop: false

        }).then((result) => {

            if (result.isConfirmed) {

                form.submit();

            }

        });

    });



    /*
    |--------------------------------------------------------------------------
    | Validasi Form Tambah Unit Perusahaan
    |--------------------------------------------------------------------------
    */

    $("#formUnitperusahaan").submit(function () {

        let unit = $("#unit").val();

        let perusahaan = $("#perusahaan").val();

        if (unit == "") {

            Swal.fire({

                title: "Oops!",

                text: "Unit tidak boleh kosong",

                icon: "warning",

                confirmButtonText: "OK",

                backdrop: false

            }).then(() => {

                $("#unit").focus();

            });

            return false;

        }

    });

});

</script>

@endpush
