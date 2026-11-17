{{--
    Kaki halaman publik.
    - Desktop (md ke atas): tiga kolom sejajar rata atas (identitas, tautan cepat, kartu Statistik Pengunjung), garis
      pemisah, lalu satu baris hak cipta di kiri dan Kebijakan Privasi di kanan.
    - HP: hanya kartu statistik, lalu hak cipta dan Kebijakan Privasi. Identitas dan tautan cepat disembunyikan karena
      sudah ada di header dan menu HP, sehingga footer tidak terlalu panjang.
    Kartu statistik hanya tampil bila STATISTIK_PUBLIK aktif; bila hilang, dua kolom lain melebar.
    Tidak memuat kontak (telepon/email/alamat) karena belum diverifikasi desa.
--}}
@php
    $kunjungan = \App\Support\StatistikPengunjung::publik();
    $angka = fn (int $n) => number_format($n, 0, ',', '.');
    $baris = $kunjungan ? [
        'Hari ini' => $kunjungan['hari_ini'],
        'Kemarin' => $kunjungan['kemarin'],
        'Jumlah pengunjung' => $kunjungan['total'],
    ] : [];

    // Wilayah lengkap dari config desa ("Kecamatan …, Kabupaten …, Provinsi …."), satu bagian per baris.
    $wilayah = array_values(array_filter(array_map('trim', explode(',', rtrim((string) config('desa.wilayah.lengkap'), '.')))));

    $tautan = [
        ['Profil Desa', route('profil')],
        ['APBDes', route('apbdes')],
        ['Berita & Agenda', route('berita.index')],
        ['Ruang Aspirasi', route('aspirasi.create')],
    ];

    $kolomIdentitas = $kunjungan ? 'md:col-span-5' : 'md:col-span-7';
    $kolomTautan = $kunjungan ? 'md:col-span-3' : 'md:col-span-5';
@endphp
<footer class="on-dark bg-primary-dark text-surface">
    <div class="container-page flex flex-col gap-6 py-8 md:gap-8 lg:py-10">
        <div class="grid gap-6 md:grid-cols-12 md:items-start md:gap-8">
            <div class="hidden flex-col gap-3 md:flex {{ $kolomIdentitas }}">
                <x-public.logo tone="gelap" class="self-start" />
                @if ($wilayah !== [])
                    <p class="flex flex-col type-body-sm text-surface/85">
                        @foreach ($wilayah as $bagian)
                            <span>{{ $bagian }}</span>
                        @endforeach
                    </p>
                @endif
            </div>

            <nav aria-labelledby="tautan-footer-judul" class="hidden flex-col gap-1 md:flex {{ $kolomTautan }}">
                <h2 id="tautan-footer-judul" class="type-label text-surface">Tautan</h2>
                <ul class="flex flex-col">
                    @foreach ($tautan as [$label, $alamat])
                        <li>
                            <a href="{{ $alamat }}" class="inline-flex min-h-11 items-center type-body-sm text-surface/85 underline-offset-4 hover:text-surface hover:underline">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @if ($kunjungan)
                <section aria-labelledby="statistik-footer-judul" class="w-full rounded-card border border-surface/20 bg-surface/10 p-4 md:col-span-4">
                    <h2 id="statistik-footer-judul" class="flex items-center gap-2 pb-3 type-label text-surface">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-[8px] bg-surface/15 text-accent">
                            <x-ui.icon name="chart" class="size-[18px]" />
                        </span>
                        Statistik Pengunjung
                    </h2>
                    <dl>
                        @foreach ($baris as $label => $nilai)
                            <div class="flex items-baseline justify-between gap-4 border-t border-surface/20 py-2.5">
                                <dt class="type-body-sm text-surface/85">{{ $label }}</dt>
                                <dd class="type-label tabular-nums text-surface">{{ $angka($nilai) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif
        </div>

        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between md:gap-6 md:border-t md:border-surface/20 md:pt-6">
            <p class="type-body-sm text-surface/85">&copy; {{ now()->year }} {{ config('desa.wilayah.sebutan') }} {{ config('desa.wilayah.nama') }}. Seluruh hak dilindungi.</p>
            <a href="{{ route('kebijakan-privasi') }}" class="inline-flex min-h-11 items-center type-label text-surface underline underline-offset-4 hover:text-accent">Kebijakan Privasi</a>
        </div>
    </div>
</footer>
