@if ($histori->isEmpty())
    <div class="bg-transparent text-[#fe9500] border border-[#fe9500] text-[13px] rounded-md py-1.5 px-4 mb-2">
        Tidak ada data untuk periode yang dipilih!
    </div>
@endif

@foreach ($histori as $d)
    @php
        $path = Storage::url('uploads/absensi/' . $d->foto_in);
    @endphp

    <ul class="listview image-listview">
        <li>
            <div class="item">
                <img src="{{ url($path) }}?v={{ time() }}" alt="image" class="image w-[50px] h-[50px] object-cover rounded-xl foto-histori">
                <div class="in">
                    <div>
                        <b>{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</b>
                    </div>
                    <span class="inline-flex items-center justify-center rounded-full text-white text-xs px-2 py-0.5 {{ $d->terlambat > 0 ? 'bg-red-500' : 'bg-green-500' }}">
                        {{ $d->jam_in }}
                    </span>
                    <span class="inline-flex items-center justify-center rounded-full bg-red-500 text-white text-xs px-2 py-0.5">
                        {{ $d->jam_out ?? 'Belum Presensi' }}
                    </span>
                </div>
            </div>
        </li>
    </ul>
@endforeach
