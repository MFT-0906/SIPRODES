@extends('layouts.public')

@section('title', 'Profil Desa')
@section('description', $content['pengantar'])

@php
    $peta = $identitas['peta'];
    $alamatKantor = $identitas['kontak']['alamat'];
    $alamatTerverifikasi = $alamatKantor['verified'] && filled($alamatKantor['value']);
    $petaTerverifikasi = $peta['verified'] && filled($peta['url']);
@endphp

@section('content')
    {{-- Judul halaman --}}
    <x-public.kepala-halaman jejak="Profil Desa" judul="Profil Desa Marga Agung" :pengantar="$content['pengantar']" />

    {{-- Navigasi bagian --}}
    {{-- Menempel di bawah header saat digulir; menandai bagian yang sedang dibaca. --}}
    <nav aria-label="Bagian profil" x-data="sectionNav(['sejarah', 'visi-misi', 'struktur', 'lokasi'])" class="sticky top-16 z-30 border-b border-border bg-surface/95 backdrop-blur-md lg:top-[72px]">
        <div class="container-page flex gap-2 overflow-x-auto py-3 md:py-4">
            @foreach ([['sejarah', 'Sejarah'], ['visi-misi', 'Visi dan Misi'], ['struktur', 'Struktur Pemerintahan'], ['lokasi', 'Lokasi']] as [$id, $label])
                <x-ui.chip
                    href="#{{ $id }}" class="shrink-0"
                    x-on:click="active = '{{ $id }}'"
                    x-bind:aria-current="active === '{{ $id }}' ? 'true' : null"
                >{{ $label }}</x-ui.chip>
            @endforeach
        </div>
    </nav>

    <div class="bg-surface">
        <div class="container-page flex flex-col gap-12 pb-14 pt-8 lg:gap-14 lg:pb-20 lg:pt-12">
            {{-- Sejarah --}}
            <section id="sejarah" aria-labelledby="sejarah-judul" class="grid scroll-mt-20 gap-8 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-12">
                <div class="flex flex-col gap-4">
                    <h2 id="sejarah-judul" class="type-h2 text-text">Sejarah desa</h2>
                    <p class="type-body-lg max-w-[70ch] text-text">{{ $content['sejarah']['utama'] }}</p>
                    <p class="type-body max-w-[70ch] text-muted">{{ $content['sejarah']['lanjutan'] }}</p>
                    <x-public.perjalanan-desa class="mt-2" />
                </div>

                <section aria-labelledby="fakta-judul" class="flex flex-col self-start rounded-[14px] border border-border bg-surface p-6">
                    <h3 id="fakta-judul" class="type-h3 pb-1 text-text">Fakta singkat</h3>
                    <dl>
                        @foreach ($content['fakta'] as $fakta)
                            <div class="flex items-center justify-between gap-4 border-t border-border py-3">
                                <dt class="type-body-sm text-muted">{{ $fakta['label'] }}</dt>
                                <dd class="type-label text-right text-text">{{ $fakta['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="type-label-sm text-muted">{{ preg_match('/^sumber\b/i', $content['fakta_sumber']) ? $content['fakta_sumber'] : 'Sumber: '.$content['fakta_sumber'] }}</p>
                </section>
            </section>

            {{-- Visi dan misi --}}
            <section id="visi-misi" aria-labelledby="visi-misi-judul" class="flex scroll-mt-20 flex-col gap-6">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 id="visi-misi-judul" class="type-h2 text-text">Visi dan misi</h2>
                    @unless ($content['visi_misi']['resmi'])
                        <x-ui.badge tone="peringatan">Menunggu naskah resmi desa</x-ui.badge>
                    @endunless
                </div>
                @if (blank($content['visi_misi']['visi']) && $content['visi_misi']['misi'] === [])
                    {{-- Belum ada naskah sama sekali: keterangan jujur, bukan visi dan misi penampung. --}}
                    <x-ui.empty-state
                        icon="building" heading="h3"
                        title="Naskah visi dan misi belum tersedia"
                        description="Visi dan misi akan ditampilkan setelah naskah resminya disahkan dan diverifikasi oleh pemerintah desa."
                    />
                @else
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="flex flex-col gap-3 rounded-card bg-primary p-6 lg:p-8">
                            <p class="type-label-sm text-accent-soft">VISI</p>
                            @if (filled($content['visi_misi']['visi']))
                                <p class="type-h2 text-surface">{{ $content['visi_misi']['visi'] }}</p>
                            @else
                                <p class="type-body text-primary-soft">Naskah visi belum tersedia.</p>
                            @endif
                        </div>
                        <div class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-6 lg:p-8">
                            <p class="type-label-sm text-primary">MISI</p>
                            @if ($content['visi_misi']['misi'] !== [])
                                <ol class="flex flex-col gap-4">
                                    @foreach ($content['visi_misi']['misi'] as $misi)
                                        <li class="flex items-start gap-3">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-[14px] bg-primary-soft type-label text-primary">{{ $loop->iteration }}</span>
                                            <span class="type-body text-text">{{ $misi }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="type-body text-muted">Naskah misi belum tersedia.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </section>

            {{-- Struktur pemerintahan --}}
            <section id="struktur" aria-labelledby="struktur-judul" class="flex scroll-mt-20 flex-col gap-6">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 id="struktur-judul" class="type-h2 text-text">Struktur pemerintahan desa</h2>
                    @unless ($content['perangkat']['resmi'])
                        <x-ui.badge tone="peringatan">Nama dan foto menunggu data resmi desa</x-ui.badge>
                    @endunless
                </div>
                @if ($content['perangkat']['daftar'] !== [])
                    <x-public.bagan-perangkat :daftar="$content['perangkat']['daftar']" />
                @else
                    <x-ui.empty-state icon="users" heading="h3" title="Data perangkat desa belum tersedia" description="Susunan perangkat desa akan ditampilkan setelah diisi oleh pemerintah desa." />
                @endif
            </section>

            {{-- Lokasi dan geografis --}}
            <section id="lokasi" aria-labelledby="lokasi-judul" class="flex scroll-mt-20 flex-col gap-6 lg:gap-8">
                <div class="flex flex-col items-start gap-4">
                    <h2 id="lokasi-judul" class="type-h2 text-text">Lokasi dan geografis</h2>
                    <p class="flex items-start gap-2.5 type-body text-text">
                        <x-ui.icon name="map-pin" class="mt-0.5 size-5 text-primary" />
                        <span>{{ $content['lokasi']['alamat_wilayah'] }}</span>
                    </p>
                    {{-- Alamat kantor dan tautan peta hanya tampil setelah diverifikasi pemerintah desa. --}}
                    @if ($alamatTerverifikasi)
                        <p class="flex items-start gap-2.5 type-body text-text">
                            <x-ui.icon name="building" class="mt-0.5 size-5 text-primary" />
                            <span>{{ $alamatKantor['value'] }}</span>
                        </p>
                    @endif
                    @if ($petaTerverifikasi)
                        <x-ui.button :href="$peta['url']" variant="secondary" icon="map-pin" target="_blank" rel="noopener noreferrer">
                            Buka di Google Maps<span class="sr-only"> (membuka tab baru)</span>
                        </x-ui.button>
                    @endif
                </div>

                <x-public.peta :peta="$peta" />
            </section>
        </div>
    </div>
@endsection
