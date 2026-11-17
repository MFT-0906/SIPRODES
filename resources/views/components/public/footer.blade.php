{{--
    Kaki halaman publik: hak cipta dan tautan kebijakan di kiri, kartu ringkas "Statistik Pengunjung" di kanan
    (di atas pada layar HP). Kartu hanya tampil bila STATISTIK_PUBLIK aktif. Tidak memuat data kontak agar tidak
    menampilkan kontak yang belum diverifikasi desa.
--}}
@php
    $kunjungan = \App\Support\StatistikPengunjung::publik();
    $angka = fn (int $n) => number_format($n, 0, ',', '.');
    $baris = $kunjungan ? [
        'Hari ini' => $kunjungan['hari_ini'],
        'Kemarin' => $kunjungan['kemarin'],
        'Jumlah pengunjung' => $kunjungan['total'],
    ] : [];
@endphp
<footer class="on-dark bg-primary-dark text-surface">
    <div class="container-page flex flex-col gap-6 py-8 md:flex-row md:items-start md:justify-between md:gap-8 lg:py-10">
        @if ($kunjungan)
            <section aria-labelledby="statistik-footer-judul" class="w-full rounded-card border border-surface/20 bg-surface/10 p-4 md:order-last md:w-[320px] md:shrink-0">
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

        <div class="flex flex-col gap-1">
            <p class="type-body-sm text-surface/85">&copy; {{ now()->year }} {{ config('desa.wilayah.sebutan') }} {{ config('desa.wilayah.nama') }}. Seluruh hak dilindungi.</p>
            <a href="{{ route('kebijakan-privasi') }}" class="inline-flex min-h-11 items-center type-label text-surface underline underline-offset-4 hover:text-accent">Kebijakan Privasi</a>
        </div>
    </div>
</footer>
