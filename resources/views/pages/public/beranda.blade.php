@extends('layouts.public')

@section('title', 'Beranda')
@section('description', $content['hero']['deskripsi'])

@section('content')
    {{--
        Hero: latar hijau tua dengan foto desa samar di belakang bila ada.
        Data singkat desa ikut di dalam hero; kartu layanan di bawahnya menumpang di tepi bawah hero.
    --}}
    @php
        $namaDesa = config('desa.nama');
        $judulHero = $content['hero']['judul'];
        $awalJudul = str_ends_with($judulHero, $namaDesa) ? trim(substr($judulHero, 0, -strlen($namaDesa))) : null;
        $sumberStatistik = collect($statistik)->pluck('source')->unique();
        $sumberTunggal = $sumberStatistik->count() === 1 ? $sumberStatistik->first() : null;
    @endphp
    <section aria-labelledby="hero-judul" class="on-dark relative isolate overflow-hidden bg-primary-dark text-surface">
        @if ($content['hero']['foto'])
            {{-- Foto desa sebagai latar samar (sedikit diburamkan agar teks tetap mudah dibaca). --}}
            <img src="{{ $content['hero']['foto'] }}" alt="" aria-hidden="true" loading="lazy" class="absolute inset-0 -z-20 size-full scale-105 object-cover object-[center_40%] blur-[1px]">
        @endif
        <div @class([
            'absolute inset-0 -z-10',
            'bg-[radial-gradient(60%_70%_at_85%_10%,rgb(249_168_37/0.16),transparent_70%),linear-gradient(180deg,#14491a_0%,#0f3a14_100%)]' => ! $content['hero']['foto'],
            // Dengan foto: lapisan hijau tembus pandang, lebih pekat di sisi kiri tempat teks berada.
            // Di HP teks memenuhi lebar layar, jadi lapisannya rata; di layar lebar menipis ke kanan.
            'bg-[linear-gradient(180deg,rgb(20_73_26/0.8)_0%,rgb(20_73_26/0.72)_100%)] md:bg-[radial-gradient(60%_70%_at_85%_10%,rgb(249_168_37/0.1),transparent_70%),linear-gradient(90deg,rgb(20_73_26/0.86)_0%,rgb(27_94_32/0.62)_50%,rgb(27_94_32/0.3)_100%)]' => $content['hero']['foto'],
        ])></div>

        {{-- Ruang atas ditambah setinggi header, karena di beranda header melayang di atas hero. --}}
        <div class="container-page grid gap-10 pb-28 pt-[140px] md:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)] md:items-center md:gap-10 md:pb-32 md:pt-[164px] lg:gap-16 lg:pb-40 lg:pt-[188px]">
            <div class="pembuka flex min-w-0 flex-col gap-5 [text-shadow:0_1px_14px_rgb(0_0_0/0.28)]">
                <p class="inline-flex w-fit items-center gap-2 rounded-pill border border-surface/25 bg-surface/5 px-3.5 py-1.5 type-label-sm text-surface/90 backdrop-blur-sm" style="--urutan: 0">
                    <span class="size-2 rounded-full bg-accent" aria-hidden="true"></span>
                    {{ $content['hero']['eyebrow'] }}
                </p>
                <h1 id="hero-judul" class="type-display text-surface lg:text-[56px] lg:leading-[1.08]" style="--urutan: 1">
                    @if ($awalJudul)
                        {{ $awalJudul }} <span class="block text-accent">{{ $namaDesa }}</span>
                    @else
                        {{ $judulHero }}
                    @endif
                </h1>
                <p class="max-w-[60ch] type-body text-surface/80 lg:type-body-lg" style="--urutan: 2">{{ $content['hero']['deskripsi'] }}</p>

                <div class="flex flex-col gap-3 pt-1 [text-shadow:none] sm:flex-row sm:flex-wrap" style="--urutan: 3">
                    <x-ui.button :href="route('aspirasi.create')" variant="accent" icon="message">Sampaikan aspirasi</x-ui.button>
                    <x-ui.button :href="route('apbdes')" variant="terang" icon="file">Lihat APBDes</x-ui.button>
                </div>

                {{-- Data singkat desa --}}
                <div class="mt-2 flex flex-col gap-3 border-t border-surface/15 pt-5" style="--urutan: 4">
                    <h2 class="sr-only">Data singkat desa</h2>
                    <dl x-data x-muncul class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-4 md:grid-cols-2 lg:gap-x-8 xl:grid-cols-4">
                        @foreach ($statistik as $stat)
                            <div class="bertahap flex min-w-0 flex-col-reverse gap-0.5" style="--urutan: {{ $loop->index }}">
                                <dt class="type-label-sm text-surface/70">{{ $stat['label'] }}</dt>
                                {{-- Angka asli untuk pembaca layar; versi beranimasi hanya visual. --}}
                                <dd class="type-h3 whitespace-nowrap tabular-nums text-surface xl:type-h2">
                                    <span class="sr-only">{{ $stat['value'] }}</span>
                                    <span aria-hidden="true" x-data="hitungNaik(@js($stat['value']))" x-text="tampil">{{ $stat['value'] }}</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($sumberTunggal)
                        <p class="type-label-sm text-surface/60">{{ preg_match('/^sumber\b/i', $sumberTunggal) ? $sumberTunggal : 'Sumber: '.$sumberTunggal }}</p>
                    @endif
                </div>
            </div>

            {{-- Kartu visual: foto desa yang sama dengan latar bila ada, sebelum itu ilustrasi ladang jagung (bukan foto AI/stok). --}}
            <div class="relative">
                <div class="overflow-hidden rounded-[22px] shadow-[0_30px_60px_rgb(0_0_0/0.35)] ring-1 ring-surface/15">
                    @if ($content['hero']['foto'])
                        <img src="{{ $content['hero']['foto'] }}" alt="Pemandangan Desa Marga Agung" loading="lazy" class="h-[260px] w-full object-cover md:h-[360px] lg:h-[460px]">
                    @else
                        <x-public.ilustrasi-ladang class="h-[260px] md:h-[360px] lg:h-[460px]" />
                    @endif
                </div>

                {{-- Kartu lokasi melayang di sudut kiri atas --}}
                <div class="kartu-melayang absolute -left-4 -top-5 hidden max-w-[260px] items-center gap-3 rounded-[14px] border border-surface/15 bg-primary-dark/85 p-3.5 pr-5 shadow-[0_16px_40px_rgb(0_0_0/0.35)] backdrop-blur-md sm:flex lg:-left-10 lg:-top-7">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-accent text-text">
                        <x-ui.icon name="map-pin" class="size-5" />
                    </span>
                    <span class="flex min-w-0 flex-col">
                        <span class="type-label-sm text-surface/70">Lokasi desa</span>
                        <span class="type-label text-surface">Kec. Jati Agung, Lampung Selatan</span>
                    </span>
                </div>

                {{-- Bilah tautan profil di bagian bawah kartu --}}
                <a href="{{ route('profil') }}" class="group absolute inset-x-3 bottom-3 flex items-center gap-3.5 rounded-[14px] border border-surface/15 bg-primary-dark/80 p-3 pr-4 backdrop-blur-md transition-colors hover:bg-primary-dark/95 md:inset-x-4 md:bottom-4 md:p-3.5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-surface text-primary transition-transform duration-300 group-hover:scale-110">
                        <x-ui.icon name="arrow-right" class="size-5" />
                    </span>
                    <span class="flex min-w-0 flex-col">
                        <span class="type-label-sm text-accent">Profil desa</span>
                        <span class="type-label text-surface">Kenali sejarah, visi misi, dan perangkat desa</span>
                    </span>
                </a>
            </div>
        </div>
    </section>

    {{-- Layanan informasi: kartu menumpang di tepi bawah hero --}}
    <section aria-labelledby="layanan-judul" class="bg-surface">
        <div class="container-page relative z-10 -mt-16 flex flex-col gap-6 pb-12 md:-mt-20 lg:-mt-24 lg:pb-[72px]">
            <h2 id="layanan-judul" class="sr-only">Layanan informasi</h2>
            <div x-data x-muncul class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5 lg:gap-5">
                @foreach ($content['akses'] as $item)
                    <x-ui.card-quick
                        :title="$item['title']" :description="$item['description']" :icon="$item['icon']" :href="route($item['route'])"
                        :row="$loop->last"
                        :class="'bertahap shadow-[0_14px_34px_rgb(20_73_26/0.14)] '.($loop->last ? 'max-md:col-span-2' : '')"
                        :style="'--urutan: '.$loop->index"
                    />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Potensi unggulan --}}
    <section aria-labelledby="potensi-judul" class="bg-background">
        <div class="container-page flex flex-col gap-6 py-10 lg:gap-8 lg:py-[72px]">
            <div class="flex items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <h2 id="potensi-judul" class="type-h2 md:type-h1 text-text">Potensi unggulan</h2>
                    <p class="type-body max-w-[640px] text-muted">{{ $content['potensi']['pengantar'] }}</p>
                </div>
                <div class="hidden shrink-0 md:block">
                    <x-ui.button :href="route('potensi')" variant="ghost" icon="arrow-right">Lihat semua potensi</x-ui.button>
                </div>
            </div>

            <div x-data x-muncul class="grid gap-6 lg:grid-cols-2">
                @php $unggulan = $content['potensi']['unggulan']; @endphp
                @if ($unggulan)
                <article class="flex flex-col overflow-hidden rounded-card bg-primary-dark">
                    @if ($unggulan['foto'])
                        <img src="{{ $unggulan['foto'] }}" alt="{{ $unggulan['foto_keterangan'] }}" loading="lazy" class="h-[220px] w-full object-cover">
                    @else
                        <x-ui.image-placeholder :caption="$unggulan['foto_keterangan']" class="h-[220px]" />
                    @endif
                    <div class="flex flex-1 flex-col items-start gap-3 p-6 lg:p-7">
                        <x-ui.badge tone="aksen">{{ $unggulan['badge'] }}</x-ui.badge>
                        <h3 class="type-h1 text-surface">{{ $unggulan['nama'] }}</h3>
                        <p class="type-body text-primary-soft">{{ $unggulan['deskripsi'] }}</p>
                        <a href="{{ route('potensi') }}" class="mt-auto inline-flex min-h-11 items-center gap-1.5 type-label text-accent hover:underline">
                            Lihat data pertanian
                            <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                </article>
                @endif

                <div class="flex flex-col gap-4">
                    <h3 class="type-h3 text-text">Usaha warga (UMKM)</h3>
                    <div class="grid content-start gap-4 sm:grid-cols-2">
                        @forelse ($content['potensi']['umkm'] as $usaha)
                            <x-ui.card-umkm
                                :name="$usaha['name']" :products="$usaha['products']" :description="$usaha['description']"
                                :whatsapp="$usaha['whatsapp']" :whatsapp-approved="$usaha['whatsappApproved']" :image="$usaha['image']" :image-alt="$usaha['imageAlt']"
                            />
                        @empty
                            <p class="type-body text-muted sm:col-span-2">Belum ada usaha warga yang dipublikasikan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="md:hidden">
                <x-ui.button :href="route('potensi')" variant="secondary" icon="arrow-right" class="w-full">Lihat semua potensi</x-ui.button>
            </div>
        </div>
    </section>

    {{-- Berita terbaru dan agenda --}}
    <section aria-labelledby="berita-judul" class="bg-surface">
        <div class="container-page grid gap-10 py-10 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-8 lg:py-[72px]">
            <div class="flex flex-col gap-6">
                <div class="flex items-end justify-between gap-4">
                    <h2 id="berita-judul" class="type-h2 md:type-h1 text-text">Berita terbaru</h2>
                    <a href="{{ route('berita.index') }}" class="inline-flex min-h-11 items-center gap-1.5 type-label text-primary hover:underline">
                        <span class="md:hidden">Semua</span><span class="hidden md:inline">Semua berita</span>
                        <x-ui.icon name="arrow-right" class="size-[18px]" />
                    </a>
                </div>
                <div x-data x-muncul class="grid gap-4 md:grid-cols-2 lg:gap-5">
                    @forelse ($content['berita'] as $berita)
                        <x-ui.card-news
                            class="bertahap" :style="'--urutan: '.$loop->index"
                            :title="$berita['title']" :excerpt="$berita['excerpt']" :date="$berita['date']"
                            :category="$berita['category']" :href="route('berita.show', $berita['slug'])"
                        />
                    @empty
                        <p class="type-body text-muted md:col-span-2">Belum ada berita yang dipublikasikan.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="type-h2 text-text">Agenda desa</h2>
                    <a href="{{ route('berita.index') }}" class="inline-flex min-h-11 items-center gap-1 type-label text-primary hover:underline">
                        Semua <x-ui.icon name="chevron-right" class="size-4" />
                    </a>
                </div>
                <div x-data x-muncul class="grid gap-3 md:grid-cols-2 lg:grid-cols-1 lg:gap-4">
                    @forelse ($content['agenda'] as $agenda)
                        <x-ui.card-agenda
                            class="bertahap" :style="'--urutan: '.$loop->index" :relatif="$agenda['relatif'] ?? null"
                            :day="$agenda['day']" :month="$agenda['month']" :title="$agenda['title']"
                            :time="$agenda['time']" :location="$agenda['location']"
                        />
                    @empty
                        <p class="type-body text-muted md:col-span-2 lg:col-span-1">Belum ada agenda mendatang.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Ajakan Ruang Aspirasi --}}
    <section aria-labelledby="aspirasi-judul" class="bg-background">
        <div class="container-page pb-12 pt-10 lg:pb-[72px] lg:pt-14">
            <div x-data x-muncul class="flex flex-col gap-5 rounded-[20px] border border-border bg-surface p-6 md:flex-row md:items-center md:gap-6 md:px-12 md:py-10">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <x-ui.icon name="message" class="size-[26px]" />
                </span>
                <div class="flex flex-1 flex-col gap-2">
                    <h2 id="aspirasi-judul" class="type-h2 text-text">{{ $content['aspirasi']['judul'] }}</h2>
                    <p class="type-body text-muted">{{ $content['aspirasi']['deskripsi'] }}</p>
                </div>
                <x-ui.button :href="route('aspirasi.create')" icon="arrow-right" class="shrink-0">Tulis aspirasi</x-ui.button>
            </div>
        </div>
    </section>
@endsection
