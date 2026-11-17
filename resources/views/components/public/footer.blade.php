{{--
    Kaki halaman publik. Memuat blok "Statistik Pengunjung" (bila STATISTIK_PUBLIK aktif), hak cipta, dan tautan kebijakan.
    Tidak memuat data kontak agar tidak menampilkan kontak yang belum diverifikasi desa.
--}}
@php
    $kunjungan = \App\Support\StatistikPengunjung::publik();
    $angka = fn (int $n) => number_format($n, 0, ',', '.');
    $statistik = $kunjungan ? [
        ['Hari ini', $kunjungan['hari_ini'], 'eye'],
        ['Kemarin', $kunjungan['kemarin'], 'clock'],
        ['30 hari terakhir', $kunjungan['tiga_puluh_hari'], 'calendar'],
        ['Total', $kunjungan['total'], 'users'],
    ] : [];
@endphp
<footer class="on-dark bg-primary-dark text-surface">
    <div class="container-page flex flex-col gap-6 py-8 lg:gap-8 lg:py-10">
        @if ($kunjungan)
            <section aria-labelledby="statistik-footer-judul" class="flex flex-col gap-4">
                <h2 id="statistik-footer-judul" class="type-h3 text-surface">Statistik Pengunjung</h2>
                <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
                    @foreach ($statistik as [$label, $nilai, $ikon])
                        <div class="flex items-center gap-3 rounded-card border border-surface/20 bg-surface/10 p-4">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-surface/15 text-accent">
                                <x-ui.icon :name="$ikon" class="size-5" />
                            </span>
                            <div class="flex min-w-0 flex-col-reverse">
                                <dt class="type-label-sm text-surface/85">{{ $label }}</dt>
                                <dd class="type-h2 tabular-nums text-surface">{{ $angka($nilai) }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <div class="flex flex-col gap-1 @if ($kunjungan) border-t border-surface/20 pt-5 @endif md:flex-row md:items-center md:justify-between md:gap-6">
            <p class="type-body-sm text-surface/85">&copy; {{ now()->year }} {{ config('desa.wilayah.sebutan') }} {{ config('desa.wilayah.nama') }}. Seluruh hak dilindungi.</p>
            <a href="{{ route('kebijakan-privasi') }}" class="inline-flex min-h-11 items-center type-label text-surface underline underline-offset-4 hover:text-accent">Kebijakan Privasi</a>
        </div>
    </div>
</footer>
