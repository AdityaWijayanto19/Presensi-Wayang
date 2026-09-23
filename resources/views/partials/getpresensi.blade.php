@if ($presensi->count() > 0)

    @foreach ($presensi as $p)

        @php
            $foto_in = Storage::url('uploads/absensi/' . $p->foto_in);
            $foto_out = $p->foto_out ? Storage::url('uploads/absensi/' . $p->foto_out) : null;
        @endphp

        <tr class="hover:bg-slate-50">

            <td class="px-2 py-1.5 text-xs">{{ $loop->iteration }}</td>

            <td class="px-2 py-1.5 text-xs">{{ $p->nik }}</td>

            <td class="px-2 py-1.5 text-xs truncate-cell">{{ $p->karyawan->nama_lengkap ?? '-' }}</td>

            <td class="px-2 py-1.5 text-xs truncate-cell">{{ $p->karyawan->unit ?? '-' }}</td>

            <td class="px-2 py-1.5 text-xs">{{ $p->jam_in }}</td>

            <td class="px-2 py-1.5 text-xs">
                <img src="{{ url($foto_in) }}"
                    class="w-10 h-10 rounded-lg object-cover cursor-pointer foto-monitoring">
            </td>

            <td class="px-2 py-1.5 text-xs">
                @if ($p->jam_out != null)
                    {{ $p->jam_out }}
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 text-[10px] px-2 py-0.5 font-medium">Belum Presensi</span>
                @endif
            </td>

            <td class="px-2 py-1.5 text-xs">
                @if ($foto_out)
                    <img src="{{ url($foto_out) }}"
                        class="w-10 h-10 rounded-lg object-cover cursor-pointer foto-monitoring">
                @else
                    <span class="text-slate-400">-</span>
                @endif
            </td>

            <td class="px-2 py-1.5 text-xs">
                @if ($p->terlambat > 0)
                    <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 text-[10px] px-2 py-0.5 font-medium">
                        Terlambat {{ $p->terlambat }}m
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 text-[10px] px-2 py-0.5 font-medium">
                        Tepat Waktu
                    </span>
                @endif
            </td>

            <td class="px-2 py-1.5 text-xs" style="min-width: 100px;">
                <div class="flex flex-col gap-1">
                    <button type="button"
                        class="inline-flex items-center justify-center rounded bg-blue-600 px-2 py-0.5 text-[10px] font-medium text-white hover:bg-blue-700 transition-colors tampilkanpetamasuk"
                        data-id="{{ $p->id }}">
                        Masuk
                    </button>

                    @if ($p->lokasi_out != null)
                        <button type="button"
                            class="inline-flex items-center justify-center rounded bg-blue-600 px-2 py-0.5 text-[10px] font-medium text-white hover:bg-blue-700 transition-colors tampilkanpetapulang"
                            data-id="{{ $p->id }}">
                            Pulang
                        </button>
                    @endif

                    @can('presensi-edit', null, 'user')
                        <button type="button"
                            class="inline-flex items-center justify-center rounded bg-slate-600 px-2 py-0.5 text-[10px] font-medium text-white hover:bg-slate-700 transition-colors edit-presensi"
                            data-id="{{ $p->id }}"
                            data-jam_in="{{ $p->jam_in }}"
                            data-jam_out="{{ $p->jam_out }}">
                            Edit
                        </button>
                    @endcan
                </div>
            </td>

        </td>
    </tr>

    @endforeach

@else

    <tr>
        <td colspan="9"
            class="px-2 py-6 text-center text-xs text-slate-500">
            Data presensi tidak ditemukan
        </td>
    </tr>

@endif
