<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Komponen — Desa Marga Agung</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    $d = require resource_path('views/dev/katalog-data.php');
    $ikon = collect(glob(resource_path('svg/icons/*.svg')))->map(fn ($path) => basename($path, '.svg'))->sort()->values();
@endphp

<x-ui.toast />

<main class="container-page space-y-12 py-8">
    <header>
        <h1 class="type-h1">Katalog komponen</h1>
        <p class="type-body mt-2 text-muted">Halaman internal untuk memeriksa semua komponen dan variannya. Hanya tampil di lingkungan lokal. Coba operasikan dengan keyboard (Tab, Enter, Spasi, Esc).</p>
    </header>

    {{-- ===== Tombol ===== --}}
    <section aria-labelledby="s-tombol" class="space-y-4">
        <h2 id="s-tombol" class="type-h2">Tombol</h2>
        @foreach (['primary', 'accent', 'secondary', 'ghost', 'danger'] as $variant)
            <div>
                <p class="type-label-sm mb-2 text-muted">{{ $variant }}</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button :variant="$variant">Label tombol</x-ui.button>
                    <x-ui.button :variant="$variant" icon="arrow-right">Dengan ikon</x-ui.button>
                    <x-ui.button :variant="$variant" disabled>Disabled</x-ui.button>
                    <x-ui.button :variant="$variant" loading>Memuat</x-ui.button>
                    <x-ui.button :variant="$variant" size="s">Label tombol</x-ui.button>
                    <x-ui.button :variant="$variant" size="s" icon="plus">Tambah</x-ui.button>
                    <x-ui.button :variant="$variant" size="s" disabled>Disabled</x-ui.button>
                    <x-ui.button :variant="$variant" size="s" loading>Memuat</x-ui.button>
                </div>
            </div>
        @endforeach
        <div>
            <p class="type-label-sm mb-2 text-muted">Sebagai tautan &amp; kunci saat kirim</p>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button href="#" variant="secondary">Tautan</x-ui.button>
                <x-ui.button href="#" disabled>Tautan nonaktif</x-ui.button>
                <form method="get">
                    <x-ui.button type="submit">Kirim (tombol terkunci saat dikirim)</x-ui.button>
                </form>
            </div>
        </div>
    </section>

    {{-- ===== Ikon ===== --}}
    <section aria-labelledby="s-ikon" class="space-y-4">
        <h2 id="s-ikon" class="type-h2">Ikon ({{ $ikon->count() }})</h2>
        <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
            @foreach ($ikon as $nama)
                <li class="flex flex-col items-center gap-1.5 rounded-control border border-border bg-surface p-3">
                    <x-ui.icon :name="$nama" class="text-text" />
                    <span class="type-label-sm break-all text-center text-muted">{{ $nama }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- ===== Badge ===== --}}
    <section aria-labelledby="s-badge" class="space-y-4">
        <h2 id="s-badge" class="type-h2">Badge</h2>
        <div class="flex flex-wrap gap-3">
            <x-ui.badge tone="sukses">Sukses</x-ui.badge>
            <x-ui.badge tone="netral">Netral</x-ui.badge>
            <x-ui.badge tone="info">Info</x-ui.badge>
            <x-ui.badge tone="peringatan">Peringatan</x-ui.badge>
            <x-ui.badge tone="utama">Utama</x-ui.badge>
            <x-ui.badge tone="aksen">Aksen</x-ui.badge>
            <x-ui.badge tone="bahaya">Bahaya</x-ui.badge>
        </div>
    </section>

    {{-- ===== Formulir ===== --}}
    <section aria-labelledby="s-form" class="space-y-4">
        <h2 id="s-form" class="type-h2">Formulir</h2>
        <div class="grid gap-6 md:grid-cols-2">
            <x-ui.field name="demo_nama" label="Nama lengkap" required placeholder="Contoh: Budi Santoso" help="Teks bantuan singkat." />
            <x-ui.field name="demo_pesan" label="Isi pesan" multiline required placeholder="Contoh: Budi Santoso" help="Teks bantuan singkat." />
            <x-ui.field name="demo_nik" label="NIK" help="Tidak wajib diisi." inputmode="numeric" maxlength="16" />
            <x-ui.field name="demo_error" label="Nama lengkap" required value="Contoh: Budi Santoso" error="Teks bantuan singkat." />
            <x-ui.field name="demo_error_area" label="Isi pesan" multiline required error="Isi pesan minimal 20 karakter." />
            <x-ui.select name="demo_kategori" label="Kategori" required :options="$d['kategori']" placeholder="Pilih kategori" />
            <x-ui.select name="demo_kategori_error" label="Kategori" required :options="$d['kategori']" placeholder="Pilih kategori" error="Pilih salah satu kategori." />
        </div>

        <div class="grid gap-x-6 md:grid-cols-3">
            <fieldset>
                <legend class="type-label mb-1">Kotak centang</legend>
                <x-ui.checkbox name="demo_cek[]" value="a" label="Label pilihan" />
                <x-ui.checkbox name="demo_cek[]" value="b" label="Label pilihan" checked />
                <x-ui.checkbox name="demo_cek[]" value="c" label="Dinonaktifkan" disabled />
            </fieldset>
            <fieldset>
                <legend class="type-label mb-1">Tombol radio</legend>
                <x-ui.radio name="demo_radio" value="a" label="Label pilihan" />
                <x-ui.radio name="demo_radio" value="b" label="Label pilihan" checked />
                <x-ui.radio name="demo_radio" value="c" label="Dinonaktifkan" disabled />
            </fieldset>
            <div>
                <p class="type-label mb-1">Sakelar</p>
                <x-ui.toggle name="demo_toggle_1" label="Tampil di publik" />
                <x-ui.toggle name="demo_toggle_2" label="Tampil di publik" checked />
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <x-ui.uploader name="demo_berkas" label="Unggah berkas (coba pilih PDF, lalu berkas lain)" accept-label="PDF" accept="application/pdf" :max-mb="2" />
            <x-ui.uploader name="demo_berkas_2" label="Mengunggah" state="mengunggah" />
            <x-ui.uploader name="demo_berkas_3" label="Berhasil" state="berhasil" />
            <x-ui.uploader name="demo_berkas_4" label="Ditolak" state="ditolak" file-name="dokumen.exe" file-size="3,1 MB" message="Ditolak: hanya PDF yang diizinkan. Pilih berkas lain." />
        </div>
    </section>

    {{-- ===== Navigasi & daftar ===== --}}
    <section aria-labelledby="s-nav" class="space-y-4">
        <h2 id="s-nav" class="type-h2">Navigasi &amp; daftar</h2>
        <x-ui.breadcrumb :items="$d['breadcrumb']" />
        <x-ui.pagination :current="request('page', 1)" :last="8" />
        <div class="flex flex-wrap items-start gap-3">
            <x-ui.search class="w-full sm:w-80" />
            <x-ui.filter-dropdown name="status" label="Status" :options="$d['status']" />
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.chip active>Kategori</x-ui.chip>
            <x-ui.chip>Kategori</x-ui.chip>
            <x-ui.chip href="#">Tautan</x-ui.chip>
        </div>
    </section>

    {{-- ===== Kartu ===== --}}
    <section aria-labelledby="s-kartu" class="space-y-4">
        <h2 id="s-kartu" class="type-h2">Kartu</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.card-news :title="$d['berita']['title']" :excerpt="$d['berita']['excerpt']" :date="$d['berita']['date']" :category="$d['berita']['category']" :href="$d['berita']['href']" />
            <x-ui.card-umkm :name="$d['umkm']['name']" :products="$d['umkm']['products']" :description="$d['umkm']['description']" :whatsapp="$d['umkm']['whatsapp']" whatsapp-approved />
            <x-ui.card-umkm :name="$d['umkm']['name']" :products="$d['umkm']['products']" :description="$d['umkm']['description']" :whatsapp="$d['umkm']['whatsapp']" />
            <x-ui.card-agenda :day="$d['agenda']['day']" :month="$d['agenda']['month']" :title="$d['agenda']['title']" :time="$d['agenda']['time']" :location="$d['agenda']['location']" />
            <x-ui.card-agenda :day="$d['agenda']['day']" :month="$d['agenda']['month']" :title="$d['agenda']['title']" :time="$d['agenda']['time']" />
            <x-ui.card-quick :title="$d['akses']['title']" :description="$d['akses']['description']" :icon="$d['akses']['icon']" :href="$d['akses']['href']" />
            <x-ui.card-stat :label="$d['statistik']['label']" :value="$d['statistik']['value']" :source="$d['statistik']['source']" />
            <x-ui.card-official :name="$d['perangkat']['name']" :position="$d['perangkat']['position']" />
            <x-ui.image-placeholder class="h-[200px] rounded-card" />
        </div>
    </section>

    {{-- ===== Status ===== --}}
    <section aria-labelledby="s-status" class="space-y-4">
        <h2 id="s-status" class="type-h2">Status &amp; umpan balik</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.alert tone="info" title="Informasi">Pesan hanya dibaca perangkat desa yang berwenang.</x-ui.alert>
            <x-ui.alert tone="sukses" title="Berhasil">Perubahan berhasil disimpan.</x-ui.alert>
            <x-ui.alert tone="peringatan" title="Perhatian">Angka belum diverifikasi oleh pihak desa.</x-ui.alert>
            <x-ui.alert tone="bahaya" title="Gagal">Perubahan belum tersimpan. Coba lagi.</x-ui.alert>
        </div>

        <x-ui.empty-state title="Belum ada berita yang dipublikasikan" description="Berita akan tampil di sini setelah dipublikasikan oleh perangkat desa.">
            <x-slot:action>
                <x-ui.button href="#" variant="secondary">Kembali ke Beranda</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.skeleton-card />
        </div>

        <div class="flex flex-wrap gap-3">
            <x-ui.button variant="secondary" x-data x-on:click="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Perubahan berhasil disimpan.', tone: 'sukses' } }))">Tampilkan toast sukses</x-ui.button>
            <x-ui.button variant="secondary" x-data x-on:click="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Perubahan belum tersimpan. Coba lagi.', tone: 'bahaya' } }))">Tampilkan toast gagal</x-ui.button>
            <x-ui.button variant="danger" x-data x-on:click="$dispatch('open-modal', 'demo-hapus')">Buka dialog hapus</x-ui.button>
        </div>
    </section>
</main>

<x-ui.modal name="demo-hapus" title="Hapus berita ini?" description="Berita “Judul berita contoh” akan dihapus dan tidak bisa dikembalikan.">
    <x-slot:footer>
        <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'demo-hapus')">Batal</x-ui.button>
        <x-ui.button variant="danger" x-on:click="$dispatch('close-modal', 'demo-hapus')">Hapus</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
</body>
</html>
