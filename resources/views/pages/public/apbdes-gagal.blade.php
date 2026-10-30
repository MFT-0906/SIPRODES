@extends('layouts.public')

@section('title', 'Transparansi APBDes')

{{-- Keadaan gagal memuat. Pesan tidak menampilkan detail teknis (kode server, query, atau jejak galat). --}}
@section('content')
    <section class="bg-background">
        <div class="container-page flex flex-col gap-3 py-8 lg:py-10">
            <x-ui.breadcrumb :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => 'Transparansi APBDes']]" />
            <h1 class="type-h1 text-text">Transparansi APBDes</h1>
        </div>
    </section>

    <div class="bg-surface">
        <div class="container-page flex justify-center py-12 lg:py-16">
            <x-ui.empty-state
                tone="bahaya" icon="alert" class="w-full max-w-[560px]"
                title="Data APBDes gagal dimuat"
                description="Periksa koneksi internet Anda lalu coba lagi. Jika masalah berlanjut, hubungi kantor desa."
            >
                <x-slot:action>
                    {{-- Memuat ulang halaman yang sama tanpa parameter contoh. --}}
                    <x-ui.button :href="route('apbdes')" variant="secondary">Coba lagi</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    </div>
@endsection
