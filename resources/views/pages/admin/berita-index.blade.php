@extends('layouts.admin')

@section('title', 'Kelola Berita')
@section('judul', 'Berita')
@section('breadcrumb', 'Dashboard / Publikasi / Berita')

@php
    use App\Support\Format;

    $opsiStatus = ['semua' => 'Semua', 'terbit' => 'Terbit', 'draf' => 'Draf'];
    $opsiKategori = ['semua' => 'Semua'] + $kategori->mapWithKeys(fn ($k) => [$k => $k])->all();
    $grid = 'lg:grid lg:grid-cols-[minmax(0,1fr)_140px_140px_110px_136px] lg:items-center lg:gap-4';
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelola Berita" description="Hanya berita berstatus “Terbit” yang tampil di situs publik.">
            <x-ui.button :href="route('admin.berita.create')" icon="plus">Tambah Berita</x-ui.button>
        </x-admin.page-header>

        @if (! $ada)
            <x-ui.empty-state title="Belum ada berita" description="Mulai dengan menambahkan berita pertama. Berita disimpan sebagai draf sampai Anda menerbitkannya.">
                <x-slot:action>
                    <x-ui.button :href="route('admin.berita.create')" variant="secondary">Tambah Berita</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <section aria-label="Daftar berita" class="rounded-[14px] border border-border bg-surface">
                {{-- Alat tabel: pencarian dan penyaring --}}
                <div class="flex flex-col gap-3 p-4 lg:flex-row lg:items-center lg:p-5">
                    <form method="get" action="{{ route('admin.berita.index') }}" role="search" class="lg:w-80">
                        @foreach (['status', 'kategori'] as $kunci)
                            @if (request($kunci) && request($kunci) !== 'semua')
                                <input type="hidden" name="{{ $kunci }}" value="{{ request($kunci) }}">
                            @endif
                        @endforeach
                        <x-ui.search name="q" label="Cari judul berita" placeholder="Cari judul berita…" />
                    </form>
                    <x-ui.filter-dropdown name="status" label="Status" :options="$opsiStatus" />
                    <x-ui.filter-dropdown name="kategori" label="Kategori" :options="$opsiKategori" />
                </div>

                @if ($berita->count())
                    <table class="block w-full">
                        <caption class="sr-only">Daftar berita beserta kategori, tanggal, status, dan aksi</caption>
                        <thead class="block">
                            <tr class="hidden border-t border-border bg-background px-5 py-3 type-label-sm text-muted lg:grid lg:grid-cols-[minmax(0,1fr)_140px_140px_110px_136px] lg:gap-4">
                                <th scope="col" class="text-left font-[inherit]">JUDUL</th>
                                <th scope="col" class="text-left font-[inherit]">KATEGORI</th>
                                <th scope="col" class="text-left font-[inherit]">TANGGAL</th>
                                <th scope="col" class="text-left font-[inherit]">STATUS</th>
                                <th scope="col" class="text-right font-[inherit]">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="block">
                            @foreach ($berita as $i => $item)
                                <tr class="flex flex-col gap-3 border-t border-border px-4 py-4 lg:px-5 {{ $grid }}">
                                    <th scope="row" class="flex min-w-0 items-center gap-3 text-left font-[inherit]">
                                        <span class="flex h-10 w-14 shrink-0 items-center justify-center rounded-[6px] bg-primary-soft text-primary" aria-hidden="true">
                                            <x-ui.icon name="image" class="size-4" />
                                        </span>
                                        <span class="min-w-0 type-label text-text">{{ $item['title'] }}</span>
                                    </th>
                                    <td class="type-body-sm text-muted">
                                        <span class="type-label-sm lg:sr-only">Kategori: </span>{{ $item['category'] }}
                                    </td>
                                    <td class="type-body-sm text-muted">
                                        <span class="type-label-sm lg:sr-only">Tanggal: </span>{{ Format::tanggal($item['tanggal']) }}
                                    </td>
                                    <td><x-admin.status-badge :status="$item['status']" /></td>
                                    <td class="flex items-center gap-1.5 lg:justify-end">
                                        @if ($item['status'] === 'terbit')
                                            <x-admin.icon-button
                                                icon="eye" :href="route('berita.show', $item['slug'])" :new-tab="true"
                                                :label="'Lihat berita di situs publik: '.$item['title']"
                                            />
                                        @endif
                                        <x-admin.icon-button
                                            icon="edit" :href="route('admin.berita.edit', $item['slug'])"
                                            :label="'Ubah berita: '.$item['title']"
                                        />
                                        <x-admin.icon-button
                                            icon="trash" tone="bahaya" :label="'Hapus berita: '.$item['title']"
                                            x-on:click="$dispatch('open-modal', 'hapus-berita-{{ $i }}')"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="flex flex-col gap-3 border-t border-border p-4 sm:flex-row sm:items-center sm:justify-between lg:px-5">
                        <p class="type-body-sm text-muted">Menampilkan {{ $berita->firstItem() }}–{{ $berita->lastItem() }} dari {{ $berita->total() }} berita</p>
                        <x-ui.pagination :paginator="$berita" />
                    </div>
                @else
                    <div class="border-t border-border p-4 lg:p-5">
                        <x-ui.empty-state icon="search" title="Tidak ada berita yang cocok" description="Coba kata kunci lain atau ubah penyaring status dan kategori.">
                            <x-slot:action>
                                <x-ui.button :href="route('admin.berita.index')" variant="secondary">Hapus filter</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </div>
                @endif
            </section>

            {{-- Dialog konfirmasi hapus: menyebut nama berita, fokus terkunci, Esc menutup. --}}
            @foreach ($berita as $i => $item)
                <x-ui.modal
                    :name="'hapus-berita-'.$i" title="Hapus berita ini?"
                    :description="'Berita “'.$item['title'].'” akan dihapus permanen beserta gambarnya. Tindakan ini tidak dapat dibatalkan.'"
                >
                    <x-slot:footer>
                        <form method="post" action="{{ route('admin.berita.destroy', $item['slug']) }}" class="contents">
                            @csrf
                            @method('delete')
                            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'hapus-berita-{{ $i }}')">Batal</x-ui.button>
                            <x-ui.button type="submit" variant="danger">Ya, hapus berita</x-ui.button>
                        </form>
                    </x-slot:footer>
                </x-ui.modal>
            @endforeach
        @endif
    </div>
@endsection
