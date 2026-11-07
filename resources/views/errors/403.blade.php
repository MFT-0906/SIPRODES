@php
    // Petugas yang sudah masuk dan tersasar ke modul terlarang tetap melihat kerangka admin (menu samping)
    // agar bisa langsung pindah ke modul miliknya. Pengunjung publik tetap memakai tata letak situs.
    $dalamAdmin = auth()->check() && request()->is('admin', 'admin/*');
@endphp
@extends($dalamAdmin ? 'layouts.admin' : 'layouts.public')

@section('title', 'Akses tidak diizinkan')
@if ($dalamAdmin)
    @section('judul', 'Akses tidak diizinkan')
@endif

@section('content')
    @if ($dalamAdmin)
        <div class="flex flex-col gap-6">
            <x-admin.page-header title="Akses tidak diizinkan" description="Akun Anda belum memiliki izin untuk modul ini." />
            <x-ui.empty-state
                icon="shield"
                title="Anda tidak memiliki izin untuk membuka halaman ini"
                description="Menu di samping hanya memuat modul yang ditugaskan kepada Anda. Hubungi Super Admin desa bila Anda merasa ini keliru."
            >
                <x-slot:action>
                    <x-ui.button :href="route('admin.dashboard')" icon="dashboard">Ke Ringkasan</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @else
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
    @endif
@endsection
