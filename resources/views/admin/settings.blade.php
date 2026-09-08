@extends('layouts.admin.app')

@section('content')

<x-app.page-header title="Izin Browser" pretitle="Pengaturan" />

<x-app.page-body>

    <div class="max-w-2xl mx-auto space-y-2">
        @php
            $perms = [
                'location' => [
                    'title' => 'Izinkan Lokasi',
                    'desc' => 'Untuk presensi otomatis dan pelacakan lokasi WFH',
                    'color' => 'green',
                ],
                'camera' => [
                    'title' => 'Izinkan Kamera',
                    'desc' => 'Untuk foto selfie saat presensi masuk/pulang',
                    'color' => 'yellow',
                ],
                'notifications' => [
                    'title' => 'Izinkan Notifikasi',
                    'desc' => 'Untuk notifikasi WFH, pengingat, dan persetujuan',
                    'color' => 'blue',
                ],
            ];
        @endphp

        @foreach ($perms as $key => $perm)
            <x-app.card>
                <div class="p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-{{ $perm['color'] }}-100 flex items-center justify-center flex-shrink-0"></div>
                            <div>
                                <div class="font-bold text-slate-800">{{ $perm['title'] }}</div>
                                <div class="text-sm text-slate-500">{{ $perm['desc'] }}</div>
                                <div id="admin-status-{{ $key }}" class="text-sm {{ $permissions[$key] ? 'text-green' : 'text-red' }}">
                                    {{ $permissions[$key] ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer toggle-permission" data-permission="{{ $key }}" {{ $permissions[$key] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </x-app.card>
        @endforeach
    </div>

</x-app.page-body>

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
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, ' + action,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('/api/admin/permissions/toggle', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    permission: permission
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                var statusEl = document.getElementById('admin-status-' + permission);
                                if (data.is_enabled) {
                                    statusEl.textContent = 'Aktif';
                                    statusEl.className = 'text-sm text-green';
                                } else {
                                    statusEl.textContent = 'Nonaktif';
                                    statusEl.className = 'text-sm text-red';
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
    </script>
@endpush
