@extends('layouts.public')

@section('title', 'Beranda')
@section('description', $content['hero']['deskripsi'])

@section('content')
    {{-- Hero --}}
    <section aria-labelledby="hero-judul" class="bg-background">
        <div class="container-page flex flex-col gap-6 py-8 md:flex-row md:items-center md:gap-10 md:py-10 lg:gap-16 lg:py-[72px]">
            <div class="order-2 flex min-w-0 flex-col gap-4 md:order-1 md:flex-1 lg:gap-5">
                <p class="type-label text-primary">{{ $content['hero']['eyebrow'] }}</p>
                <h1 id="hero-judul" class="type-display text-text">{{ $content['hero']['judul'] }}</h1>
                <p class="type-body text-muted lg:type-body-lg">{{ $content['hero']['deskripsi'] }}</p>

                <div class="flex flex-col gap-3 pt-2 lg:flex-row lg:flex-wrap">
                    <x-ui.button :href="route('apbdes')" icon="file">Lihat APBDes</x-ui.button>
                    <x-ui.button :href="route('aspirasi.create')" variant="accent" icon="message">Sampaikan aspirasi</x-ui.button>
                    <div class="hidden lg:-ml-3.5 lg:block">
                        <x-ui.button :href="route('profil')" variant="ghost" icon="arrow-right">Jelajahi profil desa</x-ui.button>
                    </div>
                </div>
            </div>

            {{-- TODO: ganti dengan foto asli desa yang telah disetujui (bukan foto AI/stok). --}}
            <div class="order-1 md:order-2 md:w-[320px] md:shrink-0 lg:w-[480px] xl:w-[576px]">
                <x-ui.image-placeholder caption="Foto desa" class="h-[190px] rounded-card md:h-[280px] lg:h-[400px]" />
            </div>
        </div>
    </section>

    {{-- Data singkat desa: pita warna penuh agar bagian ini terpisah dari "Layanan informasi" di bawahnya. --}}
    <section aria-labelledby="ringkasan-judul" class="bg-primary">
        <div class="container-page flex flex-col gap-6 py-10 lg:gap-8 lg:py-14">
            <h2 id="ringkasan-judul" class="type-h2 md:type-h1 text-surface">Data singkat desa</h2>
            @php
                // Bila semua angka bersumber sama, sumbernya ditulis sekali di bawah deret angka, bukan diulang di tiap angka.
                $sumberStatistik = collect($statistik)->pluck('source')->unique();
                $sumberTunggal = $sumberStatistik->count() === 1 ? $sumberStatistik->first() : null;
            @endphp
            <dl class="grid grid-cols-2 gap-x-4 gap-y-6 md:grid-cols-4 lg:gap-x-8">
                @foreach ($statistik as $stat)
                    <div class="flex min-w-0 flex-col gap-1">
                        <dt class="type-label text-primary-soft">{{ $stat['label'] }}</dt>
                        <dd class="type-h2 sm:type-h1 tabular-nums text-surface">{{ $stat['value'] }}</dd>
                        @unless ($sumberTunggal)
                            <dd class="type-label-sm text-primary-soft">{{ $stat['source'] }}</dd>
                        @endunless
                    </div>
                @endforeach
            </dl>
            @if ($sumberTunggal)
                <p class="type-label-sm text-primary-soft">{{ preg_match('/^sumber\b/i', $sumberTunggal) ? $sumberTunggal : 'Sumber: '.$sumberTunggal }}</p>
            @endif
        </div>
    </section>

    {{-- Layanan informasi --}}
    <section aria-labelledby="layanan-judul" class="bg-surface">
        <div class="container-page flex flex-col gap-6 pb-12 pt-10 lg:gap-8 lg:pb-[72px] lg:pt-14">
            <h2 id="layanan-judul" class="type-h2 md:type-h1 text-text">Layanan informasi</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5 lg:gap-5">
                @foreach ($content['akses'] as $item)
                    <x-ui.card-quick
                        :title="$item['title']" :description="$item['description']" :icon="$item['icon']" :href="route($item['route'])"
                        :row="$loop->last"
                        :class="$loop->last ? 'max-md:col-span-2' : ''"
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

            <div class="grid gap-6 lg:grid-cols-2">
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
                <div class="grid gap-4 md:grid-cols-2 lg:gap-5">
                    @forelse ($content['berita'] as $berita)
                        <x-ui.card-news
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
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-1 lg:gap-4">
                    @forelse ($content['agenda'] as $agenda)
                        <x-ui.card-agenda
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
            <div class="flex flex-col gap-5 rounded-[20px] border border-border bg-surface p-6 md:flex-row md:items-center md:gap-6 md:px-12 md:py-10">
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
