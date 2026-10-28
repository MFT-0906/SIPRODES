<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a
        href="#konten"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-control focus:bg-accent focus:px-4 focus:py-3 focus:type-label focus:text-text"
    >Lewati ke konten</a>

    <x-public.header />
    <x-public.region-strip />

    <main id="konten" tabindex="-1" class="flex-1 focus:outline-none">
        @yield('content')
    </main>

    <x-public.footer />

    <x-ui.toast :message="session('toast')" />
</body>
</html>
