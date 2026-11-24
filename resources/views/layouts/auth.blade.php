<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    // Halaman masuk dan kata sandi: panel identitas (foto Balai Desa + logo daerah) di kiri pada desktop,
    // pita foto ringkas di atas kartu pada HP. Tanpa foto, panel memakai latar hijau polos.
    $hero = \App\Support\KontenPublik::fotoHero();
    $logo = \App\Support\LogoDaerah::data();
    $wilayah = config('desa.wilayah');
@endphp
<body class="min-h-screen bg-background lg:grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
    {{-- Panel identitas (desktop) --}}
    <aside class="relative hidden overflow-hidden bg-primary-dark lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:p-12 xl:p-16" aria-label="Identitas situs">
        @if ($hero['foto'])
            <img src="{{ $hero['foto'] }}" @if ($hero['srcset']) srcset="{{ $hero['srcset'] }}" sizes="55vw" @endif alt="" loading="lazy" decoding="async" class="absolute inset-0 size-full object-cover object-[20%_50%]">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgb(15_51_33/0.82)_0%,rgb(20_73_26/0.72)_45%,rgb(15_51_33/0.94)_100%)]" aria-hidden="true"></div>

        <a href="{{ route('home') }}" class="relative inline-flex min-h-11 items-center gap-2 self-start rounded-control type-label text-surface/90 hover:text-surface hover:underline">
            <x-ui.icon name="arrow-right" class="size-4 rotate-180" /> Kembali ke situs publik
        </a>

        <div class="relative flex max-w-[460px] flex-col gap-5">
            @if ($logo)
                <img src="{{ $logo['url'] }}" alt="{{ $logo['alt'] }}" width="{{ $logo['lebar'] }}" height="{{ $logo['tinggi'] }}" loading="lazy" decoding="async" class="h-24 w-auto self-start object-contain drop-shadow-[0_6px_18px_rgb(0_0_0/0.35)]">
            @else
                <span class="h-16 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
            @endif
            <div class="flex flex-col gap-2">
                <p class="text-[13px] font-semibold uppercase tracking-[0.16em] text-accent-soft">Dashboard Admin</p>
                <p class="type-h1 text-surface">{{ $wilayah['sebutan'] }} {{ $wilayah['nama'] }}</p>
                <p class="type-body text-surface/85">{{ $wilayah['strip'] }}</p>
            </div>
            <p class="max-w-[400px] type-body-sm text-surface/75">Kelola profil desa, APBDes, potensi, berita, agenda, dan aspirasi warga dari satu tempat.</p>
        </div>

        <p class="relative flex items-center gap-2 type-label-sm text-surface/70">
            <x-ui.icon name="shield" class="size-4" /> Khusus perangkat desa yang memiliki akun
        </p>
    </aside>

    {{-- Kolom formulir --}}
    <div class="flex min-h-screen flex-col lg:min-h-0">
        {{-- Pita foto (HP dan tablet) --}}
        <div class="relative h-36 overflow-hidden bg-primary-dark sm:h-44 lg:hidden" aria-hidden="true">
            @if ($hero['foto'])
                <img src="{{ $hero['foto'] }}" @if ($hero['srcset']) srcset="{{ $hero['srcset'] }}" sizes="100vw" @endif alt="" loading="lazy" decoding="async" class="size-full object-cover object-[20%_45%]">
            @endif
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgb(15_51_33/0.55)_0%,rgb(15_51_33/0.85)_100%)]"></div>
            <p class="absolute inset-x-0 top-5 text-center text-[12px] font-semibold uppercase tracking-[0.16em] text-accent-soft">Dashboard Admin</p>
        </div>

        <main id="konten" class="relative mx-auto -mt-16 flex w-full max-w-[460px] flex-1 flex-col items-center gap-6 px-4 pb-10 sm:-mt-20 lg:mt-0 lg:justify-center lg:py-12">
            @yield('content')
        </main>
    </div>
</body>
</html>
