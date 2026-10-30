@extends('layouts.public')

@section('title', 'Terjadi gangguan')

{{-- Tanpa detail teknis (kode server, query, atau jejak galat). --}}
@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">500</p>
            <h1 class="type-h1 text-text">Terjadi gangguan pada situs</h1>
            <p class="type-body max-w-[560px] text-muted">Mohon maaf, halaman belum dapat ditampilkan. Silakan coba lagi beberapa saat lagi.</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <x-ui.button :href="route('home')" icon="home">Kembali ke Beranda</x-ui.button>
            </div>
        </div>
    </section>
@endsection
