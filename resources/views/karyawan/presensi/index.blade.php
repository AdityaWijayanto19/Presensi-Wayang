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
                        <select name="bulan" id="bulan" class="form-control">
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
                        <select name="tahun" id="tahun" class="form-control">
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

    $(function () {

        $(document).on('click', '.foto-histori', function () {
            let foto = $(this).attr('src');
            Swal.fire({
                html: `<img src="${foto}" style="width:100%;height:auto;border-radius:12px;display:block;">`,
                showConfirmButton: false,
                showCloseButton: true,
                width: '390px',
                padding: '10px',
                background: 'transparent'
            });
        });

        $('#getdata').click(function () {
            var bulan = $('#bulan').val();
            var tahun = $('#tahun').val();

            $.ajax({
                type: 'POST',
                url: '/gethistori',
                data: {
                    _token: "{{ csrf_token() }}",
                    bulan: bulan,
                    tahun: tahun
                },
                cache: false,
                success: function (respond) {
                    $("#showhistori").html(respond);
                }
            });
        });

    });

</script>

@endpush
