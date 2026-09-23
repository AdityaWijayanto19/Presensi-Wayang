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
    <div class="flex mt-[70px]" x-data="lemburForm()">
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

                {{-- Durasi Lembur --}}
                <div class="form-group mt-2">
                    <label class="text-sm font-medium text-[#1c1917]">Durasi Lembur <span class="text-red-500">*</span></label>
                    <select name="durasi_jam" id="durasi_jam" x-model="durasi"
                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            required>
                        <option value="">Pilih durasi</option>
                        <option value="0.5">30 menit</option>
                        <option value="1">1 jam</option>
                        <option value="1.5">1,5 jam</option>
                        <option value="2">2 jam</option>
                        <option value="2.5">2,5 jam</option>
                        <option value="3">3 jam</option>
                    </select>
                </div>

                {{-- Rencana Jam Mulai --}}
                <div class="form-group mt-2">
                    <label class="text-sm font-medium text-[#1c1917]">Rencana Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" id="jam_mulai" x-model="jamMulai"
                           class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           required step="1800">
                </div>

                {{-- Rencana Jam Selesai (Otomatis) --}}
                <div class="form-group mt-2">
                    <label class="text-sm font-medium text-[#1c1917]">Rencana Jam Selesai</label>
                    <input type="text" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-gray-100"
                           :value="jamSelesai" readonly tabindex="-1">
                    <small class="text-[#a8a29e] text-[11px]">Dihitung otomatis dari jam mulai + durasi.</small>
                </div>

                {{-- Info Rencana --}}
                <div x-show="durasi && jamMulai" x-cloak class="mt-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="text-blue-500 shrink-0" style="width:16px;height:16px;"></i>
                        <span class="text-[12px] text-blue-700 font-medium" x-text="infoText"></span>
                    </div>
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
    function lemburForm() {
        return {
            durasi: '',
            jamMulai: '',
            get jamSelesai() {
                if (!this.durasi || !this.jamMulai) return '-';
                const [h, m] = this.jamMulai.split(':').map(Number);
                const totalMenit = h * 60 + m + (parseFloat(this.durasi) * 60);
                const jam = Math.floor(totalMenit / 60) % 24;
                const menit = totalMenit % 60;
                return String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');
            },
            get infoText() {
                if (!this.durasi || !this.jamMulai) return '';
                const durasiLabel = this.durasi.replace('.', ',');
                return 'Lembur ' + durasiLabel + ' jam: ' + this.jamMulai + ' - ' + this.jamSelesai;
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('form_lembur').addEventListener('submit', function (e) {
            e.preventDefault();

            var keterangan = document.getElementById('keterangan').value.trim();
            var durasi = document.getElementById('durasi_jam').value;
            var jamMulai = document.getElementById('jam_mulai').value;

            if (!durasi) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Durasi lembur wajib dipilih!', confirmButtonColor: '#7a5234' });
                return false;
            }

            if (!jamMulai) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Rencana jam mulai wajib diisi!', confirmButtonColor: '#7a5234' });
                return false;
            }

            if (keterangan.length < 5) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Keterangan minimal 5 karakter!', confirmButtonColor: '#7a5234' });
                return false;
            }

            var durasiLabel = durasi.replace('.', ',');
            var jamSelesai = document.querySelector('[x-data]').__x.$data.jamSelesai;

            Swal.fire({
                title: 'Kirim Pengajuan Lembur?',
                html: 'Lembur <b>' + durasiLabel + ' jam</b> pada <b>' + jamMulai + ' - ' + jamSelesai + '</b>',
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
