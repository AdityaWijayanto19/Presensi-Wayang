@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/settings" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>

        <div class="pageTitle">Tentang Aplikasi</div>

        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $appName = 'WAG - Presensi Digital';
        $appVersion = 'v1.0.0';
        $appCategory = 'Presensi Wayang';

        $appDesc = 'Aplikasi presensi digital untuk pencatatan kehadiran, WFH, lembur, izin, dan cuti karyawan.';

        $credits = [
            'business_product' => 'NAUFAIL IMAMUDDIN',
            'initial_development' => 'MUHAMMAD OMAR M. R.',
            'continued_development' => 'ADITYA PUTRA WIJAYANTO',
        ];
    @endphp

    <div class="mt-[70px] min-h-screen bg-[#f4f5f6] px-2.5 sm:px-3.5 lg:px-5 py-3 sm:py-5">

        <div class="mx-auto w-full max-w-6xl pb-8">

            {{-- =====================================================
                 APP INFORMATION
            ====================================================== --}}
            <section
                class="relative flex items-center gap-3 sm:gap-5 overflow-hidden
                            rounded-[16px] sm:rounded-[20px]
                            border border-[#ebe8e5] bg-white
                            px-3.5 py-4 sm:px-6 sm:py-6
                            shadow-[0_2px_8px_rgba(0,0,0,0.035)]">

                {{-- Logo --}}
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center
                            overflow-hidden sm:h-[78px] sm:w-[78px] sm:rounded-[17px]">

                    <img src="{{ asset('assets/img/login/logo_aplikasi.png') }}" alt="{{ $appName }}"
                        class="h-11 w-11 object-contain sm:h-16 sm:w-16">
                </div>

                {{-- Divider --}}
                <div class="h-14 w-px shrink-0 bg-[#ddcfc4] sm:h-[78px]"></div>

                {{-- Information --}}
                <div class="min-w-0">

                    <h1
                        class="text-[15px] font-bold leading-tight text-[#29221e]
                               sm:text-[20px]">
                        {{ $appName }}
                    </h1>

                    <p
                        class="mt-1 max-w-3xl text-[9.5px] leading-relaxed text-[#78716c]
                              sm:mt-1.5 sm:text-[13px]">
                        {{ $appDesc }}
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-1.5 sm:mt-3 sm:gap-2">

                        <span
                            class="rounded-full border border-[#e7d8cd] bg-[#f4ebe5]
                                     px-2 py-1 text-[8px] font-bold text-[#7a5234]
                                     sm:px-3 sm:py-1.5 sm:text-[11px]">
                            {{ $appVersion }}
                        </span>

                        <span
                            class="rounded-full border border-[#e7e3df] bg-[#f5f3f1]
                                     px-2 py-1 text-[8px] font-semibold text-[#6b625c]
                                     sm:px-3 sm:py-1.5 sm:text-[11px]">
                            {{ $appCategory }}
                        </span>

                    </div>
                </div>

            </section>


            {{-- =====================================================
                 SECTION TITLE
            ====================================================== --}}
            <div class="mt-6 flex items-center gap-2.5 px-1 sm:mt-7 sm:gap-3.5">

                <h2
                    class="shrink-0 text-[9px] font-bold uppercase tracking-[0.14em]
                           text-[#79583f]
                           sm:text-[12px]">
                    Tim Pengembang
                </h2>

                <div class="h-px flex-1 bg-[#ddd6d0]"></div>

            </div>


            {{-- =====================================================
                 DEVELOPMENT TREE
            ====================================================== --}}
            <section class="mt-3 px-0.5 sm:mt-5 sm:px-2">

                {{-- ROOT --}}
                <div
                    class="mx-auto w-[64%] rounded-[14px]
                            border border-[#eadbd0] bg-[#fbf7f3]
                            px-3 py-4 text-center
                            sm:w-[54%] sm:rounded-[18px] sm:px-6 sm:py-7
                            lg:w-[48%]">

                    {{-- Icon --}}
                    <div
                        class="mx-auto flex h-10 w-10 items-center justify-center
                                rounded-full bg-coklat text-white
                                shadow-[0_5px_14px_rgba(122,82,52,0.16)]
                                sm:h-14 sm:w-14">

                        <i data-lucide="users-round" class="h-[18px] w-[18px] sm:h-6 sm:w-6"></i>

                    </div>

                    {{-- Title --}}
                    <div
                        class="mt-2.5 text-[8px] font-bold leading-[1.7]
                                tracking-[0.12em] text-[#4e3524]
                                sm:mt-4 sm:text-[13px] sm:tracking-[0.16em]">

                        BUSINESS &amp; PRODUCT
                        <br>
                        DIRECTION

                    </div>

                    {{-- Description --}}
                    <p
                        class="mx-auto mt-1.5 max-w-[360px]
                              text-[7.5px] leading-[1.5] text-[#82766e]
                              sm:mt-3 sm:text-[12px] sm:leading-[1.65]">

                        Menentukan arah produk, kebutuhan bisnis,
                        dan alur setiap fitur aplikasi.

                    </p>

                    {{-- Person --}}
                    <div
                        class="mt-3 flex items-center justify-center gap-1.5
                                border-t border-[#e4d6cb] pt-2.5
                                sm:mt-5 sm:gap-2 sm:pt-4">

                        <span
                            class="text-[8px] font-bold text-[#29221e]
                                     sm:text-[13px]">
                            {{ $credits['business_product'] }}
                        </span>

                    </div>

                </div>


                {{-- =================================================
                     TREE CONNECTOR
                ================================================== --}}
                <div class="relative mx-auto h-[50px] w-full
                            sm:h-[72px]">

                    {{-- vertical --}}
                    <div
                        class="absolute left-1/2 top-0 h-[25px] w-px
                                -translate-x-1/2 bg-[#cdbbaa]
                                sm:h-[36px]">
                    </div>

                    {{-- horizontal --}}
                    <div
                        class="absolute left-[25%] right-[25%] top-[25px]
                                h-px bg-[#cdbbaa]
                                sm:top-[36px]">
                    </div>

                    {{-- left branch --}}
                    <div
                        class="absolute left-[25%] top-[25px] h-[25px] w-px
                                bg-[#cdbbaa]
                                sm:top-[36px] sm:h-[36px]">
                    </div>

                    {{-- right branch --}}
                    <div
                        class="absolute right-[25%] top-[25px] h-[25px] w-px
                                bg-[#cdbbaa]
                                sm:top-[36px] sm:h-[36px]">
                    </div>

                    {{-- dots --}}
                    <span
                        class="absolute left-[25%] bottom-[-3px]
                                 h-1.5 w-1.5 -translate-x-1/2 rounded-full
                                 bg-[#cdbbaa] sm:h-2 sm:w-2">
                    </span>

                    <span
                        class="absolute right-[25%] bottom-[-3px]
                                 h-1.5 w-1.5 translate-x-1/2 rounded-full
                                 bg-[#cdbbaa] sm:h-2 sm:w-2">
                    </span>

                </div>


                {{-- =================================================
                     CHILD NODES
                ================================================== --}}
                <div class="grid grid-cols-2 gap-1.5 sm:gap-4 lg:gap-6">

                    {{-- INITIAL --}}
                    <div
                        class="flex min-h-[175px] flex-col items-center
                                rounded-[14px] border border-[#eadbd0]
                                bg-[#fbf7f3]
                                px-2 py-4 text-center
                                sm:min-h-[250px] sm:rounded-[18px]
                                sm:px-6 sm:py-7">

                        <div
                            class="flex h-10 w-10 items-center justify-center
                                    rounded-full bg-[#b8793d] text-white
                                    shadow-[0_5px_14px_rgba(184,121,61,0.16)]
                                    sm:h-14 sm:w-14">

                            <i data-lucide="code-2" class="h-[18px] w-[18px] sm:h-6 sm:w-6"></i>

                        </div>

                        <div
                            class="mt-2.5 text-[7px] font-bold
                                    tracking-[0.08em] text-[#4e3524]
                                    sm:mt-4 sm:text-[12.5px] sm:tracking-[0.14em]">

                            INITIAL DEVELOPMENT

                        </div>

                        <p
                            class="mt-1.5 max-w-[300px]
                                  text-[7px] leading-[1.5] text-[#82766e]
                                  sm:mt-3 sm:text-[12px] sm:leading-[1.65]">

                            Membangun fondasi awal aplikasi
                            dan fitur utama.

                        </p>

                        <div
                            class="mt-auto flex w-full items-center justify-center
                                    gap-1.5 border-t border-[#e5d9d0]
                                    pt-2.5
                                    sm:gap-2 sm:pt-4">

                            <span
                                class="text-[8px] font-bold text-[#29221e]
                                         sm:text-[13px]">
                                {{ $credits['initial_development'] }}
                            </span>

                        </div>

                    </div>


                    {{-- CONTINUED --}}
                    <div
                        class="flex min-h-[175px] flex-col items-center
                                rounded-[14px] border border-[#dbe8f3]
                                bg-[#f4f8fc]
                                px-2 py-4 text-center
                                sm:min-h-[250px] sm:rounded-[18px]
                                sm:px-6 sm:py-7">

                        <div
                            class="flex h-10 w-10 items-center justify-center
                                    rounded-full bg-[#527eb5] text-white
                                    shadow-[0_5px_14px_rgba(82,126,181,0.16)]
                                    sm:h-14 sm:w-14">

                            <i data-lucide="git-branch" class="h-[18px] w-[18px] sm:h-6 sm:w-6"></i>

                        </div>

                        <div
                            class="mt-2.5 text-[7px] font-bold
                                    tracking-[0.08em] text-[#304b68]
                                    sm:mt-4 sm:text-[12.5px] sm:tracking-[0.14em]">

                            CONTINUED DEVELOPMENT

                        </div>

                        <p
                            class="mt-1.5 max-w-[300px]
                                  text-[7px] leading-[1.5] text-[#718095]
                                  sm:mt-3 sm:text-[12px] sm:leading-[1.65]">

                            Mengembangkan, memperbaiki,
                            dan menjaga keberlanjutan aplikasi.

                        </p>

                        <div
                            class="mt-auto flex w-full items-center justify-center
                                    gap-1.5 border-t border-[#d8e4ee]
                                    pt-2.5
                                    sm:gap-2 sm:pt-4">

                            <span
                                class="text-[8px] font-bold text-[#29221e]
                                         sm:text-[13px]">
                                {{ $credits['continued_development'] }}
                            </span>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 FOOTER
            ====================================================== --}}
            <div class="mt-7 flex items-center justify-center gap-2.5
                        sm:mt-9 sm:gap-3.5">

                <span class="h-px w-8 bg-[#d8d0ca] sm:w-16"></span>

                <p class="m-0 whitespace-nowrap text-[8px] text-[#9a8e85]
                          sm:text-[11px]">
                    © {{ date('Y') }} WAG - Presensi Digital
                </p>

                <span class="h-px w-8 bg-[#d8d0ca] sm:w-16"></span>

            </div>

        </div>
    </div>
@endsection
