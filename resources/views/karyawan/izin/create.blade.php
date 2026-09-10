@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/izin" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Kirim Dokumen Izin / Sakit</div>
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

            <form method="POST" action="/izin/store" id="form_izin" autocomplete="off" enctype="multipart/form-data">
                @csrf

                {{-- Tanggal --}}
                <div class="flex flex-wrap -mx-2 mt-2">
                    <div class="w-full px-2">
                        <div class="form-group">
                            <input type="text" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors datepicker" placeholder="Tanggal" name="tgl_izin" id="tgl_izin">
                        </div>
                    </div>
                </div>

                {{-- Jenis Izin --}}
                <div class="form-group mt-2">
                    <select name="jenis_izin" id="jenis_izin" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Pilih Jenis Izin</option>
                        <option value="i">Izin</option>
                        <option value="s">Sakit</option>
                    </select>
                </div>

                {{-- Upload Dokumen --}}
                <div class="form-group mt-2">
                    <label class="text-base text-black font-medium">Dokumen Izin / Sakit</label>
                    <small class="text-red-500 block -mt-1 mb-2">* Format yang didukung: PDF, DOC, DOCX (Maks. 4 MB)</small>
                    <input type="file" name="file" id="file" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" accept=".pdf,.doc,.docx">
                </div>

                {{-- Submit --}}
                <div class="form-group mt-2">
                    <button class="btn btn-primary w-full">Kirim File</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('myscript')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr(".datepicker", { dateFormat: "Y-m-d" });

        document.getElementById('form_izin').addEventListener('submit', function (e) {
            e.preventDefault();

            var tgl_izin = document.getElementById('tgl_izin').value;
            var jenis_izin = document.getElementById('jenis_izin').value;
            var file = document.getElementById('file').files[0];

            if (tgl_izin == "") {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Tanggal harus diisi!', confirmButtonColor: '#7a5234' });
                return;
            } else if (jenis_izin == "") {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Jenis Izin harus diisi!', confirmButtonColor: '#7a5234' });
                return;
            } else if (!file) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'File harus diupload!', confirmButtonColor: '#7a5234' });
                return;
            } else if (file.size > 4 * 1024 * 1024) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Ukuran file maksimal 4MB!', confirmButtonColor: '#7a5234' });
                return;
            }

            Swal.fire({
                title: 'Kirim Surat?',
                text: 'Pastikan data yang dikirim sudah benar!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7a5234',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('form_izin').submit(); }
            });
        });
    });
</script>

@endpush
