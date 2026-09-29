@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Log Aktivitas Administrator')

<x-admin.page-body>

    <x-admin.card>

        <div class="p-3">

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <form action="/panel/activity-log" method="GET">
                <div class="grid grid-cols-12 gap-2 mb-2">

                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <x-admin.input name="q" placeholder="Cari aktivitas..." value="{{ request('q') }}"
                            autocomplete="off" />
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <x-admin.select name="user" placeholder="Semua Pengguna" searchable
                            :value="request('user')">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <x-admin.select name="action" placeholder="Semua Aksi" :value="request('action')">
                            @foreach ($actions as $a)
                                <option value="{{ $a->value }}">{{ $a->label() }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <x-admin.select name="module" placeholder="Semua Modul" :value="request('module')">
                            @foreach ($modules as $m)
                                <option value="{{ $m->value }}">{{ $m->label() }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <x-admin.select name="status" placeholder="Semua Status" :value="request('status')">
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Berhasil
                            </option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal
                            </option>
                        </x-admin.select>
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <x-admin.input name="from" type="date" label="Dari Tanggal"
                            value="{{ request('from') }}" />
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <x-admin.input name="to" type="date" label="Sampai Tanggal" value="{{ request('to') }}" />
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2 self-end">
                        <x-admin.button variant="primary" icon="search" type="submit" block>Cari</x-admin.button>
                    </div>

                    <div class="col-span-12 sm:col-span-6 md:col-span-2 self-end">
                        <x-admin.button variant="secondary" icon="rotate-ccw" href="/panel/activity-log" block>
                            Reset
                        </x-admin.button>
                    </div>

                </div>
            </form>

            {{-- ================================================== --}}
            {{-- Tabel Log --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 border border-slate-200">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                No.</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Waktu</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Pengguna</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Aksi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Modul</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                IP Address</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-slate-200">

                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    {{ $logs->firstItem() + $loop->index }}</td>
                                <td class="px-2 py-1.5 text-xs text-slate-700 whitespace-nowrap"
                                    title="{{ $log->created_at?->format('d M Y H:i:s') }}">
                                    {{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    <div class="font-medium">{{ $log->user_name ?? $log->user?->name ?? '-' }}</div>
                                    @if ($log->role)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ match ($log->role) {
                                                'super_admin' => 'bg-purple-100 text-purple-700',
                                                'admin' => 'bg-blue-100 text-blue-700',
                                                'owner' => 'bg-amber-100 text-amber-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            } }}">
                                            {{ ucwords(str_replace('_', ' ', $log->role)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $log->action->badgeClass() }}">
                                        {{ $log->action->label() }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">{{ $log->module->label() }}</td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">{{ $log->description }}</td>
                                <td class="px-2 py-1.5 text-xs">
                                    @if ($log->status === \App\Models\AdminActivityLog::STATUS_SUCCESS)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-700">Berhasil</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-700">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-500 whitespace-nowrap"
                                    title="{{ $log->user_agent }}">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 py-6 text-center text-xs text-slate-400">
                                    Belum ada aktivitas yang tercatat.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-2">
                {{ $logs->links() }}
            </div>

        </div>

    </x-admin.card>

</x-admin.page-body>

@endsection
