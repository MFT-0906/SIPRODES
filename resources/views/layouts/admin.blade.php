<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — Dashboard {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background">
    <a
        href="#konten"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-control focus:bg-accent focus:px-4 focus:py-3 focus:type-label focus:text-text"
    >Lewati ke konten</a>

    <div
        x-data="{ open: false }"
        x-on:keydown.escape.window="if (open) { open = false; $nextTick(() => $refs.pemicu.focus()) }"
        x-on:resize.window="if (window.innerWidth >= 1024) open = false"
        class="min-h-screen"
    >
        {{-- Sidebar tetap (>= 1024 px) --}}
        <aside class="fixed inset-y-0 left-0 hidden w-[264px] lg:block">
            <x-admin.sidebar :menu="$menuAdmin" :pengguna="$pengguna" :peran-label="$peranLabel" :aspirasi-baru="$aspirasiBaru" />
        </aside>

        {{-- Laci menu untuk HP/tablet --}}
        <div
            id="menu-samping-admin" x-show="open" x-cloak x-trap.noscroll="open"
            role="dialog" aria-modal="true" aria-label="Menu admin"
            class="fixed inset-0 z-50 lg:hidden"
        >
            <div class="absolute inset-0 bg-[rgb(18_26_23/0.45)]" x-on:click="open = false; $nextTick(() => $refs.pemicu.focus())" aria-hidden="true"></div>
            <x-admin.sidebar :menu="$menuAdmin" :pengguna="$pengguna" :peran-label="$peranLabel" :aspirasi-baru="$aspirasiBaru" class="absolute inset-y-0 left-0 w-[300px] max-w-[calc(100%-72px)]" />
            <button
                type="button" x-on:click="open = false; $nextTick(() => $refs.pemicu.focus())" aria-label="Tutup menu"
                class="absolute left-[min(316px,calc(100%-60px))] top-4 flex size-11 items-center justify-center rounded-full bg-surface text-text"
            >
                <x-ui.icon name="x" class="size-5" />
            </button>
        </div>

        <div class="flex min-h-screen flex-col lg:pl-[264px]">
            <x-admin.topbar :judul="html_entity_decode(trim($__env->yieldContent('judul')))" :breadcrumb="html_entity_decode(trim($__env->yieldContent('breadcrumb'))) ?: null" :pengguna="$pengguna" />
            <main id="konten" tabindex="-1" class="flex-1 p-4 focus:outline-none lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <x-ui.toast :message="session('toast')" />
</body>
</html>
