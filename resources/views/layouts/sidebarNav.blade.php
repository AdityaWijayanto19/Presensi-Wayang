<aside class="hidden lg:flex fixed inset-y-0 left-0 z-40 w-56 bg-[#5E3E27] text-white flex-col">

    <div class="flex items-center h-14 px-4 border-b border-white/10 shrink-0">
        <a href="/dashboard" class="flex items-center gap-2">
            <img src="{{ asset('assets/img/login/logo_aplikasi.png') }}" alt="Logo" class="h-10">
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

        {{-- Home --}}
        <a href="/dashboard"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('dashboard') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="flex-shrink-0 w-5 h-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </span>
            <span>Home</span>
        </a>

        {{-- Histori --}}
        <a href="/presensi"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('presensi') && !request()->is('presensi/create') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="flex-shrink-0 w-5 h-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
            <span>Histori</span>
        </a>

        {{-- Presensi --}}
        <a href="/presensi/create"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('presensi/create') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="flex-shrink-0 w-5 h-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                </svg>
            </span>
            <span>Presensi</span>
        </a>

        {{-- Pengajuan --}}
        <a href="/pengajuan"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('pengajuan') || request()->is('izin*') || request()->is('lembur*') || request()->is('wfh*') || request()->is('cuti*') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="flex-shrink-0 w-5 h-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                </svg>
            </span>
            <span>Pengajuan</span>
        </a>

        {{-- Setting --}}
        <a href="/settings"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('settings') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="flex-shrink-0 w-5 h-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </span>
            <span>Setting</span>
        </a>

    </nav>

</aside>
