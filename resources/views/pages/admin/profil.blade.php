@extends('layouts.admin')

@section('title', 'Profil Desa')
@section('judul', 'Profil Desa')
@section('breadcrumb', 'Dashboard / Konten Desa / Profil Desa')

@php
    $tabs = [
        'sejarah' => ['Sejarah', route('admin.profil', ['tab' => 'sejarah'])],
        'visi' => ['Visi & Misi', route('admin.profil', ['tab' => 'visi'])],
        'lokasi' => ['Lokasi & Geografis', route('admin.profil', ['tab' => 'lokasi'])],
        'data' => ['Data Ringkas', route('admin.profil', ['tab' => 'data'])],
    ];
    $deskripsi = $tab === 'lokasi'
        ? 'Lokasi dan kontak resmi hanya ditampilkan setelah diverifikasi.'
        : 'Perubahan tampil di halaman Profil publik setelah disimpan.';
    $d = $data[$tab];
    $aksiForm = route('admin.profil.simpan', $tab);
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Profil Desa" :description="$deskripsi" />

        <x-admin.tabs :items="$tabs" :aktif="$tab" label="Bagian profil desa" />

        <x-admin.galat />

        <form method="post" action="{{ $aksiForm }}" class="flex flex-col gap-6 rounded-[16px] border border-border bg-surface p-5 lg:p-7">
            @csrf
            @method('put')

            @if ($tab === 'sejarah')
                <x-admin.editor name="sejarah" label="Naskah sejarah" :value="$d['sejarah']" help="Salin dari naskah resmi pemerintah desa. Baris kosong memisahkan paragraf." />
            @elseif ($tab === 'visi')
                <div class="flex flex-col gap-6" x-data="daftarButir({{ Js::from(old('misi', $d['misi'])) }})">
                    <x-ui.field name="visi" label="Visi" required multiline :rows="4" :value="$d['visi']" placeholder="Masukkan teks visi resmi desa…" help="Salin sesuai dokumen RPJMDes yang berlaku." />

                    <div class="flex flex-col gap-3">
                        <p id="misi-judul" class="type-label text-text">Misi</p>
                        <ol x-ref="daftar" aria-labelledby="misi-judul" class="flex flex-col gap-3">
                            <template x-for="(item, i) in butir" :key="item.id">
                                <li class="flex items-center gap-2">
                                    <span class="w-7 shrink-0 text-right type-label text-text" x-text="(i + 1) + '.'"></span>
                                    <label class="min-w-0 flex-1">
                                        <span class="sr-only" x-text="'Butir misi ' + (i + 1)"></span>
                                        <input
                                            type="text" name="misi[]" x-model="item.teks" maxlength="300" placeholder="Tulis butir misi"
                                            class="block min-h-11 w-full rounded-control border border-border bg-surface px-3.5 py-3 type-body text-text placeholder:text-muted focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)] focus-visible:outline-none"
                                        >
                                    </label>
                                    <button type="button" x-on:click="geser(i, -1)" x-bind:disabled="i === 0" x-bind:aria-label="'Naikkan butir misi ' + (i + 1)" class="relative inline-flex size-10 items-center justify-center rounded-control border border-border text-text hover:bg-primary-soft disabled:opacity-40 after:absolute after:-inset-0.5">
                                        <x-ui.icon name="chevron-down" class="size-4 rotate-180" />
                                    </button>
                                    <button type="button" x-on:click="geser(i, 1)" x-bind:disabled="i === butir.length - 1" x-bind:aria-label="'Turunkan butir misi ' + (i + 1)" class="relative inline-flex size-10 items-center justify-center rounded-control border border-border text-text hover:bg-primary-soft disabled:opacity-40 after:absolute after:-inset-0.5">
                                        <x-ui.icon name="chevron-down" class="size-4" />
                                    </button>
                                    <button type="button" x-on:click="hapus(i)" x-bind:aria-label="'Hapus butir misi ' + (i + 1)" class="relative inline-flex size-10 items-center justify-center rounded-control border border-border text-danger hover:border-danger hover:bg-danger-soft after:absolute after:-inset-0.5">
                                        <x-ui.icon name="trash" class="size-4" />
                                    </button>
                                </li>
                            </template>
                        </ol>
                        <div>
                            <x-ui.button variant="ghost" icon="plus" x-on:click="tambah()">Tambah butir misi</x-ui.button>
                        </div>
                    </div>
                </div>
            @elseif ($tab === 'lokasi')
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                    <div class="flex flex-col gap-5">
                        <x-ui.field name="alamat" label="Alamat kantor desa" required multiline :rows="4" :value="$d['alamat']" placeholder="Alamat lengkap" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.field name="lintang" label="Lintang" required inputmode="decimal" :value="$d['lintang']" placeholder="-5.xxxxx" />
                            <x-ui.field name="bujur" label="Bujur" required inputmode="decimal" :value="$d['bujur']" placeholder="105.xxxxx" />
                        </div>
                        <x-ui.field name="telepon" type="tel" label="Telepon kantor" :value="$d['telepon']" placeholder="(0721) …" />
                        <x-ui.field name="email" type="email" label="Email resmi" :value="$d['email']" placeholder="nama@desa.id" />
                    </div>
                    <div class="flex min-h-64 flex-col items-center justify-center gap-2 rounded-[12px] border border-border bg-primary-soft p-6 text-center">
                        <x-ui.icon name="map-pin" class="size-7 text-primary" />
                        <p class="type-label text-primary">Pratinjau titik peta</p>
                        @if ($petaUrl)
                            <a href="{{ $petaUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center type-label text-primary hover:underline">Buka titik di Google Maps<span class="sr-only"> (tab baru)</span></a>
                        @else
                            <p class="type-body-sm text-muted">Muncul setelah koordinat diisi</p>
                        @endif
                    </div>
                </div>
                <x-ui.checkbox name="terverifikasi" label="Data lokasi & kontak sudah diverifikasi pemerintah desa" :checked="$d['terverifikasi']" />
                <p class="type-body-sm text-muted">Alamat, telepon, email, dan peta baru tampil di situs publik bila kotak ini dicentang dan disimpan lewat “Simpan &amp; Terbitkan”. “Simpan Draf” tidak menayangkannya.</p>
            @else
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field name="penduduk" label="Jumlah penduduk (jiwa)" required inputmode="numeric" :value="$d['penduduk']" />
                    <x-ui.field name="kk" label="Kepala keluarga (KK)" required inputmode="numeric" :value="$d['kk']" />
                    <x-ui.field name="luas" label="Luas wilayah (ha)" required inputmode="numeric" :value="$d['luas']" />
                    <x-ui.field name="dusun" label="Jumlah dusun" required inputmode="numeric" :value="$d['dusun']" />
                </div>
                <x-ui.field name="sumber" label="Sumber data" required :value="$d['sumber']" help="Cantumkan sumber dan tahunnya. Angka tanpa sumber tidak boleh ditampilkan di situs publik." />
                <x-ui.checkbox name="terverifikasi" label="Angka sudah diverifikasi pemerintah desa" :checked="$d['terverifikasi']" />
            @endif

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="type-body-sm text-muted">
                    @if (in_array($tab, ['sejarah', 'visi'], true))
                        Terakhir diperbarui: {{ $diperbarui ?? '—' }}
                    @endif
                </p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    @if ($tab === 'lokasi')
                        <x-ui.button type="submit" name="aksi" value="draf" variant="secondary">Simpan Draf</x-ui.button>
                        <x-ui.button type="submit" name="aksi" value="terbit" icon="check">Simpan &amp; Terbitkan</x-ui.button>
                    @else
                        <x-ui.button :href="route('profil')" variant="secondary" icon="eye" target="_blank" rel="noopener">Pratinjau<span class="sr-only"> (buka di tab baru)</span></x-ui.button>
                        <x-ui.button type="submit" icon="check">Simpan Perubahan</x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
