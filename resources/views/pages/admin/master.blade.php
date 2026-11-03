@extends('layouts.admin')

@section('title', 'Data Master')
@section('judul', 'Data Master')
@section('breadcrumb', 'Dashboard / Administrasi / Data Master')

@php
    $bagian = [
        'dusun' => ['Dusun', $dusun, 'kelompok_tani_count', 'kelompok tani', 'Dusun dipakai untuk memilih lokasi kelompok tani.'],
        'jabatan' => ['Jabatan', $jabatan, 'perangkat_count', 'perangkat', 'Jabatan dipakai pada Struktur Perangkat Desa.'],
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Data Master" description="Daftar dusun dan jabatan resmi desa. Hanya Super Admin yang dapat mengubahnya." />

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-2">
            @foreach ($bagian as $jenis => [$judul, $daftar, $hitung, $pemakai, $keterangan])
                <section aria-labelledby="master-{{ $jenis }}" class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-5 lg:p-6">
                    <div class="flex flex-col gap-1">
                        <h2 id="master-{{ $jenis }}" class="type-h3 text-text">{{ $judul }}</h2>
                        <p class="type-body-sm text-muted">{{ $keterangan }} Nilai awal menunggu konfirmasi desa.</p>
                    </div>

                    <ul class="flex flex-col gap-3">
                        @forelse ($daftar as $i => $baris)
                            <li class="flex flex-col gap-2 rounded-[10px] bg-background p-3 sm:flex-row sm:items-start">
                                <form method="post" action="{{ route('admin.master.update', [$jenis, $baris]) }}" class="grid flex-1 gap-2 sm:grid-cols-[minmax(0,1fr)_88px_auto] sm:items-start">
                                    @csrf
                                    @method('put')
                                    <label class="min-w-0">
                                        <span class="sr-only">Nama {{ mb_strtolower($judul) }} {{ $baris->nama }}</span>
                                        <input type="text" name="nama" value="{{ $baris->nama }}" maxlength="100" required class="block min-h-11 w-full rounded-control border border-border bg-surface px-3.5 py-2.5 type-body text-text focus:border-primary focus-visible:outline-none">
                                    </label>
                                    <label>
                                        <span class="sr-only">Urutan {{ $baris->nama }}</span>
                                        <input type="number" name="urutan" value="{{ $baris->urutan }}" min="0" max="999" class="block min-h-11 w-full rounded-control border border-border bg-surface px-3.5 py-2.5 type-body text-text focus:border-primary focus-visible:outline-none">
                                    </label>
                                    <x-ui.button type="submit" variant="secondary" size="s">Simpan<span class="sr-only"> {{ $baris->nama }}</span></x-ui.button>
                                </form>
                                <div class="flex items-center gap-2 sm:pt-1">
                                    <span class="type-label-sm text-muted">{{ $baris->{$hitung} }} {{ $pemakai }}</span>
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus '.mb_strtolower($judul).': '.$baris->nama" x-on:click="$dispatch('open-modal', 'hapus-{{ $jenis }}-{{ $i }}')" />
                                </div>
                            </li>
                        @empty
                            <li class="type-body text-muted">Belum ada data.</li>
                        @endforelse
                    </ul>

                    <form method="post" action="{{ route('admin.master.store', $jenis) }}" class="flex flex-col gap-3 border-t border-border pt-4 sm:flex-row sm:items-end">
                        @csrf
                        <div class="flex-1">
                            <x-ui.field :name="'nama'" :id="'baru-'.$jenis" :label="'Tambah '.mb_strtolower($judul).' baru'" :placeholder="'Nama '.mb_strtolower($judul)" />
                        </div>
                        <x-ui.button type="submit" icon="plus">Tambah</x-ui.button>
                    </form>
                </section>
            @endforeach
        </div>

        @foreach ($bagian as $jenis => [$judul, $daftar, $hitung, $pemakai])
            @foreach ($daftar as $i => $baris)
                <x-admin.confirm-delete
                    :name="'hapus-'.$jenis.'-'.$i" :title="'Hapus '.mb_strtolower($judul).' ini?'"
                    :description="'“'.$baris->nama.'” akan dihapus. '.($baris->{$hitung} ? 'Dipakai '.$baris->{$hitung}.' '.$pemakai.'; '.($jenis === 'jabatan' ? 'penghapusan akan ditolak selama masih dipakai.' : 'isian dusunnya di data tersebut dikosongkan.') : 'Tindakan ini tidak dapat dibatalkan.')"
                    :action="route('admin.master.destroy', [$jenis, $baris])" :confirm="'Ya, hapus '.mb_strtolower($judul)"
                />
            @endforeach
        @endforeach
    </div>
@endsection
