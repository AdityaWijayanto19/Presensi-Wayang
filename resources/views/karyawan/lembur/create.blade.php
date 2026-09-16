@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/lembur" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Ajukan Lembur</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="flex mt-[70px]">
        <div class="w-full px-2">
            @php
                $messagesuccess = Session::get('success');
                $messageerror = Session::get('error');
            @endphp

            @if (Session::get('success'))
                <div class="bg-[#34c759] text-white border border-[#34c759] text-[13px] rounded-md py-1.5 px-4">{{ $messagesuccess }}</div>
            @endif

            @if (Session::get('error'))
                <div class="bg-[#ec4433] text-white border border-[#ec4433] text-[13px] rounded-md py-1.5 px-4">{{ $messageerror }}</div>
            @endif

            {{-- Profile Card --}}
            <div class="bg-white rounded-xl border border-[#f0ece8] p-4 mt-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-coklat/10 flex items-center justify-center">
                        <i data-lucide="user" class="text-coklat" style="width:24px;height:24px;"></i>
                    </div>
                    <div>
                        <div class="text-[14px] font-bold text-[#1c1917]">{{ $karyawan->nama_lengkap }}</div>
                        <div class="text-[12px] text-[#78716c]">{{ $karyawan->jabatan }} • {{ $karyawan->posisi ?? '-' }}</div>
                        <div class="text-[11px] text-[#a8a29e]">{{ $karyawan->unitperusahaan?->perusahaan ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="/lembur/store" id="form_lembur" autocomplete="off">
                @csrf

                {{-- Tanggal Lembur --}}
                <div class="form-group mt-3">
                    <label class="text-sm font-medium text-[#1c1917]">Tanggal Lembur</label>
                    <input type="text" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-gray-100"
                           value="{{ now('Asia/Jakarta')->format('Y-m-d') }}" readonly name="tgl_lembur">
                    <small class="text-[#a8a29e] text-[11px]">Lembur hanya bisa diajukan untuk hari ini.</small>
                </div>

                {{-- Keterangan --}}
                <div class="form-group mt-2">
                    <label class="text-sm font-medium text-[#1c1917]">Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="keterangan" id="keterangan" rows="4" maxlength="1000"
                              class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                              placeholder="Jelaskan alasan lembur..." required minlength="5">{{ old('keterangan') }}</textarea>
                    <small class="text-[#a8a29e] text-[11px]">Min. 5 karakter, maks. 1000 karakter.</small>
                </div>

                {{-- Submit --}}
                <div class="form-group mt-3">
                    <button class="btn btn-primary w-full">Kirim Pengajuan Lembur</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('form_lembur').addEventListener('submit', function (e) {
            e.preventDefault();

            var keterangan = document.getElementById('keterangan').value.trim();

            if (keterangan.length < 5) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Keterangan minimal 5 karakter!', confirmButtonColor: '#7a5234' });
                return false;
            }

            Swal.fire({
                title: 'Kirim Pengajuan Lembur?',
                text: 'Pastikan data yang dikirim sudah benar!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7a5234',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('form_lembur').submit(); }
            });
        });
    });
</script>
@endpush
