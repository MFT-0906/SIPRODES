@extends('layouts.public')

@section('title', 'Potensi Desa')
@section('description', $content['pengantar'])

@php
    $kelompok = $content['kelompok_tani'];
    $tabs = [['pertanian', 'Pertanian', 'sprout'], ['umkm', 'UMKM', 'store']];
@endphp

@section('content')
    {{-- Judul halaman --}}
    <section class="bg-background">
        <div class="container-page flex flex-col gap-3 py-8 lg:py-10">
            <x-ui.breadcrumb :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => 'Potensi Desa']]" />
            <h1 class="type-h1 text-text">Potensi Desa</h1>
            <p class="type-body max-w-[760px] text-muted">{{ $content['pengantar'] }}</p>
        </div>
    </section>

    <div class="bg-surface" x-data="katalog(@js($tabAwal))">
        <div class="container-page flex flex-col gap-8 pb-14 pt-8 lg:gap-10 lg:pb-20 lg:pt-12">
            {{-- Tab dan pencarian --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div role="tablist" aria-label="Kategori potensi" class="grid grid-cols-2 gap-1 rounded-[10px] border border-border bg-surface p-1 md:inline-grid">
                    @foreach ($tabs as [$id, $label, $ikon])
                        <button
                            type="button" role="tab" id="tab-{{ $id }}" aria-controls="panel-{{ $id }}"
                            x-bind:aria-selected="tab === '{{ $id }}'" x-bind:tabindex="tab === '{{ $id }}' ? 0 : -1"
                            x-on:click="pilih('{{ $id }}')" x-on:keydown="tombol($event)"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-5 py-2.5 type-label text-text hover:bg-primary-soft aria-selected:bg-primary aria-selected:text-surface aria-selected:hover:bg-primary"
                        >
                            <x-ui.icon :name="$ikon" class="size-[18px]" />
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Pencarian di server (semua halaman), mempertahankan tab yang sedang dibuka. --}}
                <form method="get" action="{{ route('potensi') }}" role="search" class="md:w-[360px]">
                    <input type="hidden" name="tab" x-bind:value="tab" value="{{ $tabAwal }}">
                    <x-ui.search name="q" label="Cari komoditas atau usaha" placeholder="Cari komoditas atau usaha…" :value="$kata" />
                </form>
            </div>

            @if ($kata !== '')
                <p class="type-body text-muted" role="status">
                    Hasil pencarian untuk “{{ $kata }}”.
                    <a href="{{ route('potensi', ['tab' => $tabAwal]) }}" class="text-primary hover:underline">Hapus pencarian</a>
                </p>
            @endif

            {{-- Panel Pertanian --}}
            <div id="panel-pertanian" role="tabpanel" aria-labelledby="tab-pertanian" tabindex="0" x-show="tab === 'pertanian'" x-cloak class="flex flex-col gap-10">
                <section aria-labelledby="komoditas-judul" class="flex flex-col gap-5">
                    <h2 id="komoditas-judul" class="type-h2 text-text">Pertanian</h2>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                        @forelse ($content['komoditas'] as $komoditas)
                            <x-public.card-komoditas
                                :nama="$komoditas['nama']" :deskripsi="$komoditas['deskripsi']" :unggulan="$komoditas['unggulan']"
                                :musim-panen="$komoditas['musim_panen']" :foto-keterangan="$komoditas['foto_keterangan']" :foto="$komoditas['foto']" :peluang="$komoditas['peluang']"
                            />
                        @empty
                            <p class="type-body text-muted md:col-span-2 lg:col-span-3">
                                {{ $kata !== '' ? 'Tidak ada komoditas yang cocok dengan pencarian.' : 'Belum ada komoditas yang dipublikasikan.' }}
                            </p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="kelompok-judul" class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 id="kelompok-judul" class="type-h2 text-text">Kelompok tani</h2>
                        @unless ($kelompok['resmi'])
                            <x-ui.badge tone="peringatan">Menunggu data resmi desa</x-ui.badge>
                        @endunless
                    </div>

                    {{-- Tabel; di HP setiap baris berubah menjadi kartu bertumpuk. --}}
                    <div class="overflow-hidden rounded-[14px] border border-border bg-surface">
                        <table role="table" class="w-full border-collapse max-md:block">
                            <thead class="max-md:sr-only">
                                <tr role="row" class="bg-background type-label-sm text-muted">
                                    <th role="columnheader" scope="col" class="px-6 py-3.5 text-left font-medium">NAMA KELOMPOK</th>
                                    <th role="columnheader" scope="col" class="w-[260px] px-3 py-3.5 text-left font-medium">KOMODITAS</th>
                                    <th role="columnheader" scope="col" class="w-[220px] px-3 py-3.5 text-left font-medium">DUSUN</th>
                                    <th role="columnheader" scope="col" class="w-[240px] py-3.5 pl-3 pr-6 text-left font-medium">KONTAK</th>
                                </tr>
                            </thead>
                            <tbody class="max-md:block">
                                @forelse ($kelompok['daftar'] as $item)
                                    <tr
                                        role="row"
                                        class="border-t border-border type-body-sm text-text max-md:flex max-md:flex-col max-md:gap-1 max-md:px-4 max-md:py-3.5"
                                    >
                                        <td role="cell" class="px-6 py-3.5 type-label max-md:p-0">{{ $item['nama'] }}</td>
                                        <td role="cell" data-label="Komoditas" class="px-3 py-3.5 max-md:p-0 max-md:before:text-muted max-md:before:content-[attr(data-label)_':_']">{{ $item['komoditas'] }}</td>
                                        <td role="cell" data-label="Dusun" class="px-3 py-3.5 max-md:p-0 max-md:before:text-muted max-md:before:content-[attr(data-label)_':_']">{{ $item['dusun'] ?: '—' }}</td>
                                        <td role="cell" data-label="Kontak" class="py-3.5 pl-3 pr-6 text-muted max-md:p-0 max-md:before:content-[attr(data-label)_':_']">
                                            {{-- Kontak hanya tampil bila sudah disetujui untuk dipublikasikan. --}}
                                            {{ $item['kontak_disetujui'] && $item['kontak'] ? $item['kontak'] : 'Belum disetujui untuk publikasi' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr role="row" class="border-t border-border type-body-sm text-muted max-md:block max-md:px-4 max-md:py-3.5">
                                        <td role="cell" colspan="4" class="px-6 py-3.5 max-md:p-0">
                                            {{ $kata !== '' ? 'Tidak ada kelompok tani yang cocok dengan pencarian.' : 'Belum ada kelompok tani yang dipublikasikan.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Panel UMKM --}}
            <div id="panel-umkm" role="tabpanel" aria-labelledby="tab-umkm" tabindex="0" x-show="tab === 'umkm'" x-cloak class="flex flex-col gap-5">
                <section aria-labelledby="umkm-judul" class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 id="umkm-judul" class="type-h2 text-text">Usaha warga (UMKM)</h2>
                        @unless ($content['umkm']['resmi'])
                            <x-ui.badge tone="peringatan">Menunggu data resmi desa</x-ui.badge>
                        @endunless
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                        @forelse ($umkm as $usaha)
                            <x-ui.card-umkm
                                :name="$usaha['name']" :products="$usaha['products']" :description="$usaha['description']"
                                :whatsapp="$usaha['whatsapp']" :whatsapp-approved="$usaha['whatsappApproved']" :image="$usaha['image']" :image-alt="$usaha['imageAlt']"
                            />
                        @empty
                            <p class="type-body text-muted sm:col-span-2 lg:col-span-4">
                                {{ $kata !== '' ? 'Tidak ada usaha yang cocok dengan pencarian.' : 'Belum ada usaha warga yang dipublikasikan.' }}
                            </p>
                        @endforelse
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <p class="type-body-sm text-muted">
                            Menampilkan {{ $umkm->firstItem() ?? 0 }}–{{ $umkm->lastItem() ?? 0 }} dari {{ $umkm->total() }} UMKM
                        </p>
                        <x-ui.pagination :paginator="$umkm" />
                    </div>
                </section>
            </div>

            {{-- Ajakan mendaftarkan usaha --}}
            <section aria-labelledby="ajakan-judul" class="flex flex-col gap-5 rounded-card bg-background p-6 md:flex-row md:items-center md:gap-6 md:px-8 md:py-7">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-accent-soft text-primary">
                    <x-ui.icon name="sprout" class="size-[26px]" />
                </span>
                <div class="flex flex-1 flex-col gap-1">
                    <h2 id="ajakan-judul" class="type-h3 text-text">{{ $content['ajakan']['judul'] }}</h2>
                    <p class="type-body text-muted">{{ $content['ajakan']['deskripsi'] }}</p>
                </div>
                <x-ui.button :href="route('aspirasi.create')" variant="accent" icon="message" class="shrink-0">Sampaikan usulan</x-ui.button>
            </section>
        </div>
    </div>
@endsection
