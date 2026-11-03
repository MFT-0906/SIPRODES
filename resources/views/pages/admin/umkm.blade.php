@extends('layouts.admin')

@section('title', 'Kelola UMKM')
@section('judul', 'UMKM')
@section('breadcrumb', 'Dashboard / Potensi / UMKM')

@php
    $ubah = ! is_null($item);
    $cols = 'minmax(0,1.3fr) minmax(0,1fr) 130px 110px 100px';
    $setuju = session()->hasOldInput() ? (bool) old('disetujui') : ($item['disetujui'] ?? false);
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelola UMKM" description="Data usaha warga yang tampil di katalog Potensi Desa.">
            <x-ui.button :href="route('admin.umkm', ['tambah' => 1]).'#form-umkm'" icon="plus">Tambah UMKM</x-ui.button>
        </x-admin.page-header>

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_368px]">
            <section aria-label="Daftar UMKM" class="rounded-[14px] border border-border bg-surface">
                <form method="get" action="{{ route('admin.umkm') }}" role="search" class="p-4 lg:p-5">
                    <x-ui.search name="q" label="Cari usaha" placeholder="Cari usaha…" class="lg:w-80" />
                </form>

                @if ($daftar->count())
                    <x-admin.table :cols="$cols" caption="Daftar UMKM beserta produk, persetujuan WhatsApp, dan status" :heads="[['USAHA', 'left'], ['PRODUK', 'left'], ['WHATSAPP', 'left'], ['STATUS', 'left'], ['AKSI', 'right']]">
                        @foreach ($daftar as $i => $u)
                            <x-admin.tr :cols="$cols" :aktif="$ubah && $item['id'] === $u['id']">
                                <th scope="row" class="flex min-w-0 items-center gap-3 text-left font-[inherit]">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-[8px] bg-primary-soft text-primary" aria-hidden="true"><x-ui.icon name="store" class="size-[18px]" /></span>
                                    <span class="min-w-0 type-label text-text">{{ $u['nama'] }}</span>
                                </th>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Produk: </span>{{ $u['produk'] }}</td>
                                <td class="flex items-center gap-1.5 type-body-sm {{ $u['disetujui'] ? 'text-success' : 'text-muted' }}">
                                    <x-ui.icon name="{{ $u['disetujui'] ? 'message' : 'lock' }}" class="size-3.5" />
                                    <span><span class="type-label-sm lg:sr-only">WhatsApp: </span>{{ $u['disetujui'] ? 'Disetujui' : 'Belum' }}</span>
                                </td>
                                <td><x-admin.status-badge :status="$u['status']" /></td>
                                <td class="flex items-center gap-1.5 lg:justify-end">
                                    <x-admin.icon-button icon="edit" :href="route('admin.umkm', ['ubah' => $u['id']]).'#form-umkm'" :label="'Ubah UMKM: '.$u['nama']" />
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus UMKM: '.$u['nama']" x-on:click="$dispatch('open-modal', 'hapus-umkm-{{ $i }}')" />
                                </td>
                            </x-admin.tr>
                        @endforeach
                    </x-admin.table>
                @else
                    <div class="border-t border-border p-4 lg:p-5">
                        <x-ui.empty-state icon="store" title="Tidak ada UMKM yang cocok" description="Coba kata kunci lain atau tambahkan usaha baru.">
                            <x-slot:action><x-ui.button :href="route('admin.umkm')" variant="secondary">Hapus filter</x-ui.button></x-slot:action>
                        </x-ui.empty-state>
                    </div>
                @endif
            </section>

            <section id="form-umkm" aria-labelledby="form-umkm-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5">
                <h2 id="form-umkm-judul" class="type-h3 mb-5 text-text">{{ $ubah ? 'Edit UMKM' : 'Tambah UMKM' }}</h2>
                <form method="post" enctype="multipart/form-data" class="flex flex-col gap-5" action="{{ $ubah ? route('admin.umkm.update', $item['id']) : route('admin.umkm.store') }}">
                    @csrf
                    @if ($ubah)
                        @method('put')
                    @endif
                    @if ($item['foto_url'] ?? null)
                        <img src="{{ $item['foto_url'] }}" alt="{{ $item['alt'] }}" loading="lazy" class="max-h-40 rounded-[10px] border border-border object-cover">
                        <x-ui.checkbox name="hapus_foto" label="Hapus foto saat disimpan" />
                    @endif
                    <x-ui.uploader name="foto" :max-mb="\App\Support\UnggahBerkas::batasMb('gambar')" label="Foto produk" accept="image/jpeg,image/png,image/webp" accept-label="JPG, PNG, atau WebP" :hint="'Dengan izin pemilik usaha · maks. '.round(config('desa.unggahan.gambar_maks_kb') / 1024, 1).' MB'" />
                    @error('foto')
                        <p class="flex items-start gap-1.5 type-body-sm text-danger"><x-ui.icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                    @enderror
                    <x-ui.field name="alt" label="Teks alternatif foto" :value="$item['alt'] ?? null" help="Wajib diisi bila foto dipilih." />
                    <x-ui.field name="nama" label="Nama usaha" required :value="$item['nama'] ?? null" placeholder="Nama usaha" />
                    <x-ui.field name="produk" label="Produk" required :value="$item['produk'] ?? null" placeholder="Jenis produk" />
                    <x-ui.field name="deskripsi" label="Deskripsi" multiline :rows="4" :value="$item['deskripsi'] ?? null" placeholder="Deskripsi singkat…" />
                    <x-ui.field name="whatsapp" type="tel" label="Nomor WhatsApp" :value="$item['whatsapp'] ?? null" placeholder="08xx-xxxx-xxxx" />
                    <x-ui.select name="status" label="Status" required :placeholder="null" :options="['terbit' => 'Terbit', 'draf' => 'Draf']" :selected="$item['status'] ?? 'terbit'" />
                    <x-ui.checkbox name="disetujui" label="Pemilik usaha menyetujui nomor WhatsApp dan foto ditampilkan di situs publik." :checked="$setuju" class="items-start" />
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button :href="route('admin.umkm')" variant="ghost">Batal</x-ui.button>
                        <x-ui.button type="submit">Simpan</x-ui.button>
                    </div>
                </form>
            </section>
        </div>

        @foreach ($daftar as $i => $u)
            <x-admin.confirm-delete
                :name="'hapus-umkm-'.$i" title="Hapus UMKM ini?"
                :description="'Usaha “'.$u['nama'].'” akan dihapus dari katalog Potensi Desa. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.umkm.destroy', $u['id'])" confirm="Ya, hapus UMKM"
            />
        @endforeach
    </div>
@endsection
