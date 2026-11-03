@extends('layouts.public')

@section('title', 'Sesi berakhir')

@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">419</p>
            <h1 class="type-h1 text-text">Sesi halaman sudah berakhir</h1>
            <p class="type-body max-w-[560px] text-muted">Formulir dibiarkan terbuka terlalu lama atau sesi Anda sudah habis. Muat ulang halaman sebelumnya lalu kirim kembali. Isian Anda mungkin perlu ditulis ulang.</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <x-ui.button type="button" x-data x-on:click="history.back()" icon="arrow-right">Kembali ke halaman sebelumnya</x-ui.button>
                <x-ui.button :href="route('home')" variant="secondary" icon="home">Ke Beranda</x-ui.button>
            </div>
        </div>
    </section>
@endsection
