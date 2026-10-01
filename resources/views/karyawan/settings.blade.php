@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Pengaturan</div>
    </div>
@endsection

@section('content')
    <div class="section mt-[70px]">
        {{-- Permission Toggles --}}
        @php
            $perms = [
                'location' => ['icon' => 'map-pin', 'title' => 'Izinkan Lokasi', 'desc' => 'Untuk presensi otomatis dan pelacakan lokasi WFH'],
                'camera' => ['icon' => 'camera', 'title' => 'Izinkan Kamera', 'desc' => 'Untuk foto selfie saat presensi masuk/pulang'],
                'notifications' => ['icon' => 'bell', 'title' => 'Izinkan Notifikasi', 'desc' => 'Pengingat WFH, izin, dan lembur — tetap masuk walau aplikasi ditutup'],
            ];
        @endphp

        @foreach ($perms as $key => $perm)
            @php $isNotifications = $key === 'notifications'; @endphp
            <x-admin.card class="mb-3">
                <div class="card-body p-4 flex items-center justify-between"
                    @if($isNotifications) x-data="pushSettings({{ $permissions[$key] ? 'true' : 'false' }})" x-init="init()" @endif>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700">
                            <i data-lucide="{{ $perm['icon'] }}" class="text-xl"></i>
                        </div>
                        <div>
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ $perm['title'] }}</div>
                            <div class="text-[11px] text-[#78716c]">{{ $perm['desc'] }}</div>
                            @if($isNotifications)
                                {{-- Satu sumber kebenaran: gabungan status browser dan preferensi aplikasi. --}}
                                <div class="text-[10px] mt-0.5" :class="statusClass" x-text="statusText"></div>
                                <div x-show="needsInstall" x-cloak class="mt-1">
                                    <a href="/install" class="text-[10px] font-semibold text-amber-700 underline underline-offset-2">Lihat cara pasang ke Layar Utama</a>
                                </div>
                                <div x-show="needsRetry" x-cloak class="mt-1">
                                    <button type="button" @click="retrySync()" :disabled="busy"
                                        x-text="busy ? 'Menyinkronkan…' : 'Coba sinkronkan lagi'"
                                        class="text-[10px] font-semibold text-amber-700 underline underline-offset-2 border-0 bg-transparent p-0 cursor-pointer disabled:opacity-60"></button>
                                </div>
                            @else
                                <div id="status-{{ $key }}" class="text-[10px] mt-0.5 {{ $permissions[$key] ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $permissions[$key] ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer"
                        @if($isNotifications) :class="{ 'opacity-50': toggleDisabled, 'pointer-events-none': toggleDisabled }" @endif>
                        <input type="checkbox" class="sr-only peer toggle-permission"
                            data-permission="{{ $key }}"
                            @if($isNotifications) :disabled="toggleDisabled" @endif
                            {{ $permissions[$key] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </x-admin.card>
        @endforeach

        {{-- Guideline --}}
        <x-admin.card class="mt-3">
            <a href="/guideline" class="flex items-center gap-3 p-4 no-underline active:scale-[0.99] transition-transform">
                <div class="w-10 h-10 rounded-xl bg-sky-100 border border-sky-200 flex items-center justify-center text-sky-700 shrink-0">
                    <i data-lucide="book-open" class="text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[14px] font-bold text-[#1c1917]">Panduan & Ketentuan</div>
                    <div class="text-[11px] text-[#78716c]">Pahami aturan WFH, lembur, izin, dan cuti</div>
                </div>
                <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0" style="width:18px;height:18px;"></i>
            </a>
        </x-admin.card>

        {{-- Logout --}}
        <x-admin.card class="mt-6">
                <a href="#" id="btnLogout" class="flex items-center gap-3 text-rose-600 no-underline p-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-200 flex items-center justify-center">
                        <i data-lucide="log-out" class="text-xl"></i>
                    </div>
                    <div class="text-[14px] font-bold">Keluar</div>
                </a>
        </x-admin.card>
    </div>
@endsection

@push('myscript')
    <script>
        document.querySelectorAll('.toggle-permission').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                var permission = this.dataset.permission;
                var isChecked = this.checked;
                var toggleEl = this;
                var action = isChecked ? 'Mengaktifkan' : 'Menonaktifkan';
                var permissionLabels = {
                    location: 'Lokasi',
                    camera: 'Kamera',
                    notifications: 'Notifikasi'
                };
                var label = permissionLabels[permission] || permission;

                Swal.fire({
                    title: action + ' Izin ' + label + '?',
                    text: isChecked ? 'Browser akan meminta izin.' : 'Izin akan dinonaktifkan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#9c6b43',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, ' + action,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Izin browser WAJIB diminta tepat di dalam gestur klik konfirmasi
                        // ini, sebelum await apa pun. WebKit/iOS menolak
                        // Notification.requestPermission() yang dipanggil setelah await fetch.
                        var pushEnable = null;
                        if (permission === 'notifications' && isChecked && window.WAGPush) {
                            pushEnable = window.WAGPush.enable();
                        }

                        fetch('/api/user/permissions/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ permission: permission })
                        })
                        .then(r => r.json())
                        .then(data => {
                            var statusEl = document.getElementById('status-' + permission);
                            if (statusEl) {
                                statusEl.textContent = data.is_enabled ? 'Aktif' : 'Nonaktif';
                                statusEl.className = 'text-[10px] mt-0.5 ' +
                                    (data.is_enabled ? 'text-emerald-600' : 'text-rose-600');
                            }

                            // Kartu notifikasi dikendalikan Alpine (pushSettings) — beri tahu
                            // lewat event, jangan menulis DOM secara langsung.
                            document.dispatchEvent(new CustomEvent('wag-permission-toggled', {
                                detail: { permission: permission, is_enabled: data.is_enabled }
                            }));

                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Izin ' + label + ' berhasil ' + (data.is_enabled ? 'diaktifkan' : 'dinonaktifkan'),
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        })
                        .catch(() => {
                            toggleEl.checked = !toggleEl.checked;
                            Swal.fire('Gagal', 'Terjadi kesalahan. Coba lagi.', 'error');
                        });

                        if (pushEnable) {
                            pushEnable.then(function (pushResult) {
                                if (typeof window.showToast !== 'function') return;
                                if (!pushResult || pushResult.ok) return;

                                if (pushResult.reason === 'denied') {
                                    window.showToast('error', 'Izin notifikasi ditolak oleh browser');
                                } else if (pushResult.reason === 'unsupported') {
                                    window.showToast('error', 'Browser ini belum mendukung notifikasi');
                                } else if (pushResult.reason === 'error') {
                                    window.showToast('error', 'Langganan notifikasi gagal dibuat');
                                }
                            });
                        }
                    } else {
                        this.checked = !this.checked;
                    }
                });
            });
        });

        document.getElementById('btnLogout').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin logout?',
                text: 'Anda akan keluar dari aplikasi!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#9c6b43',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/proseslogout';
                }
            });
        });
    </script>
@endpush
