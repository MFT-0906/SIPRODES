@php
    $menu = \App\Support\Navigation::publik();
    $aspirasiAktif = request()->routeIs('aspirasi.*');
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
    $jamLayanan = $identitas['kontak']['jam_layanan']['value'];
    $hariIni = now()->locale('id')->translatedFormat('l, j F Y');

    // Di beranda, header melayang transparan di atas foto hero dan berubah putih saat halaman digulir.
    $transparan = request()->routeIs('home');

    // Pintasan ke bagian halaman Profil (menu turun di desktop, sub-menu di HP).
    $subProfil = [
        ['#sejarah', 'Sejarah desa', 'Asal-usul transmigrasi bedol desa', 'clock'],
        ['#visi-misi', 'Visi dan misi', 'Arah pembangunan desa', 'eye'],
        ['#struktur', 'Struktur pemerintahan', 'Kepala desa dan perangkat', 'users'],
        ['#lokasi', 'Lokasi dan geografis', 'Wilayah dan peta desa', 'map-pin'],
    ];
@endphp

<header
    x-data="{ open: false, gulir: false, transparan: @js($transparan) }"
    x-init="gulir = window.scrollY > 8"
    x-on:keydown.escape.window="open = false"
    x-on:resize.window="if (window.innerWidth >= 1024) open = false"
    x-on:scroll.window.passive="gulir = window.scrollY > 8"
    data-terang="{{ $transparan ? 'true' : 'false' }}"
    x-bind:data-terang="transparan && ! gulir ? 'true' : 'false'"
    @class([
        'group/header z-40',
        // Beranda: melayang di atas hero. Halaman lain: lengket di atas konten.
        'fixed inset-x-0 top-0' => $transparan,
        'sticky top-0' => ! $transparan,
    ])
>
    {{-- Bilah atas: wilayah, tanggal, dan jam layanan. Menutup saat halaman digulir agar header lebih ringkas. --}}
    <div
        class="max-h-10 overflow-hidden bg-primary-dark text-surface/85 transition-[max-height,background-color] duration-300 group-data-[terang=true]/header:bg-black/20"
        x-bind:class="gulir && 'max-h-0!'"
    >
        <div class="container-page flex min-h-9 items-center justify-between gap-4 py-1.5">
            <p role="region" aria-label="Wilayah administrasi" class="flex min-w-0 items-center gap-2 type-label-sm">
                <x-ui.icon name="map-pin" class="size-3.5 text-accent" />
                <span class="truncate md:hidden">{{ $identitas['wilayah_strip_pendek'] }}</span>
                <span class="hidden truncate md:inline">{{ $identitas['wilayah_strip'] }}</span>
            </p>
            <div class="hidden shrink-0 items-center gap-5 type-label-sm md:flex">
                @if ($jamLayanan)
                    <span class="flex items-center gap-1.5"><x-ui.icon name="clock" class="size-3.5 text-accent" />Layanan: {{ $jamLayanan }}</span>
                @endif
                <span class="flex items-center gap-1.5"><x-ui.icon name="calendar" class="size-3.5 text-accent" />{{ $hariIni }}</span>
            </div>
        </div>
    </div>

    {{-- Bilah utama --}}
    <div
        class="border-b border-border bg-surface transition-[background-color,border-color,box-shadow] duration-300 group-data-[terang=true]/header:border-surface/15 group-data-[terang=true]/header:bg-transparent"
        x-bind:class="gulir && 'bg-surface/95 shadow-[0_6px_24px_rgb(20_73_26/0.10)] backdrop-blur-md'"
    >
        <div class="container-page flex items-center justify-between gap-6 py-2.5 lg:py-3.5">
            <x-public.logo tone="adaptif" tanpa-caption />

            {{-- Navigasi desktop (>= 1024 px) --}}
            <nav aria-label="Navigasi utama" class="hidden items-center gap-0.5 lg:flex">
                @foreach ($menu as $item)
                    @php
                        $aktif = request()->routeIs(...$item['match']);
                        $kelasMenu = 'relative flex min-h-11 items-center gap-1.5 whitespace-nowrap rounded-control px-2.5 py-2 type-label transition-colors xl:px-3 '
                            .($aktif
                                ? 'text-primary group-data-[terang=true]/header:text-surface'
                                : 'text-text hover:bg-primary-soft hover:text-primary group-data-[terang=true]/header:text-surface/90 group-data-[terang=true]/header:hover:bg-surface/10 group-data-[terang=true]/header:hover:text-surface');
                    @endphp

                    @if ($item['route'] === 'profil')
                        {{-- Profil Desa dengan menu turun: terbuka saat disorot kursor atau tombol panah diklik. --}}
                        <div
                            class="relative" x-data="{ sub: false }"
                            x-on:mouseenter="sub = true" x-on:mouseleave="sub = false"
                            x-on:keydown.escape.stop="sub = false; $refs.pemicuSub.focus()"
                            x-on:focusout="if (! $el.contains($event.relatedTarget)) sub = false"
                        >
                            <div class="flex items-center">
                                <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif class="{{ $kelasMenu }} pr-1.5">
                                    <x-ui.icon :name="$item['icon']" class="hidden size-[18px] opacity-70 xl:block" />
                                    {{ $item['label'] }}
                                    @if ($aktif)
                                        <span class="absolute bottom-0.5 left-1/2 h-[3px] w-6 -translate-x-1/2 rounded-[2px] bg-accent" aria-hidden="true"></span>
                                    @endif
                                </a>
                                <button
                                    type="button" x-ref="pemicuSub" x-on:click="sub = ! sub"
                                    x-bind:aria-expanded="sub" aria-controls="menu-profil" aria-label="Tampilkan bagian Profil Desa"
                                    class="relative -ml-1 flex size-8 items-center justify-center rounded-control text-muted transition-colors hover:text-primary group-data-[terang=true]/header:text-surface/80 after:absolute after:-inset-1.5"
                                >
                                    <x-ui.icon name="chevron-down" class="size-4 transition-transform duration-200" x-bind:class="sub && 'rotate-180'" />
                                </button>
                            </div>
                            <div
                                id="menu-profil" x-show="sub" x-cloak
                                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="absolute left-0 top-full w-[340px] pt-2"
                            >
                                <ul class="flex flex-col gap-0.5 rounded-[14px] border border-border bg-surface p-2 shadow-[0_18px_40px_rgb(20_73_26/0.16)]">
                                    @foreach ($subProfil as [$jangkar, $judul, $keterangan, $ikon])
                                        <li>
                                            <a href="{{ route('profil').$jangkar }}" class="group/sub flex items-start gap-3 rounded-[10px] p-3 transition-colors hover:bg-primary-soft focus-visible:bg-primary-soft">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-[9px] bg-primary-soft text-primary transition-colors group-hover/sub:bg-primary group-hover/sub:text-accent">
                                                    <x-ui.icon :name="$ikon" class="size-[18px]" />
                                                </span>
                                                <span class="flex flex-col">
                                                    <span class="type-label text-text">{{ $judul }}</span>
                                                    <span class="type-body-sm text-muted">{{ $keterangan }}</span>
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif class="{{ $kelasMenu }}">
                            <x-ui.icon :name="$item['icon']" class="hidden size-[18px] opacity-70 xl:block" />
                            {{ $item['label'] }}
                            @if ($aktif)
                                <span class="absolute bottom-0.5 left-1/2 h-[3px] w-6 -translate-x-1/2 rounded-[2px] bg-accent" aria-hidden="true"></span>
                            @endif
                        </a>
                    @endif
                @endforeach

                <x-ui.button
                    :href="route('aspirasi.create')" variant="accent" size="s" icon="message" class="ml-2 shrink-0 rounded-pill! px-4"
                    :aria-current="$aspirasiAktif ? 'page' : null"
                >Ruang Aspirasi</x-ui.button>
            </nav>

            {{-- Tombol menu HP/tablet (< 1024 px) --}}
            <button
                type="button" x-ref="pemicu" x-on:click="open = true"
                x-bind:aria-expanded="open" aria-controls="menu-seluler" aria-label="Buka menu"
                class="flex size-11 items-center justify-center rounded-[10px] bg-primary-soft text-primary transition-colors group-data-[terang=true]/header:bg-surface/15 group-data-[terang=true]/header:text-surface lg:hidden"
            >
                <x-ui.icon name="menu" class="size-[22px]" />
            </button>
        </div>
    </div>

    {{-- Menu layar penuh untuk HP/tablet --}}
    <div
        id="menu-seluler" x-show="open" x-cloak x-trap.noscroll="open"
        role="dialog" aria-modal="true" aria-label="Menu navigasi"
        class="fixed inset-0 z-50 flex flex-col bg-background lg:hidden"
    >
        <div class="flex items-center justify-between border-b border-border bg-surface px-4 py-2.5">
            <x-public.logo tone="terang" tanpa-caption />
            <button
                type="button" x-on:click="open = false" autofocus aria-label="Tutup menu"
                class="flex size-11 items-center justify-center rounded-[10px] bg-primary-soft text-primary"
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
                        @if ($item['route'] === 'profil')
                            <ul class="mb-1 ml-[52px] flex flex-col border-l border-border">
                                @foreach ($subProfil as [$jangkar, $judul])
                                    <li>
                                        <a href="{{ route('profil').$jangkar }}" x-on:click="open = false" class="flex min-h-11 items-center px-4 type-body-sm text-muted hover:text-primary">{{ $judul }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="pt-4">
                <x-ui.button :href="route('aspirasi.create')" variant="accent" icon="message" class="w-full">Ruang Aspirasi</x-ui.button>
            </div>
        </nav>
    </div>
</header>
