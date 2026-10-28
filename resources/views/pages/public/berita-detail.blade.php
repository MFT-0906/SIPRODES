@extends('layouts.public')

@php
    use App\Support\Format;
@endphp

@section('title', $artikel['title'])
@section('description', $artikel['excerpt'])

@section('content')
    <article aria-labelledby="judul-artikel" class="bg-background">
        <div class="container-page pb-12 pt-8 lg:pb-16 lg:pt-10">
            <div class="mx-auto flex max-w-[800px] flex-col gap-5">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Beranda', 'href' => route('home')],
                    ['label' => 'Berita', 'href' => route('berita.index')],
                    ['label' => $artikel['category']],
                ]" />

                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                    <x-ui.badge tone="utama">{{ $artikel['category'] }}</x-ui.badge>
                    <p class="flex items-center gap-1.5 type-body-sm text-muted">
                        <x-ui.icon name="calendar" class="size-4 shrink-0" />
                        <span>{{ Format::tanggalLengkap($artikel['tanggal']) }} · {{ $artikel['penulis'] }} · {{ $menitBaca }} menit baca</span>
                    </p>
                </div>

                <h1 id="judul-artikel" class="type-h1 text-text">{{ $artikel['title'] }}</h1>

                {{-- TODO: ganti dengan gambar utama asli; isi alt deskriptif saat memakai <img>. --}}
                <x-ui.image-placeholder caption="Foto berita" class="h-[220px] rounded-card md:h-[420px]" />

                {{-- Isi artikel: lebar baca dibatasi agar nyaman dibaca. --}}
                <div class="flex max-w-[70ch] flex-col gap-5">
                    @foreach ($artikel['isi'] as [$jenis, $teks])
                        @if ($jenis === 'h2')
                            <h2 class="type-h2 mt-2 text-text">{{ $teks }}</h2>
                        @elseif ($jenis === 'kutipan')
                            <blockquote class="flex gap-4 py-2">
                                <span class="w-1 shrink-0 self-stretch rounded-[2px] bg-accent" aria-hidden="true"></span>
                                <p class="type-h3 text-primary">{{ $teks }}</p>
                            </blockquote>
                        @else
                            <p class="type-body-lg text-text">{{ $teks }}</p>
                        @endif
                    @endforeach
                </div>

                <div class="mt-2 flex flex-col gap-3 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <x-ui.button :href="route('berita.index')" variant="ghost">‹ Kembali ke daftar berita</x-ui.button>
                    <div x-data="bagikan({{ \Illuminate\Support\Js::from($artikel['title']) }})">
                        <x-ui.button variant="secondary" size="s" x-on:click="kirim()" class="max-sm:w-full">Bagikan tautan</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    @if ($lainnya)
        <section aria-labelledby="lainnya-judul" class="bg-surface">
            <div class="container-page flex flex-col gap-6 pb-14 pt-10 lg:pb-[72px] lg:pt-14">
                <h2 id="lainnya-judul" class="type-h2 text-text">Berita lainnya</h2>
                <div class="grid gap-4 md:grid-cols-3 md:gap-6">
                    @foreach ($lainnya as $item)
                        <x-ui.card-news
                            :title="$item['title']" :excerpt="$item['excerpt']" :date="Format::tanggal($item['tanggal'])"
                            :category="$item['category']" :href="route('berita.show', $item['slug'])"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
