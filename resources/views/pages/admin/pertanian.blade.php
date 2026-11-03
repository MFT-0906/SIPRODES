@extends('layouts.admin')

@section('title', 'Potensi Pertanian')
@section('judul', 'Potensi Pertanian')
@section('breadcrumb', 'Dashboard / Potensi / Pertanian')

@php
    $cols = 'minmax(0,1fr) 180px 140px 110px 136px';
    $tabs = ['komoditas' => ['Komoditas', route('admin.pertanian')], 'kelompok' => ['Kelompok Tani', route('admin.kelompok-tani')]];
    $opsiStatus = ['semua' => 'Semua', 'terbit' => 'Terbit', 'draf' => 'Draf'];
    $ubah = ! is_null($item);
    $formTampil = $tambah || $ubah || $errors->any();
    $kelompokPerKomoditas = collect($kelompok)->countBy('komoditas');
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Potensi Pertanian" description="Kelola komoditas, musim panen, dan keterkaitan dengan kelompok tani.">
            <x-ui.button :href="route('admin.pertanian', ['tambah' => 1]).'#form-komoditas'" icon="plus">Tambah Komoditas</x-ui.button>
        </x-admin.page-header>

        <x-admin.tabs :items="$tabs" aktif="komoditas" label="Bagian potensi pertanian" />

        <section aria-label="Daftar komoditas" class="rounded-[14px] border border-border bg-surface">
            <div class="flex flex-col gap-3 p-4 lg:flex-row lg:items-center lg:p-5">
                <form method="get" action="{{ route('admin.pertanian') }}" role="search" class="lg:w-80">
                    @if (request('status') && request('status') !== 'semua')
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <x-ui.search name="q" label="Cari komoditas" placeholder="Cari komoditas…" />
                </form>
                <x-ui.filter-dropdown name="status" label="Status" :options="$opsiStatus" />
            </div>

            @if ($komoditas->count())
                <x-admin.table :cols="$cols" caption="Daftar komoditas beserta musim panen, kelompok tani, dan status" :heads="[['KOMODITAS', 'left'], ['MUSIM PANEN', 'left'], ['KELOMPOK TANI', 'left'], ['STATUS', 'left'], ['AKSI', 'right']]">
                    @foreach ($komoditas as $i => $k)
                        <x-admin.tr :cols="$cols">
                            <th scope="row" class="flex min-w-0 items-center gap-3 text-left font-[inherit]">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-[8px] bg-primary-soft text-primary" aria-hidden="true"><x-ui.icon name="sprout" class="size-[18px]" /></span>
                                <span class="min-w-0 type-label text-text">{{ $k['nama'] }}</span>
                            </th>
                            <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Musim panen: </span>{{ $k['musim_panen'] ?? 'Menunggu konfirmasi' }}</td>
                            <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Kelompok tani: </span>{{ ($n = $kelompokPerKomoditas[$k['nama']] ?? 0) ? $n.' kelompok' : '—' }}</td>
                            <td><x-admin.status-badge :status="$k['status']" /></td>
                            <td class="flex items-center gap-1.5 lg:justify-end">
                                <x-admin.icon-button icon="edit" :href="route('admin.pertanian', ['ubah' => $k['id']]).'#form-komoditas'" :label="'Ubah komoditas: '.$k['nama']" />
                                <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus komoditas: '.$k['nama']" x-on:click="$dispatch('open-modal', 'hapus-komoditas-{{ $i }}')" />
                            </td>
                        </x-admin.tr>
                    @endforeach
                </x-admin.table>
            @else
                <div class="border-t border-border p-4 lg:p-5">
                    @if ($disaring || $ada)
                        <x-ui.empty-state icon="search" title="Tidak ada komoditas yang cocok" description="Coba kata kunci lain atau ubah penyaring status.">
                            <x-slot:action><x-ui.button :href="route('admin.pertanian')" variant="secondary">Hapus filter</x-ui.button></x-slot:action>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state icon="sprout" title="Belum ada komoditas" description="Tambahkan komoditas pertama melalui tombol Tambah Komoditas." />
                    @endif
                </div>
            @endif
        </section>

        @if ($formTampil)
            <section id="form-komoditas" aria-labelledby="form-komoditas-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5 lg:p-6">
                <h2 id="form-komoditas-judul" class="type-h3 mb-5 text-text">{{ $ubah ? 'Ubah Komoditas' : 'Tambah Komoditas' }}</h2>
                <x-admin.galat class="mb-5" />
                <form method="post" enctype="multipart/form-data" class="flex flex-col gap-5" action="{{ $ubah ? route('admin.pertanian.update', $item['id']) : route('admin.pertanian.store') }}">
                    @csrf
                    @if ($ubah)
                        @method('put')
                    @endif
                    <div class="grid gap-4 md:grid-cols-3">
                        <x-ui.field name="nama" label="Nama komoditas" required :value="$item['nama'] ?? null" />
                        <x-ui.field name="musim_panen" label="Musim panen" :value="$item['musim_panen'] ?? null" help="Kosongkan jika belum dikonfirmasi." />
                        <x-ui.select name="status" label="Status" required :placeholder="null" :options="['draf' => 'Draf', 'terbit' => 'Terbit']" :selected="$item['status'] ?? 'draf'" />
                    </div>
                    <x-ui.field name="deskripsi" label="Deskripsi" multiline :rows="4" :value="$item['deskripsi'] ?? null" />
                    <x-ui.checkbox name="unggulan" label="Tandai sebagai komoditas unggulan (tampil di Beranda)" :checked="$item['unggulan'] ?? false" />
                    <div class="flex flex-col gap-3 rounded-[10px] border border-border p-4">
                        <p class="type-label text-text">Foto komoditas</p>
                        @if ($item['foto_url'] ?? null)
                            <img src="{{ $item['foto_url'] }}" alt="{{ $item['alt'] }}" loading="lazy" class="max-h-40 rounded-[10px] border border-border object-cover">
                            <x-ui.checkbox name="hapus_foto" label="Hapus foto saat disimpan" />
                        @endif
                        <x-ui.uploader name="foto" :max-mb="\App\Support\UnggahBerkas::batasMb('gambar')" accept="image/jpeg,image/png,image/webp" accept-label="JPG, PNG, atau WebP" :hint="'Foto asli yang disetujui desa · maks. '.round(config('desa.unggahan.gambar_maks_kb') / 1024, 1).' MB'" />
                        <x-ui.field name="alt" label="Teks alternatif foto" :value="$item['alt'] ?? null" help="Wajib diisi bila foto dipilih." />
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button :href="route('admin.pertanian')" variant="ghost">Batal</x-ui.button>
                        <x-ui.button type="submit">Simpan</x-ui.button>
                    </div>
                </form>
            </section>
        @endif

        <section aria-labelledby="kelompok-judul" class="rounded-[14px] border border-border bg-surface p-5 lg:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="kelompok-judul" class="type-h3 text-text">Kelompok Tani</h2>
                <x-ui.button :href="route('admin.kelompok-tani').'#form-kelompok'" variant="secondary" size="s" icon="plus">Tambah Kelompok</x-ui.button>
            </div>
            <ul class="flex flex-col gap-3">
                @forelse ($kelompok as $i => $g)
                    <li class="flex flex-col gap-3 rounded-[10px] bg-background p-3.5 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <x-ui.icon name="users" class="size-5 shrink-0 text-primary" />
                            <div class="min-w-0">
                                <p class="type-label text-text">{{ $g['nama'] }}</p>
                                <p class="type-body-sm text-muted">Ketua: {{ $g['ketua'] ?? '—' }} · {{ $g['dusun'] ?? 'Dusun —' }}</p>
                            </div>
                        </div>
                        <p class="flex items-center gap-1.5 type-label-sm text-muted">
                            <x-ui.icon name="{{ $g['disetujui'] ? 'check' : 'lock' }}" class="size-3.5" />
                            {{ $g['disetujui'] ? 'Kontak disetujui untuk publikasi' : 'Kontak tidak dipublikasikan' }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            <x-admin.icon-button icon="edit" :href="route('admin.kelompok-tani', ['ubah' => $g['id']]).'#form-kelompok'" :label="'Ubah kelompok tani: '.$g['nama']" />
                            <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus kelompok tani: '.$g['nama']" x-on:click="$dispatch('open-modal', 'hapus-kelompok-{{ $i }}')" />
                        </div>
                    </li>
                @empty
                    <li class="type-body text-muted">Belum ada kelompok tani.</li>
                @endforelse
            </ul>
            <p class="mt-4 flex items-start gap-2 type-body-sm text-muted">
                <x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0 text-warning" />
                Kontak kelompok tani hanya tampil di situs publik jika opsi “Kontak disetujui untuk publikasi” dicentang dan ada bukti persetujuan.
            </p>
        </section>

        @foreach ($komoditas as $i => $k)
            <x-admin.confirm-delete
                :name="'hapus-komoditas-'.$i" title="Hapus komoditas ini?"
                :description="'Komoditas “'.$k['nama'].'” akan dihapus dari daftar potensi pertanian. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.pertanian.destroy', $k['id'])" confirm="Ya, hapus komoditas"
            />
        @endforeach
        @foreach ($kelompok as $i => $g)
            <x-admin.confirm-delete
                :name="'hapus-kelompok-'.$i" title="Hapus kelompok tani ini?"
                :description="'Kelompok “'.$g['nama'].'” akan dihapus. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.kelompok-tani.destroy', $g['id'])" confirm="Ya, hapus kelompok"
            />
        @endforeach
    </div>
@endsection
