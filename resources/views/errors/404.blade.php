@extends('layouts.public')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">404</p>
            <h1 class="type-h1 text-text">Halaman tidak ditemukan</h1>
            <p class="type-body max-w-[560px] text-muted">Alamat yang Anda buka mungkin salah ketik, sudah dipindahkan, atau kontennya belum dipublikasikan.</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <x-ui.button :href="route('home')" icon="home">Kembali ke Beranda</x-ui.button>
                <x-ui.button :href="route('berita.index')" variant="secondary" icon="newspaper">Lihat Berita</x-ui.button>
            </div>
        </div>
    </section>
@endsection
