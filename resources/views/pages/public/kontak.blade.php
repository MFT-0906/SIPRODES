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

    // Hanya saluran yang sudah diverifikasi yang mendapat kartu sendiri. Bila telepon dan email sama-sama belum
    // tersedia, keduanya digabung menjadi satu kartu keterangan agar halaman tidak berisi kartu kosong berulang.
    $adaTelepon = $tersedia('telepon');
    $adaEmail = $tersedia('email');
    $jumlahKartu = ($adaTelepon ? 1 : 0) + ($adaEmail ? 1 : 0) + ($adaTelepon || $adaEmail ? 0 : 1) + 1;
    $kolomKartu = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][$jumlahKartu];
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
            <ul class="grid gap-4 {{ $kolomKartu }} lg:gap-6">
                @if ($adaTelepon)
                    <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                        <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="phone" class="size-6" /></span>
                        <h2 class="type-h3 text-text">{{ $kontak['telepon']['value'] }}</h2>
                        @if ($tersedia('jam_layanan'))
                            <p class="type-body-sm text-muted">{{ $kontak['jam_layanan']['value'] }}</p>
                        @endif
                        <x-ui.button :href="'tel:'.preg_replace('/[^\d+]/', '', $kontak['telepon']['value'])" variant="secondary" icon="phone" class="mt-auto w-full">Telepon</x-ui.button>
                    </li>
                @endif

                @if ($adaEmail)
                    <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                        <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="mail" class="size-6" /></span>
                        <h2 class="type-h3 break-all text-text">{{ $kontak['email']['value'] }}</h2>
                        <x-ui.button :href="'mailto:'.$kontak['email']['value']" variant="secondary" icon="mail" class="mt-auto w-full">Kirim email</x-ui.button>
                    </li>
                @endif

                @unless ($adaTelepon || $adaEmail)
                    <li class="flex flex-col items-start gap-3 rounded-[14px] border border-border bg-surface p-6">
                        <span class="flex size-12 items-center justify-center rounded-[12px] bg-primary-soft text-primary"><x-ui.icon name="phone" class="size-6" /></span>
                        <h2 class="type-h3 text-text">Telepon dan email resmi</h2>
                        <p class="type-body-sm text-muted">Nomor telepon dan alamat email resmi akan ditampilkan setelah diverifikasi oleh pemerintah desa. Sementara itu, gunakan formulir daring.</p>
                    </li>
                @endunless

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
                        <x-ui.button :href="$peta['url']" icon="map-pin" target="_blank" rel="noopener noreferrer" class="mt-auto w-full">
                            Buka di Google Maps<span class="sr-only"> (membuka tab baru)</span>
                        </x-ui.button>
                    @endif
                </section>

                {{-- Peta: tampil setelah lokasi diverifikasi; alamat tertulis tetap terbaca bila peta gagal dimuat. --}}
                <x-public.peta :peta="$peta" tinggi="h-[300px] lg:h-[440px]" />
            </div>
        </div>
    </div>
@endsection
