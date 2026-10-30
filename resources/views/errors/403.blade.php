@extends('layouts.public')

@section('title', 'Akses tidak diizinkan')

@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col items-center gap-4 py-16 text-center lg:py-[120px]">
            <p class="type-display text-primary" aria-hidden="true">403</p>
            <h1 class="type-h1 text-text">Akses tidak diizinkan</h1>
            <p class="type-body max-w-[560px] text-muted">Anda tidak memiliki izin untuk membuka halaman ini. Hubungi Super Admin desa bila Anda merasa ini keliru.</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <x-ui.button :href="route('home')" icon="home">Kembali ke Beranda</x-ui.button>
                <x-ui.button :href="route('admin.dashboard')" variant="secondary" icon="dashboard">Ke Dashboard</x-ui.button>
            </div>
        </div>
    </section>
@endsection
