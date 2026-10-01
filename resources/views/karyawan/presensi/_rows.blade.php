@if ($histori->isEmpty())
    <div class="bg-transparent text-[#fe9500] border border-[#fe9500] text-[13px] rounded-md py-1.5 px-4 mb-2 mt-4 text-center">
        Data absensi tidak ditemukan
    </div>
@else
    <ul class="listview image-listview">
        @foreach ($histori as $d)
            @php
                $pathIn = Storage::url('uploads/absensi/' . $d->foto_in);
                $pathOut = $d->foto_out ? Storage::url('uploads/absensi/' . $d->foto_out) : null;
                $statusClass = $d->terlambat > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700';
                $statusLabel = $d->terlambat > 0 ? 'Terlambat ' . $d->terlambat . 'm' : 'Tepat Waktu';
            @endphp

            <li>
                <div class="item">
                    <div class="flex gap-2 mr-3 flex-shrink-0">
                        <div class="text-center">
                            <img src="{{ url($pathIn) }}?v={{ time() }}" alt="Foto masuk"
                                class="image w-[35px] h-[35px] rounded-[10px] object-cover border-2 border-white shadow-sm foto-histori">
                            <span class="block text-[8px] text-[#78716c] leading-tight mt-0.5">Masuk</span>
                        </div>
                        <div class="text-center">
                            @if ($pathOut)
                                <img src="{{ url($pathOut) }}?v={{ time() }}" alt="Foto pulang"
                                    class="image w-[35px] h-[35px] rounded-[10px] object-cover border-2 border-white shadow-sm foto-histori">
                            @else
                                <div
                                    class="w-[35px] h-[35px] rounded-[10px] bg-gray-100 border-2 border-white shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-400">
                                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                        <circle cx="12" cy="13" r="3" />
                                    </svg>
                                </div>
                            @endif
                            <span class="block text-[8px] text-[#78716c] leading-tight mt-0.5">Pulang</span>
                        </div>
                    </div>
                    <div class="in flex-wrap gap-1">
                        <div class="w-full flex items-center justify-between gap-2">
                            <span class="text-[13px] text-[#141515]"><b>{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</b></span>
                            <span
                                class="inline-flex items-center justify-center rounded-full {{ $statusClass }} text-[10px] sm:text-xs font-semibold px-2 py-0.5">{{ $statusLabel }}</span>
                        </div>
                        <div class="w-full flex flex-wrap gap-1">
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-[#1c1917] text-white text-[10px] sm:text-xs px-2 py-0.5">Masuk
                                {{ $d->jam_in }}</span>
                            @if ($d->jam_out != null)
                                <span
                                    class="inline-flex items-center justify-center rounded-full bg-[#1c1917] text-white text-[10px] sm:text-xs px-2 py-0.5">Pulang
                                    {{ $d->jam_out }}</span>
                            @else
                                <span
                                    class="inline-flex items-center justify-center rounded-full bg-amber-500 text-white text-[10px] sm:text-xs px-2 py-0.5">Belum
                                    Presensi Pulang</span>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
