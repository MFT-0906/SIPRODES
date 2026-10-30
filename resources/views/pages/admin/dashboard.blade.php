@extends('layouts.admin')

@section('title', 'Ringkasan')
@section('judul', 'Ringkasan')

@php
    use App\Support\Format;

    $pintasan = [
        ['Tambah berita', 'newspaper', route('admin.berita.create')],
        ['Tambah agenda', 'calendar', route('admin.agenda.index').'#form-agenda'],
        ['Unggah dokumen APBDes', 'upload', route('admin.apbdes')],
        ['Tambah UMKM', 'store', route('admin.umkm')],
        ['Perbarui struktur perangkat', 'users', route('admin.struktur')],
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header :title="'Selamat datang, '.$pengguna['name']" description="Ringkasan konten situs. Tanda — berarti angkanya belum tersedia.">
            <x-ui.button :href="route('admin.berita.create')" icon="plus">Tambah Berita</x-ui.button>
        </x-admin.page-header>

        {{-- Angka ringkas: yang belum ada sumbernya ditampilkan sebagai —, bukan angka karangan. --}}
        {{-- TODO(backend): hitung UMKM dan data pertanian dari database. --}}
        <section aria-label="Statistik" class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-5">
            <x-ui.card-stat label="Berita terbit" :value="$terbit" source="data contoh" />
            <x-ui.card-stat label="Berita draf" :value="$draf" source="belum dipublikasikan" />
            <x-ui.card-stat label="UMKM terdaftar" value="—" source="data aktif" />
            <x-ui.card-stat label="Data pertanian" value="—" source="komoditas & kelompok" />
            <x-ui.card-stat label="Aspirasi belum diproses" :value="$aspirasiBaru" source="status “Baru”" tone="bahaya" />
        </section>

        <div class="grid items-stretch gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            {{-- Aspirasi terbaru: hanya cuplikan; NIK tidak pernah ditampilkan. --}}
            <section aria-labelledby="aspirasi-terbaru-judul" class="flex flex-col rounded-[14px] border border-border bg-surface">
                <div class="flex items-center justify-between gap-3 px-5 py-4">
                    <h2 id="aspirasi-terbaru-judul" class="type-h3 text-text">Aspirasi Terbaru</h2>
                    <x-ui.button :href="route('admin.aspirasi.index')" variant="ghost" size="s" icon="arrow-right">Lihat semua</x-ui.button>
                </div>
                <ul>
                    @foreach ($aspirasiTerbaru as $a)
                        <li class="flex items-center gap-3 border-t border-border px-5 py-3.5">
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <p class="type-label text-text">{{ $a['pengirim'] }}</p>
                                <p class="type-body-sm text-muted">{{ $a['cuplikan'] }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <x-admin.status-badge :status="$a['status']" />
                                <span class="type-label-sm text-muted">{{ $a['waktu'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section aria-labelledby="pintasan-judul" class="flex flex-col gap-2.5 rounded-[14px] border border-border bg-surface p-5">
                <h2 id="pintasan-judul" class="type-h3 text-text">Pintasan</h2>
                @foreach ($pintasan as [$teks, $ikon, $tujuan])
                    <a href="{{ $tujuan }}" class="flex min-h-11 items-center gap-3 rounded-[10px] bg-background p-3 type-label text-text hover:bg-primary-soft">
                        <x-ui.icon :name="$ikon" class="size-[18px] shrink-0 text-primary" />
                        <span class="min-w-0 flex-1">{{ $teks }}</span>
                        <x-ui.icon name="chevron-right" class="size-4 shrink-0" />
                    </a>
                @endforeach
            </section>
        </div>

        <section aria-labelledby="draf-terakhir-judul" class="rounded-[14px] border border-border bg-surface">
            <h2 id="draf-terakhir-judul" class="type-h3 px-5 py-4 text-text">Konten Draf Terakhir</h2>
            @forelse ($konten as $k)
                <div class="flex flex-col gap-3 border-t border-border px-5 py-3.5 sm:flex-row sm:items-center sm:gap-4">
                    <p class="min-w-0 flex-1 type-label text-text">{{ $k['judul'] }}</p>
                    <p class="type-body-sm text-muted">{{ $k['jenis'] }}</p>
                    <p class="type-body-sm text-muted">Diubah {{ Format::tanggal($k['diubah']) }}</p>
                    <x-admin.status-badge status="draf" class="self-start sm:self-auto" />
                    <x-ui.button :href="$k['href']" variant="secondary" size="s" icon="edit" class="self-start sm:self-auto">Edit<span class="sr-only"> {{ $k['jenis'] }}: {{ $k['judul'] }}</span></x-ui.button>
                </div>
            @empty
                <p class="border-t border-border px-5 py-6 type-body text-muted">Tidak ada konten draf. Semua konten sudah diterbitkan.</p>
            @endforelse
        </section>
    </div>
@endsection
