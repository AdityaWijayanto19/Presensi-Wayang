<form action="/users/{{ $user->id }}/update"
    method="POST"
    id="formUser"
    enctype="multipart/form-data">

    @csrf

    {{-- ================================================== --}}
    {{-- Nama User --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">

            <div class="relative mb-3">

                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                        <path d="M16 19h6" />
                        <path d="M19 16v6" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />

                    </svg>

                </span>

                <input type="text"
                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="nama_user"
                    id="nama_user"
                    value="{{ $user->name }}"
                    placeholder="Nama User">

            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Email --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">

            <div class="relative mb-3">

                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 18h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v7.5" />
                        <path d="M3 6l9 6l9 -6" />
                        <path d="M15 18h6" />
                        <path d="M18 15l3 3l-3 3" />

                    </svg>

                </span>

                <input type="text"
                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="email"
                    id="email"
                    value="{{ $user->email }}"
                    placeholder="Email User"
                    autocomplete="off">

            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Unit Perusahaan --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">

            <div class="space-y-1">

                <select name="unit"
                    id="unit"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                    <option value="">Unit Perusahaan</option>

                    @foreach ($unitperusahaan as $d)
                        <option value="{{ $d->unit }}"
                            {{ $user->unit == $d->unit ? 'selected' : '' }}>

                            {{ $d->unit }}

                        </option>
                    @endforeach

                </select>

            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Role --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2 mt-3">
        <div class="col-span-12">

            <div class="space-y-1">

                <select name="role"
                    id="role"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                    <option value="">Role</option>

                    @foreach ($role as $d)
                        <option value="{{ $d->id }}"
                            {{ $user->role_id == $d->id ? 'selected' : '' }}>

                            {{ ucwords($d->name) }}

                        </option>
                    @endforeach

                </select>

            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Password --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2 mt-3">
        <div class="col-span-12">

            <div class="relative mb-3">

                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0" />
                        <path d="M15 9h.01" />

                    </svg>

                </span>

                <input type="password"
                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="password"
                    id="password"
                    value=""
                    placeholder="Password">

            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Button Perbarui --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2 mt-3">
        <div class="col-span-12">

            <div class="space-y-1">

                <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12" />
                        <path d="M13 8l3 3l-3 3" />
                        <path d="M16 11h-8" />

                    </svg>

                    Perbarui Data!

                </button>

            </div>

        </div>
    </div>

</form>
