@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/dashboard" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Pengaturan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="section mt-[70px]">
        {{-- Permission Toggles --}}
        @php
            $perms = [
                'location' => ['icon' => 'map-pin', 'title' => 'Izinkan Lokasi', 'desc' => 'Untuk presensi otomatis dan pelacakan lokasi WFH'],
                'camera' => ['icon' => 'camera', 'title' => 'Izinkan Kamera', 'desc' => 'Untuk foto selfie saat presensi masuk/pulang'],
                'notifications' => ['icon' => 'bell', 'title' => 'Izinkan Notifikasi', 'desc' => 'Untuk notifikasi WFH, pengingat, dan persetujuan'],
            ];
        @endphp

        @foreach ($perms as $key => $perm)
            <div class="card mb-3 border border-stone-200 rounded-2xl shadow-sm">
                <div class="card-body p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700">
                            <i data-lucide="{{ $perm['icon'] }}" class="text-xl"></i>
                        </div>
                        <div>
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ $perm['title'] }}</div>
                            <div class="text-[11px] text-[#78716c]">{{ $perm['desc'] }}</div>
                            <div id="status-{{ $key }}" class="text-[10px] mt-0.5 {{ $permissions[$key] ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $permissions[$key] ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer toggle-permission"
                            data-permission="{{ $key }}"
                            {{ $permissions[$key] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>
        @endforeach

        {{-- Logout --}}
        <div class="card mt-6 border border-stone-200 rounded-2xl shadow-sm">
                <a href="#" id="btnLogout" class="flex items-center gap-3 text-rose-600 no-underline p-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-200 flex items-center justify-center">
                        <i data-lucide="log-out" class="text-xl"></i>
                    </div>
                    <div class="text-[14px] font-bold">Keluar</div>
                </a>
        </div>
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
                            if (data.is_enabled) {
                                statusEl.textContent = 'Aktif';
                                statusEl.className = 'text-[10px] mt-0.5 text-emerald-600';
                                if (permission === 'notifications' && 'Notification' in window && Notification.permission === 'default') {
                                    Notification.requestPermission();
                                }
                            } else {
                                statusEl.textContent = 'Nonaktif';
                                statusEl.className = 'text-[10px] mt-0.5 text-rose-600';
                            }
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
