<?php

// ==================================================
// Menghitung Selisih Jam Kerja
// ==================================================
function selisih($jam_in, $jam_out)
{
    $awal = strtotime($jam_in);
    $akhir = strtotime($jam_out);

    $selisih = $akhir - $awal;

    $jam = floor($selisih / 3600);
    $menit = floor(($selisih % 3600) / 60);

    return $jam . " Jam " . $menit . " Menit";
}

?>

{{-- ================================================== --}}
{{-- Data Presensi --}}
{{-- ================================================== --}}
@if ($presensi->count() > 0)

    @foreach ($presensi as $p)

        @php
            $foto_in = Storage::url('uploads/absensi/' . $p->foto_in);
            $foto_out = Storage::url('uploads/absensi/' . $p->foto_out);
        @endphp

        <tr>

            <td>{{ $loop->iteration }}</td>

            <td>{{ $p->nik }}</td>

            <td>{{ $p->nama_lengkap }}</td>

            <td>{{ $p->unitkerja }}</td>

            <td>{{ $p->jam_in }}</td>

            <td>

                <img src="{{ url($foto_in) }}"
                    class="avatar foto-monitoring"
                    style="cursor:pointer">

            </td>

            <td>

                {!! $p->jam_out != null
                    ? $p->jam_out
                    : '<span class="badge bg-warning">Belum Presensi</span>' !!}

            </td>

            <td>

                @if ($p->jam_out != null)

                    <img src="{{ url($foto_out) }}"
                        class="avatar foto-monitoring"
                        style="cursor:pointer">

                @else

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-hourglass">

                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M6.5 7h11" />
                        <path d="M6.5 17h11" />
                        <path d="M6 20v-2a6 6 0 1 1 12 0v2a1 1 0 0 1 -1 1h-10a1 1 0 0 1 -1 -1" />
                        <path d="M6 4v2a6 6 0 1 0 12 0v-2a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1" />

                    </svg>

                @endif

            </td>

            <td>

                @if ($p->terlambat > 0)

                    <span class="badge bg-danger">
                        Terlambat {{ $p->terlambat }} Menit
                    </span>

                @else

                    <span class="badge bg-success">
                        Tepat Waktu
                    </span>

                @endif

            </td>

            <td style="min-width: 100px;">

                <div class="d-flex flex-column gap-1">

                    <a href="#"
                        class="btn btn-sm btn-primary tampilkanpetamasuk"
                        id="{{ $p->id }}">

                        Masuk

                    </a>

                    @if ($p->lokasi_out != null)

                        <a href="#"
                            class="btn btn-sm btn-primary tampilkanpetapulang"
                            id="{{ $p->id }}">

                            Pulang

                        </a>

                    @endif

                    @can('presensi-edit')
                    <a href="#"
                        class="btn btn-sm btn-info edit-presensi"
                        data-id="{{ $p->id }}"
                        data-jam_in="{{ $p->jam_in }}"
                        data-jam_out="{{ $p->jam_out }}">

                        Edit

                    </a>
                    @endcan

                </div>

            </td>

            <td>

                {{ $p->durasi ?? '-' }}

            </td>

        </tr>

    @endforeach

@else

    <tr>

        <td colspan="10"
            class="text-center text-muted py-4">

            Data presensi tidak ditemukan

        </td>

    </tr>

@endif

{{-- ================================================== --}}
{{-- Tampilkan Peta Presensi Masuk --}}
{{-- ================================================== --}}
<script>

    $(".tampilkanpetamasuk").click(function () {

        var id = $(this).attr("id");

        $.ajax({
            type: "POST",
            url: "/tampilkanpetamasuk",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            cache: false,
            success: function (respond) {
                $("#loadmap").html(respond);
            }
        });

        $("#modal-tampilkanpeta").modal("show");

    });

    // ==================================================
    // Tampilkan Peta Presensi Pulang
    // ==================================================
    $(".tampilkanpetapulang").click(function () {

        var id = $(this).attr("id");

        $.ajax({
            type: "POST",
            url: "/tampilkanpetapulang",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            cache: false,
            success: function (respond) {
                $("#loadmap").html(respond);
            }
        });

        $("#modal-tampilkanpeta").modal("show");

    });

</script>