@extends('layouts.public')

@section('title', 'Transparansi APBDes')

{{-- Belum ada periode APBDes yang diterbitkan. --}}
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
                icon="wallet" class="w-full max-w-[560px]"
                title="Data APBDes belum dipublikasikan"
                description="Ringkasan dan dokumen APBDes akan tampil di sini setelah dipublikasikan oleh pemerintah desa."
            >
                <x-slot:action>
                    <x-ui.button :href="route('profil')" variant="secondary">Lihat Profil Desa</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    </div>
@endsection
