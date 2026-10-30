@extends('layouts.admin')

@section('title', 'Kelompok Tani')
@section('judul', 'Kelompok Tani')
@section('breadcrumb', 'Dashboard / Potensi / Kelompok Tani')

@php
    $ubah = ! is_null($item);
    $cols = 'minmax(0,1.2fr) 100px 100px 170px 100px';
    $setuju = session()->hasOldInput() ? (bool) old('disetujui') : ($item['disetujui'] ?? false);
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelompok Tani" description="Data kelompok tani dan kontak yang disetujui untuk dipublikasikan.">
            <x-ui.button :href="route('admin.kelompok-tani').'#form-kelompok'" icon="plus">Tambah Kelompok</x-ui.button>
        </x-admin.page-header>

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_368px]">
            <section aria-label="Daftar kelompok tani" class="rounded-[14px] border border-border bg-surface">
                <form method="get" action="{{ route('admin.kelompok-tani') }}" role="search" class="p-4 lg:p-5">
                    <x-ui.search name="q" label="Cari kelompok tani" placeholder="Cari kelompok…" class="lg:w-80" />
                </form>

                @if ($daftar->count())
                    <x-admin.table :cols="$cols" caption="Daftar kelompok tani beserta komoditas, dusun, dan status kontak publik" :heads="[['NAMA KELOMPOK', 'left'], ['KOMODITAS', 'left'], ['DUSUN', 'left'], ['KONTAK PUBLIK', 'left'], ['AKSI', 'right']]">
                        @foreach ($daftar as $i => $g)
                            <x-admin.tr :cols="$cols" :aktif="$ubah && $item['id'] === $g['id']">
                                <th scope="row" class="min-w-0 text-left type-label text-text">{{ $g['nama'] }}</th>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Komoditas: </span>{{ $g['komoditas'] }}</td>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Dusun: </span>{{ $g['dusun'] ?? '—' }}</td>
                                <td>
                                    <x-ui.badge :tone="$g['disetujui'] ? 'sukses' : 'netral'">{{ $g['disetujui'] ? 'Disetujui' : 'Belum disetujui' }}</x-ui.badge>
                                </td>
                                <td class="flex items-center gap-1.5 lg:justify-end">
                                    <x-admin.icon-button icon="edit" :href="route('admin.kelompok-tani', ['ubah' => $g['id']]).'#form-kelompok'" :label="'Ubah kelompok tani: '.$g['nama']" />
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus kelompok tani: '.$g['nama']" x-on:click="$dispatch('open-modal', 'hapus-kelompok-{{ $i }}')" />
                                </td>
                            </x-admin.tr>
                        @endforeach
                    </x-admin.table>
                @else
                    <div class="border-t border-border p-4 lg:p-5">
                        <x-ui.empty-state icon="users" title="Tidak ada kelompok tani" description="Coba kata kunci lain atau tambahkan kelompok melalui formulir di samping.">
                            <x-slot:action><x-ui.button :href="route('admin.kelompok-tani')" variant="secondary">Hapus filter</x-ui.button></x-slot:action>
                        </x-ui.empty-state>
                    </div>
                @endif
            </section>

            <section id="form-kelompok" aria-labelledby="form-kelompok-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5">
                <h2 id="form-kelompok-judul" class="type-h3 mb-5 text-text">{{ $ubah ? 'Ubah Kelompok Tani' : 'Tambah Kelompok Tani' }}</h2>
                <form method="post" class="flex flex-col gap-5" action="{{ $ubah ? route('admin.kelompok-tani.update', $item['id']) : route('admin.kelompok-tani.store') }}">
                    @csrf
                    @if ($ubah)
                        @method('put')
                    @endif
                    <x-ui.field name="nama" label="Nama kelompok" required :value="$item['nama'] ?? null" placeholder="Nama kelompok tani" />
                    <x-ui.select name="komoditas" label="Komoditas" required placeholder="Pilih komoditas" :options="array_combine($komoditas, $komoditas)" :selected="$item['komoditas'] ?? null" />
                    <x-ui.select name="dusun" label="Dusun" required placeholder="Pilih dusun" :options="array_combine($dusun, $dusun)" :selected="$item['dusun'] ?? null" />
                    <x-ui.field name="ketua" label="Nama ketua" :value="$item['ketua'] ?? null" placeholder="Nama ketua kelompok" />
                    <x-ui.field name="kontak" type="tel" label="Nomor kontak" :value="$item['kontak'] ?? null" placeholder="08xx-xxxx-xxxx" help="Hanya tampil di publik jika disetujui." />
                    <x-ui.checkbox name="disetujui" label="Kontak disetujui untuk publikasi" :checked="$setuju" />
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button :href="route('admin.kelompok-tani')" variant="ghost">Batal</x-ui.button>
                        <x-ui.button type="submit">Simpan</x-ui.button>
                    </div>
                </form>
            </section>
        </div>

        @foreach ($daftar as $i => $g)
            <x-admin.confirm-delete
                :name="'hapus-kelompok-'.$i" title="Hapus kelompok tani ini?"
                :description="'Kelompok “'.$g['nama'].'” akan dihapus. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.kelompok-tani.destroy', $g['id'])" confirm="Ya, hapus kelompok"
            />
        @endforeach
    </div>
@endsection
