@extends('layouts.admin')

@php
    $ubah = ! is_null($item);
    $judulHalaman = $ubah ? 'Ubah Berita' : 'Tambah Berita';
    $statusAwal = old('status', $item['status'] ?? 'draf');
    $aksiForm = $ubah ? route('admin.berita.update', $item['slug']) : route('admin.berita.store');
    $pilihanStatus = [
        'draf' => ['Draf', 'Disimpan, tidak tampil di situs publik.'],
        'terbit' => ['Terbit', 'Tampil di situs publik.'],
    ];
@endphp

@section('title', $judulHalaman)
@section('judul', $judulHalaman)
@section('breadcrumb', 'Dashboard / Berita / '.$judulHalaman)

@section('content')
    <form
        method="post" action="{{ $aksiForm }}" enctype="multipart/form-data"
        x-data="{ status: {{ Js::from($statusAwal) }} }" class="flex flex-col gap-6"
    >
        @csrf
        @if ($ubah)
            @method('put')
        @endif

        <h1 class="sr-only">{{ $judulHalaman }}</h1>

        {{-- Ringkasan galat di atas formulir --}}
        @if ($errors->any())
            <x-ui.alert tone="bahaya" title="Periksa kembali isian Anda">
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            {{-- Isi berita --}}
            <div class="flex flex-col gap-5 rounded-[14px] border border-border bg-surface p-5 lg:p-6">
                <x-ui.field name="title" label="Judul berita" required :value="$item['title'] ?? null" :counter="150" help="Maksimal 150 karakter." placeholder="Tulis judul yang jelas dan ringkas" />
                <x-ui.field name="excerpt" label="Ringkasan" required :value="$item['excerpt'] ?? null" :counter="300" help="Ditampilkan di daftar berita dan beranda." placeholder="Satu–dua kalimat untuk kartu berita" />
                <x-admin.editor name="isi_teks" label="Isi berita" :value="$item['isi_teks'] ?? null" />
            </div>

            <div class="flex flex-col gap-6">
                {{-- Status publikasi --}}
                <div role="radiogroup" aria-labelledby="status-publikasi-judul" class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-5">
                    <h2 id="status-publikasi-judul" class="type-h3 text-text">Status publikasi</h2>

                    @foreach ($pilihanStatus as $nilai => [$nama, $keterangan])
                        <label class="flex cursor-pointer items-start gap-3 rounded-[10px] border p-3 has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary"
                            :class="status === '{{ $nilai }}' ? 'border-2 border-primary bg-primary-soft' : 'border-border'">
                            <input
                                type="radio" name="status" value="{{ $nilai }}" x-model="status"
                                class="mt-0.5 size-[18px] shrink-0 appearance-none rounded-full border-[1.5px] border-muted bg-surface checked:border-primary checked:bg-primary checked:shadow-[inset_0_0_0_3px_var(--color-surface)] focus-visible:outline-none"
                            >
                            <span class="flex flex-col gap-0.5">
                                <span class="type-label text-text">{{ $nama }}</span>
                                <span class="type-body-sm text-muted">{{ $keterangan }}</span>
                            </span>
                        </label>
                    @endforeach

                    <x-ui.field name="tanggal" type="date" label="Tanggal" required :value="$item['tanggal'] ?? now()->toDateString()" />
                    <x-ui.select
                        name="kategori" label="Kategori" required placeholder="Pilih kategori"
                        :options="array_combine($kategori, $kategori)" :selected="$item['category'] ?? null"
                    />
                </div>

                {{-- Gambar utama --}}
                <section aria-labelledby="gambar-utama-judul" class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-5">
                    <h2 id="gambar-utama-judul" class="type-h3 text-text">Gambar utama</h2>
                    @if ($item['gambar_url'] ?? null)
                        <img src="{{ $item['gambar_url'] }}" alt="{{ $item['alt'] }}" loading="lazy" class="max-h-48 w-full rounded-[10px] border border-border object-cover">
                        <x-ui.checkbox name="hapus_gambar" label="Hapus gambar utama saat disimpan" />
                    @endif
                    <x-ui.uploader
                        name="gambar" accept="image/jpeg,image/png,image/webp" accept-label="JPG, PNG, atau WebP"
                        :hint="'JPG/PNG/WebP · maks. '.round(config('desa.unggahan.gambar_maks_kb') / 1024, 1).' MB · '.config('desa.unggahan.gambar_maks_piksel').' px'"
                    />
                    @error('gambar')
                        <p class="flex items-start gap-1.5 type-body-sm text-danger"><x-ui.icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                    @enderror
                    <x-ui.field
                        name="alt" label="Teks alternatif (alt)" required :value="$item['alt'] ?? null"
                        placeholder="Deskripsi isi gambar" help="Wajib diisi bila gambar utama dipilih. Membantu pengguna pembaca layar."
                    />
                </section>
            </div>
        </div>

        {{-- Aksi formulir: tombol yang ditekan menentukan status akhir --}}
        <div class="flex flex-col-reverse gap-3 rounded-[12px] border border-border bg-surface p-4 sm:flex-row sm:items-center sm:justify-end lg:px-5">
            <x-ui.button :href="route('admin.berita.index')" variant="ghost">Batal</x-ui.button>
            <x-ui.button type="submit" name="aksi" value="draf" variant="secondary" x-on:click="status = 'draf'">Simpan sebagai Draf</x-ui.button>
            <x-ui.button type="submit" name="aksi" value="terbit" icon="check" x-on:click="status = 'terbit'">Simpan &amp; Terbitkan</x-ui.button>
        </div>
    </form>
@endsection
