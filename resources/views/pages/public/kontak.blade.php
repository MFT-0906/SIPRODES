@extends('layouts.public')

@section('title', 'Kontak & Lokasi')
@section('description', 'Hubungi Pemerintah Desa Marga Agung melalui saluran resmi.')

@php
    // Telepon, email, alamat, jam layanan, dan peta hanya tampil setelah diverifikasi pemerintah desa.
    $kontak = $identitas['kontak'];
    $tersedia = fn (string $kunci) => $kontak[$kunci]['verified'] && filled($kontak[$kunci]['value']);
    $peta = $identitas['peta'];
    $petaTerverifikasi = $peta['verified'] && filled($peta['url']);
    $wilayah = $statistik['Jumlah dusun']['value'].' · luas ±'.$statistik['Luas wilayah']['value'];
@endphp

@section('content')
    {{-- Judul halaman --}}
    <section class="bg-background">
        <div class="container-page flex flex-col gap-3 py-8 lg:py-10">
            <x-ui.breadcrumb :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => 'Kontak & Lokasi']]" />
            <h1 class="type-h1 text-text">Kontak &amp; Lokasi Kantor Desa</h1>
            <p class="type-body max-w-[760px] text-muted">Hubungi Pemerintah Desa Marga Agung melalui saluran resmi berikut.</p>
        </div>
    </section>

    <div class="bg-surface">
        <div class="container-page flex flex-col gap-6 pb-14 pt-8 lg:gap-8 lg:pb-20 lg:pt-12">
            {{-- Saluran kontak --}}
            <ul class="grid gap-4 md:grid-cols-3 lg:gap-6">
                <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                    <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="phone" class="size-6" /></span>
                    @if ($tersedia('telepon'))
                        <h2 class="type-h3 text-text">{{ $kontak['telepon']['value'] }}</h2>
                        @if ($tersedia('jam_layanan'))
                            <p class="type-body-sm text-muted">{{ $kontak['jam_layanan']['value'] }}</p>
                        @endif
                        <x-ui.button :href="'tel:'.preg_replace('/[^\d+]/', '', $kontak['telepon']['value'])" variant="secondary" icon="phone" class="mt-auto w-full">Telepon</x-ui.button>
                    @else
                        <h2 class="type-h3 text-text">Telepon kantor desa</h2>
                        <p class="type-body-sm text-muted">Nomor resmi akan ditampilkan setelah diverifikasi oleh pemerintah desa.</p>
                    @endif
                </li>

                <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                    <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="mail" class="size-6" /></span>
                    @if ($tersedia('email'))
                        <h2 class="type-h3 break-all text-text">{{ $kontak['email']['value'] }}</h2>
                        <x-ui.button :href="'mailto:'.$kontak['email']['value']" variant="secondary" icon="mail" class="mt-auto w-full">Kirim email</x-ui.button>
                    @else
                        <h2 class="type-h3 text-text">Email resmi desa</h2>
                        <p class="type-body-sm text-muted">Alamat email resmi akan ditampilkan setelah diverifikasi oleh pemerintah desa.</p>
                    @endif
                </li>

                <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                    <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="message" class="size-6" /></span>
                    <h2 class="type-h3 text-text">Formulir daring</h2>
                    <p class="type-body-sm text-muted">Pertanyaan, keluhan, atau saran</p>
                    <x-ui.button :href="route('aspirasi.create')" variant="secondary" icon="arrow-right" class="mt-auto w-full">Buka formulir</x-ui.button>
                </li>
            </ul>

            {{-- Kantor desa dan peta --}}
            <div class="grid gap-4 lg:grid-cols-[440px_minmax(0,1fr)] lg:gap-6">
                <section aria-labelledby="kantor-judul" class="flex flex-col gap-5 rounded-[14px] border border-border bg-surface p-6 lg:p-7">
                    <h2 id="kantor-judul" class="type-h2 text-text">Kantor desa</h2>

                    <div class="flex items-start gap-3">
                        <x-ui.icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-primary" />
                        <div class="flex flex-col gap-0.5">
                            <p class="type-label text-text">Alamat</p>
                            <p class="type-body-sm text-muted">
                                {{ $tersedia('alamat') ? $kontak['alamat']['value'] : 'Alamat resmi akan ditampilkan setelah diverifikasi oleh pemerintah desa.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <x-ui.icon name="clock" class="mt-0.5 size-5 shrink-0 text-primary" />
                        <div class="flex flex-col gap-0.5">
                            <p class="type-label text-text">Jam layanan</p>
                            <p class="type-body-sm text-muted">
                                {{ $tersedia('jam_layanan') ? $kontak['jam_layanan']['value'] : 'Jam layanan akan ditampilkan setelah diverifikasi oleh pemerintah desa.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <x-ui.icon name="building" class="mt-0.5 size-5 shrink-0 text-primary" />
                        <div class="flex flex-col gap-0.5">
                            <p class="type-label text-text">Wilayah</p>
                            <p class="type-body-sm text-muted">{{ $wilayah }}</p>
                            <p class="type-label-sm text-muted">{{ $statistik['Luas wilayah']['source'] }}</p>
                        </div>
                    </div>

                    @if ($petaTerverifikasi)
                        <x-ui.button :href="$peta['url']" icon="map-pin" target="_blank" rel="noopener noreferrer" class="w-full">
                            Buka di Google Maps<span class="sr-only"> (membuka tab baru)</span>
                        </x-ui.button>
                    @endif
                </section>

                {{-- Peta: tampil setelah lokasi diverifikasi; alamat tertulis tetap terbaca bila peta gagal dimuat. --}}
                <div class="flex min-h-[220px] flex-col items-center justify-center gap-2 rounded-[14px] border border-border bg-primary-soft px-6 text-center text-primary lg:min-h-0">
                    <x-ui.icon name="map-pin" class="size-9" />
                    <p class="type-label">Peta lokasi desa</p>
                    @unless ($petaTerverifikasi)
                        <p class="type-body-sm max-w-xs text-muted">Peta akan ditampilkan setelah lokasi diverifikasi oleh pemerintah desa.</p>
                    @endunless
                </div>
            </div>
        </div>
    </div>
@endsection
