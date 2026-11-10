<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#1b5e20">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Pratinjau saat tautan dibagikan (WhatsApp, Facebook, X, Telegram). --}}
    @php $gambarBagikan = trim($__env->yieldContent('og_image')) ?: \App\Support\KontenPublik::gambarBagikan(); @endphp
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@hasSection('title')@yield('title') — @endif{{ config('app.name') }}">
    @hasSection('description')
        <meta property="og:description" content="@yield('description')">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($gambarBagikan)
        <meta property="og:image" content="{{ $gambarBagikan }}">
        <meta property="og:image:alt" content="@yield('og_image_alt', 'Balai Desa Marga Agung')">
    @endif
    <meta name="twitter:card" content="{{ $gambarBagikan ? 'summary_large_image' : 'summary' }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a
        href="#konten"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-control focus:bg-accent focus:px-4 focus:py-3 focus:type-label focus:text-text"
    >Lewati ke konten</a>

    <x-public.header />

    <main id="konten" tabindex="-1" class="flex-1 focus:outline-none">
        @yield('content')
    </main>

    <x-ui.toast :message="session('toast')" />
</body>
</html>
