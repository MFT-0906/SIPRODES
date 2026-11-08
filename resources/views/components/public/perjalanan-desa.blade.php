{{--
    Panel perjalanan transmigrasi bedol desa: dari desa asal di Jawa Tengah ke Marga Agung di Lampung.
    Data asal ada di config/desa.php ('asal'), tujuan dari wilayah administrasi desa.
--}}
@php
    $asal = config('desa.asal');
    $tujuan = config('desa.wilayah');
@endphp

<figure {{ $attributes->class(['relative overflow-hidden rounded-[16px] border border-border bg-background p-5 md:p-6']) }}>
    <figcaption class="mb-4 type-label text-primary">Perjalanan bedol desa</figcaption>

    <div class="grid items-center gap-3 md:grid-cols-[minmax(0,1fr)_minmax(120px,0.8fr)_minmax(0,1fr)] md:gap-2">
        {{-- Asal --}}
        <div class="flex items-center gap-3 rounded-[12px] border border-border bg-surface p-3.5">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                <x-ui.icon name="map-pin" class="size-5" />
            </span>
            <span class="flex min-w-0 flex-col">
                <span class="type-label-sm text-muted">Desa asal</span>
                <span class="type-label text-text">{{ $asal['desa'] }}</span>
                <span class="type-body-sm text-muted">{{ $asal['wilayah'] }}</span>
            </span>
        </div>

        {{-- Lintasan: garis putus-putus melengkung (mendatar di layar lebar, menurun di HP) --}}
        <div class="flex items-center justify-center py-1 md:py-0" aria-hidden="true">
            <svg viewBox="0 0 160 60" class="hidden h-[60px] w-full md:block" fill="none">
                <path d="M6 44 C46 4 114 4 152 40" stroke="#1b5e20" stroke-width="2" stroke-dasharray="5 6" stroke-linecap="round" />
                <path d="M143 33 L153 41 L141 44" stroke="#1b5e20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <text x="80" y="58" text-anchor="middle" fill="#5f6b5b" font-size="10" font-family="inherit">menyeberang Selat Sunda</text>
            </svg>
            <svg viewBox="0 0 40 44" class="h-11 w-10 md:hidden" fill="none">
                <path d="M20 2 C34 14 6 26 20 40" stroke="#1b5e20" stroke-width="2" stroke-dasharray="4 5" stroke-linecap="round" />
                <path d="M14 34 L20 41 L26 34" stroke="#1b5e20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        {{-- Tujuan --}}
        <div class="flex items-center gap-3 rounded-[12px] border border-primary bg-primary p-3.5 text-surface">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent text-text">
                <x-ui.icon name="home" class="size-5" />
            </span>
            <span class="flex min-w-0 flex-col">
                <span class="type-label-sm text-accent-soft">Kini</span>
                <span class="type-label">Desa {{ $tujuan['nama'] }}</span>
                <span class="type-body-sm text-primary-soft">Kec. Jati Agung, Kab. Lampung Selatan, Lampung</span>
            </span>
        </div>
    </div>
</figure>
