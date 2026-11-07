@props(['day', 'month', 'title', 'time' => null, 'location' => null, 'relatif' => null]) {{-- lokasi disembunyikan bila belum terverifikasi; relatif: "Besok", "3 hari lagi" --}}

@php $dekat = in_array($relatif, ['Hari ini', 'Besok'], true); @endphp

<article {{ $attributes->class(['group flex items-start gap-4 rounded-card border border-border bg-surface p-4 transition-colors hover:border-primary']) }}>
    <div @class([
        'flex size-16 shrink-0 flex-col items-center justify-center rounded-[10px] text-center transition-colors',
        'bg-primary text-surface' => $dekat,
        'bg-primary-soft text-primary group-hover:bg-primary group-hover:text-surface' => ! $dekat,
    ])>
        <span class="type-h2">{{ $day }}</span>
        <span class="type-label-sm">{{ $month }}</span>
    </div>

    <div class="flex min-w-0 flex-1 flex-col gap-1">
        @if ($relatif)
            <p class="flex items-center gap-1.5 type-label-sm {{ $dekat ? 'text-warning' : 'text-primary' }}">
                <span @class(['size-2 rounded-full', 'titik-denyut bg-accent' => $dekat, 'bg-primary' => ! $dekat]) aria-hidden="true"></span>
                {{ $relatif }}
            </p>
        @endif
        <h3 class="type-label text-text">{{ $title }}</h3>
        @if ($time)
            <p class="flex items-center gap-1.5 type-body-sm text-muted">
                <x-ui.icon name="clock" class="size-3.5" />
                {{ $time }}
            </p>
        @endif
        @if ($location)
            <p class="flex items-center gap-1.5 type-body-sm text-muted">
                <x-ui.icon name="map-pin" class="size-3.5" />
                {{ $location }}
            </p>
        @endif
    </div>
</article>
