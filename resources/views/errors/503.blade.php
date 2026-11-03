@extends('layouts.public')

@section('title', 'Situs sedang dipelihara')

@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">503</p>
            <h1 class="type-h1 text-text">Situs sedang dipelihara</h1>
            <p class="type-body max-w-[560px] text-muted">Kami sedang memperbarui situs agar lebih baik. Silakan kembali beberapa saat lagi.</p>
            <div class="mt-2">
                <x-ui.button type="button" x-data x-on:click="location.reload()" icon="arrow-right">Muat ulang</x-ui.button>
            </div>
        </div>
    </section>
@endsection
