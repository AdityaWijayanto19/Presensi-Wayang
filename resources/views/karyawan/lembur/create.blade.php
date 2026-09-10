@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/lembur" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Kirim Dokumen Lembur</div>
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

            <form method="POST" action="/lembur/store" id="form_lembur" autocomplete="off" enctype="multipart/form-data">
                @csrf

                {{-- Tanggal Lembur --}}
                <div class="flex flex-wrap -mx-2 mt-2">
                    <div class="w-full px-2">
                        <div class="form-group">
                            <input type="text" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors datepicker" placeholder="Tanggal Lembur" name="tgl_lembur" id="tgl_lembur">
                        </div>
                    </div>
                </div>

                {{-- Durasi --}}
                <div class="form-group mt-2">
                    <select name="durasi" id="durasi" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Pilih Durasi Lembur</option>
                        <option value="1 Jam">1 Jam</option>
                        <option value="1.5 Jam">1.5 Jam</option>
                        <option value="2 Jam">2 Jam</option>
                        <option value="2.5 Jam">2.5 Jam</option>
                        <option value="3 Jam">3 Jam</option>
                        <option value="3.5 Jam">3.5 Jam</option>
                        <option value="4 Jam">4 Jam</option>
                        <option value="4.5 Jam">4.5 Jam</option>
                        <option value="5 Jam">5 Jam</option>
                        <option value="Prorate">Prorate</option>
                    </select>
                </div>

                {{-- Upload Form Lembur --}}
                <div class="form-group mt-2">
                    <label class="text-base text-black font-medium">Form Lembur</label>
                    <small class="text-red-500 block -mt-1 mb-2">* Format yang didukung: PDF, DOC, DOCX, JPG, JPEG, PNG (Maks. 4 MB)</small>
                    <input type="file" name="file_form" id="file_form" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>

                {{-- Upload Laporan --}}
                <div class="form-group mt-2">
                    <label class="text-base text-black font-medium">Laporan Lembur</label>
                    <small class="text-red-500 block -mt-1 mb-2">* Format yang didukung: PDF, DOC, DOCX, JPG, JPEG, PNG (Maks. 4 MB)</small>
                    <input type="file" name="file_laporan" id="file_laporan" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>

                {{-- Submit --}}
                <div class="form-group mt-2">
                    <button class="btn btn-primary w-full">Kirim Data Lembur</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('myscript')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr(".datepicker", { dateFormat: "Y-m-d" });

        document.getElementById('form_lembur').addEventListener('submit', function (e) {
            e.preventDefault();

            var tgl_lembur = document.getElementById('tgl_lembur').value;
            var durasi = document.getElementById('durasi').value;
            var file_form = document.getElementById('file_form').files[0];
            var file_laporan = document.getElementById('file_laporan').files[0];

            if (tgl_lembur == "") {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Tanggal lembur harus diisi!', confirmButtonColor: '#7a5234' });
                return false;
            } else if (durasi == "") {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Durasi lembur harus dipilih!', confirmButtonColor: '#7a5234' });
                return false;
            } else if (!file_form) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Form lembur harus diupload!', confirmButtonColor: '#7a5234' });
                return false;
            } else if (!file_laporan) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Laporan lembur harus diupload!', confirmButtonColor: '#7a5234' });
                return false;
            } else if (file_form.size > 4 * 1024 * 1024) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Ukuran Form Lembur maksimal 4MB!', confirmButtonColor: '#7a5234' });
                return false;
            } else if (file_laporan.size > 4 * 1024 * 1024) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Ukuran Laporan Lembur maksimal 4MB!', confirmButtonColor: '#7a5234' });
                return false;
            }

            Swal.fire({
                title: 'Kirim Data Lembur?',
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
