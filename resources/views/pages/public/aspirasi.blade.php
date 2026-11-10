@extends('layouts.public')

@section('title', 'Ruang Aspirasi')
@section('description', 'Sampaikan pertanyaan, keluhan, atau saran kepada Desa Marga Agung.')

@php
    $terkirim = (bool) session('aspirasi_terkirim');
    $jumlahGalat = $errors->count();
    $pesanGalat = $jumlahGalat ? "Ada {$jumlahGalat} isian yang perlu diperbaiki sebelum pesan dapat dikirim." : '';

    $langkah = [
        ['Kirim pesan', 'Isi nama dan pesan. NIK tidak wajib.'],
        ['Dibaca perangkat desa', 'Status pesan berubah menjadi “Sudah Dibaca”.'],
        ['Ditindaklanjuti', 'Pesan ditandai “Selesai” setelah ditangani.'],
    ];
    $privasi = [
        'Data dikumpulkan hanya untuk menindaklanjuti aspirasi Anda.',
        'Pesan hanya dapat dibaca perangkat desa yang berwenang.',
        'Aspirasi tidak ditampilkan ke publik.',
        config('desa.aspirasi.nik_aktif')
            ? 'NIK bersifat opsional — kosongkan jika tidak diperlukan. Bila diisi, NIK disimpan terenkripsi.'
            : 'Kami tidak meminta NIK Anda.',
        'Nama dan NIK dianonimkan otomatis '.config('desa.aspirasi.masa_simpan_bulan').' bulan setelah aspirasi selesai ditangani.',
    ];
    $tips = [
        ['Ceritakan dengan jelas', 'Sebutkan hal yang ingin Anda sampaikan dan lokasinya.'],
        ['Gunakan bahasa yang sopan', 'Fokus pada masalah dan saran perbaikan.'],
        ['Periksa sebelum mengirim', 'Pastikan pesan lengkap dan tidak memuat data sensitif.'],
    ];
@endphp

@section('content')
    {{-- Judul halaman --}}
    <x-public.kepala-halaman jejak="Ruang Aspirasi" judul="Ruang Aspirasi Warga" pengantar="Sampaikan pertanyaan, keluhan, atau saran kepada Desa Marga Agung." />

    <div class="bg-surface">
        <div class="container-page grid gap-8 pb-14 pt-8 lg:grid-cols-[minmax(0,1fr)_620px] lg:gap-12 lg:pb-20 lg:pt-10">
            {{-- Formulir / keadaan berhasil --}}
            <div class="order-1 flex flex-col gap-4 lg:order-2">
                @if ($terkirim)
                    <section
                        aria-labelledby="berhasil-judul" tabindex="-1" x-data x-init="$el.focus()"
                        class="flex flex-col items-center gap-4 rounded-card border border-border bg-surface px-6 py-10 text-center focus:outline-none lg:px-10 lg:py-12"
                    >
                        <span class="flex size-[72px] items-center justify-center rounded-full bg-success-soft text-success">
                            <x-ui.icon name="check" class="size-9" />
                        </span>
                        <h2 id="berhasil-judul" class="type-h2 text-text">Aspirasi berhasil dikirim</h2>
                        <p class="type-body text-muted">Terima kasih. Pesan Anda telah diterima dan akan dibaca oleh perangkat desa yang berwenang. Kami tidak menampilkan isi pesan Anda ke publik.</p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <x-ui.button :href="route('aspirasi.create')" variant="secondary">Kirim aspirasi lain</x-ui.button>
                            <x-ui.button :href="route('home')">Kembali ke Beranda</x-ui.button>
                        </div>
                    </section>
                @else
                    {{-- Catatan privasi ringkas untuk HP (versi lengkap ada di bawah formulir). --}}
                    <p class="flex items-start gap-3 rounded-[12px] bg-primary-soft p-3.5 type-body-sm text-text lg:hidden">
                        <x-ui.icon name="shield" class="mt-0.5 size-5 text-primary" />
                        <span>Pesan hanya dibaca perangkat desa yang berwenang dan tidak ditampilkan ke publik. NIK tidak wajib.</span>
                    </p>

                    <form
                        method="post" action="{{ route('aspirasi.store') }}" novalidate
                        x-data="formAspirasi" x-on:submit="periksa($event)"
                        class="flex flex-col gap-5 rounded-card border border-border bg-surface p-5 lg:p-8"
                    >
                        @csrf

                        {{-- Anti-spam: token waktu terenkripsi dan kolom jebakan yang tidak terlihat oleh manusia. --}}
                        <input type="hidden" name="{{ \App\Support\AntiSpamAspirasi::KOLOM_TOKEN }}" value="{{ \App\Support\AntiSpamAspirasi::token() }}">
                        <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                            <input type="text" name="{{ \App\Support\AntiSpamAspirasi::KOLOM_JEBAKAN }}" value="" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="flex flex-col gap-2">
                            <h2 class="type-h2 text-text">Formulir aspirasi</h2>
                            <p class="type-body-sm text-muted">Kolom bertanda “Wajib” harus diisi.</p>
                        </div>

                        {{-- Ringkasan galat: dari server (setelah kirim) atau dari pemeriksaan di klien. --}}
                        <div
                            role="alert"
                            x-show="ringkasan !== '' || {{ $jumlahGalat ? 'true' : 'false' }}" @unless ($jumlahGalat) x-cloak @endunless
                            class="flex items-start gap-2.5 rounded-[10px] border border-danger bg-danger-soft px-4 py-3.5 type-body-sm text-danger"
                        >
                            <x-ui.icon name="alert" class="mt-0.5 size-5" />
                            <p x-text="ringkasan !== '' ? ringkasan : {{ \Illuminate\Support\Js::from($pesanGalat) }}">{{ $pesanGalat }}</p>
                        </div>

                        @error('formulir')
                            <x-ui.alert tone="bahaya">{{ $message }}</x-ui.alert>
                        @enderror

                        <x-ui.field name="nama" label="Nama" required client-validated placeholder="Tulis nama Anda" help="Boleh nama panggilan." autocomplete="name" />
                        @if (config('desa.aspirasi.nik_aktif'))
                            <x-ui.field
                                name="nik" label="NIK" client-validated placeholder="16 digit angka" inputmode="numeric" maxlength="16" autocomplete="off"
                                help="Tidak wajib. Isi hanya jika diminta untuk keperluan layanan tertentu."
                            />
                        @endif
                        <x-ui.field
                            name="pesan" label="Isi Pesan" multiline required client-validated :counter="1000"
                            placeholder="Tuliskan pertanyaan, keluhan, atau saran Anda…" help="Minimal 20 karakter"
                        />

                        <p class="flex items-start gap-2.5 rounded-[10px] bg-accent-soft px-3.5 py-3 type-body-sm text-text">
                            <x-ui.icon name="alert" class="mt-0.5 size-[18px] text-warning" />
                            <span>Hindari menulis kata sandi, nomor rekening, atau data pribadi orang lain di dalam pesan.</span>
                        </p>

                        <x-ui.button type="submit" icon="arrow-right" class="w-full">
                            <span class="group-aria-busy:hidden">Kirim Aspirasi</span>
                            <span class="hidden group-aria-busy:inline">Mengirim…</span>
                        </x-ui.button>

                        <p class="type-label-sm text-muted">Dilindungi pembatasan pengiriman untuk mencegah spam.</p>
                    </form>
                @endif
            </div>

            {{-- Informasi penanganan --}}
            <div class="order-2 flex flex-col gap-5 lg:order-1">
                <h2 class="type-h2 text-text">Bagaimana pesan Anda ditangani?</h2>

                <ol class="flex flex-col gap-5">
                    @foreach ($langkah as [$judul, $keterangan])
                        <li class="flex items-start gap-3.5">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary type-label text-surface">{{ $loop->iteration }}</span>
                            <div class="flex flex-col gap-0.5">
                                <p class="type-label text-text">{{ $judul }}</p>
                                <p class="type-body-sm text-muted">{{ $keterangan }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>

                <section aria-labelledby="privasi-judul" class="flex flex-col gap-2.5 rounded-[12px] bg-primary-soft p-5">
                    <h3 id="privasi-judul" class="flex items-center gap-2 type-label text-primary">
                        <x-ui.icon name="shield" class="size-5" />
                        Privasi Anda
                    </h3>
                    <ul class="flex flex-col gap-2">
                        @foreach ($privasi as $butir)
                            <li class="flex items-start gap-2 type-body-sm text-text">
                                <x-ui.icon name="check" class="mt-0.5 size-4 shrink-0 text-primary" />
                                <span>{{ $butir }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section aria-labelledby="tips-judul" class="flex flex-col gap-3.5 rounded-card border border-border bg-surface p-5">
                    <h3 id="tips-judul" class="type-h3 text-text">Agar mudah dipahami</h3>
                    <ul class="flex flex-col gap-3.5">
                        @foreach ($tips as [$judul, $keterangan])
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="check" class="mt-0.5 size-[18px] shrink-0 text-primary" />
                                <div class="flex flex-col gap-0.5">
                                    <p class="type-label text-text">{{ $judul }}</p>
                                    <p class="type-body-sm text-muted">{{ $keterangan }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>
        </div>
    </div>
@endsection
