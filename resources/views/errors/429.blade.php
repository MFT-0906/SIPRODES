@extends('layouts.public')

@section('title', 'Terlalu banyak permintaan')

@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">429</p>
            <h1 class="type-h1 text-text">Terlalu banyak permintaan</h1>
            <p class="type-body max-w-[560px] text-muted">Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa menit, lalu coba lagi. Pembatasan ini melindungi situs dari spam.</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <x-ui.button :href="route('home')" icon="home">Kembali ke Beranda</x-ui.button>
                <x-ui.button :href="route('aspirasi.create')" variant="secondary" icon="message">Ruang Aspirasi</x-ui.button>
            </div>
        </div>
    </section>
@endsection
