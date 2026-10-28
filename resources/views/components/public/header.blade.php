@php
    $menu = \App\Support\Navigation::publik();
    $aspirasiAktif = request()->routeIs('aspirasi.*');
@endphp

<header
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    x-on:resize.window="if (window.innerWidth >= 1024) open = false"
    class="on-dark bg-primary"
>
    <div class="container-page flex items-center justify-between py-2.5 lg:py-[18px]">
        <x-public.logo tone="gelap" />

        {{-- Navigasi desktop (>= 1024 px) --}}
        <nav aria-label="Navigasi utama" class="hidden items-center gap-1 lg:flex">
            @foreach ($menu as $item)
                @php $aktif = request()->routeIs(...$item['match']); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @if ($aktif) aria-current="page" @endif
                    class="relative flex min-h-11 items-center justify-center rounded-control px-3 py-2 type-label text-surface hover:bg-primary-dark"
                >
                    {{ $item['label'] }}
                    @if ($aktif)
                        <span class="absolute bottom-1 left-1/2 h-[3px] w-6 -translate-x-1/2 rounded-[2px] bg-accent" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach

            <x-ui.button
                :href="route('aspirasi.create')" variant="accent" size="s" icon="message" class="ml-1"
                :aria-current="$aspirasiAktif ? 'page' : null"
            >Ruang Aspirasi</x-ui.button>
        </nav>

        {{-- Tombol menu HP/tablet (< 1024 px) --}}
        <button
            type="button" x-ref="pemicu" x-on:click="open = true"
            x-bind:aria-expanded="open" aria-controls="menu-seluler" aria-label="Buka menu"
            class="flex size-11 items-center justify-center rounded-[10px] bg-primary-dark text-surface lg:hidden"
        >
            <x-ui.icon name="menu" class="size-[22px]" />
        </button>
    </div>

    {{-- Menu layar penuh untuk HP/tablet --}}
    <div
        id="menu-seluler" x-show="open" x-cloak x-trap.noscroll="open"
        role="dialog" aria-modal="true" aria-label="Menu navigasi"
        class="fixed inset-0 z-50 flex flex-col bg-background lg:hidden"
    >
        <div class="flex items-center justify-between bg-primary px-4 py-2.5">
            <x-public.logo tone="gelap" />
            <button
                type="button" x-on:click="open = false" autofocus aria-label="Tutup menu"
                class="flex size-11 items-center justify-center rounded-[10px] bg-primary-dark text-surface"
            >
                <x-ui.icon name="x" class="size-[22px]" />
            </button>
        </div>

        <nav aria-label="Navigasi utama" class="flex-1 overflow-y-auto bg-surface p-4">
            <ul class="flex flex-col gap-1">
                @foreach ($menu as $item)
                    @php $aktif = request()->routeIs(...$item['match']); @endphp
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @if ($aktif) aria-current="page" @endif
                            class="flex min-h-[52px] items-center gap-3.5 rounded-[10px] px-4 py-3.5 type-label {{ $aktif ? 'bg-primary-soft text-primary' : 'text-text hover:bg-primary-soft' }}"
                        >
                            <x-ui.icon :name="$item['icon']" class="size-[22px]" />
                            <span class="flex-1">{{ $item['mobile'] }}</span>
                            <x-ui.icon name="chevron-right" class="size-[18px] {{ $aktif ? '' : 'text-muted' }}" />
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="pt-4">
                <x-ui.button :href="route('aspirasi.create')" variant="accent" icon="message" class="w-full">Ruang Aspirasi</x-ui.button>
            </div>
        </nav>
    </div>
</header>
