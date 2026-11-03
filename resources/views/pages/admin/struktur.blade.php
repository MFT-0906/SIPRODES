@extends('layouts.admin')

@section('title', 'Struktur Perangkat')
@section('judul', 'Struktur Perangkat')
@section('breadcrumb', 'Dashboard / Konten Desa / Struktur Perangkat')

@php
    $ubah = ! is_null($item);
    $cols = '56px minmax(0,1.2fr) minmax(0,1fr) 140px 200px';
    $tampilAwal = session()->hasOldInput() ? (bool) old('tampil') : ($item['tampil'] ?? true);
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Struktur Perangkat Desa" description="Gunakan tombol panah untuk mengubah urutan tampilan di situs publik.">
            <x-ui.button :href="route('admin.struktur').'#form-perangkat'" icon="plus">Tambah Perangkat</x-ui.button>
        </x-admin.page-header>

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_368px]">
            <section aria-label="Daftar perangkat desa" class="rounded-[14px] border border-border bg-surface">
                @if (count($daftar))
                    <x-admin.table :cols="$cols" caption="Daftar perangkat desa menurut urutan tampilan" :heads="[['URUT', 'left'], ['NAMA', 'left'], ['JABATAN', 'left'], ['STATUS', 'left'], ['AKSI', 'right']]">
                        @foreach ($daftar as $i => $p)
                            <x-admin.tr :cols="$cols" :aktif="$ubah && $item['id'] === $p['id']" class="first:border-t-0">
                                <td class="type-label text-text"><span class="type-label-sm lg:sr-only">Urutan: </span>{{ $i + 1 }}</td>
                                <th scope="row" class="flex min-w-0 items-center gap-3 text-left font-[inherit]">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary" aria-hidden="true"><x-ui.icon name="user" class="size-[18px]" /></span>
                                    <span class="min-w-0 type-label text-text">{{ $p['nama'] }}</span>
                                </th>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Jabatan: </span>{{ $p['jabatan'] }}</td>
                                <td>
                                    @if ($p['tampil'])
                                        <x-ui.badge tone="sukses">Tampil</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="netral">Disembunyikan</x-ui.badge>
                                    @endif
                                </td>
                                <td class="flex flex-wrap items-center gap-1.5 lg:justify-end">
                                    @foreach (['naik' => ['Naikkan', 'rotate-180', $i === 0], 'turun' => ['Turunkan', '', $i === count($daftar) - 1]] as $arah => [$kata, $putar, $habis])
                                        <form method="post" action="{{ route('admin.struktur.geser', $p['id']) }}">
                                            @csrf
                                            <input type="hidden" name="arah" value="{{ $arah }}">
                                            <x-admin.icon-button icon="chevron-down" :icon-class="$putar" :submit="true" :label="$kata.' urutan: '.$p['nama'].' ('.$p['jabatan'].')'" :disabled="$habis" class="disabled:opacity-40" />
                                        </form>
                                    @endforeach
                                    <x-admin.icon-button icon="edit" :href="route('admin.struktur', ['ubah' => $p['id']]).'#form-perangkat'" :label="'Ubah perangkat: '.$p['nama'].' ('.$p['jabatan'].')'" />
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus perangkat: '.$p['nama'].' ('.$p['jabatan'].')'" x-on:click="$dispatch('open-modal', 'hapus-perangkat-{{ $i }}')" />
                                </td>
                            </x-admin.tr>
                        @endforeach
                    </x-admin.table>
                @else
                    <div class="p-4 lg:p-5">
                        <x-ui.empty-state icon="users" title="Belum ada perangkat desa" description="Tambahkan perangkat pertama melalui formulir di samping." />
                    </div>
                @endif
            </section>

            <section id="form-perangkat" aria-labelledby="form-perangkat-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5">
                <h2 id="form-perangkat-judul" class="type-h3 mb-5 text-text">{{ $ubah ? 'Edit Perangkat' : 'Tambah Perangkat' }}</h2>

                <form method="post" enctype="multipart/form-data" class="flex flex-col gap-5"
                    action="{{ $ubah ? route('admin.struktur.update', $item['id']) : route('admin.struktur.store') }}">
                    @csrf
                    @if ($ubah)
                        @method('put')
                    @endif

                    @if ($item['foto_url'] ?? null)
                        <img src="{{ $item['foto_url'] }}" alt="Foto {{ $item['nama'] }}" loading="lazy" class="size-24 rounded-full border border-border object-cover">
                        <x-ui.checkbox name="hapus_foto" label="Hapus foto saat disimpan" />
                    @endif
                    <x-ui.uploader name="foto" :max-mb="\App\Support\UnggahBerkas::batasMb('gambar')" label="Foto" accept="image/jpeg,image/png,image/webp" accept-label="JPG, PNG, atau WebP" :hint="'Rasio 1:1 · maks. '.round(config('desa.unggahan.gambar_maks_kb') / 1024, 1).' MB'" />
                    @error('foto')
                        <p class="flex items-start gap-1.5 type-body-sm text-danger"><x-ui.icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                    @enderror
                    <x-ui.field name="nama" label="Nama lengkap" required :value="$item['nama'] ?? null" placeholder="Nama sesuai SK" />
                    <x-ui.field name="jabatan" label="Jabatan" required :value="$item['jabatan'] ?? null" placeholder="Contoh: Kepala Desa, Sekretaris Desa" />
                    <div class="flex items-center justify-between gap-3">
                        <span class="type-label text-text" id="tampil-label">Tampilkan di situs publik</span>
                        <x-ui.toggle name="tampil" label="Tampilkan di situs publik" :checked="$tampilAwal" />
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button :href="route('admin.struktur')" variant="ghost">Batal</x-ui.button>
                        <x-ui.button type="submit">Simpan</x-ui.button>
                    </div>
                </form>
            </section>
        </div>

        @foreach ($daftar as $i => $p)
            <x-admin.confirm-delete
                :name="'hapus-perangkat-'.$i" title="Hapus perangkat ini?"
                :description="'“'.$p['nama'].'” ('.$p['jabatan'].') akan dihapus dari struktur perangkat desa. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.struktur.destroy', $p['id'])" confirm="Ya, hapus perangkat"
            />
        @endforeach
    </div>
@endsection
