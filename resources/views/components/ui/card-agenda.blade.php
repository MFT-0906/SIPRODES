@props(['day', 'month', 'title', 'time' => null, 'location' => null]) {{-- lokasi disembunyikan bila belum terverifikasi --}}

<article {{ $attributes->class(['flex items-start gap-4 rounded-card border border-border bg-surface p-4']) }}>
    <div class="flex size-16 shrink-0 flex-col items-center justify-center rounded-[10px] bg-primary-soft text-center text-primary">
        <span class="type-h2">{{ $day }}</span>
        <span class="type-label-sm">{{ $month }}</span>
    </div>

    <div class="flex min-w-0 flex-1 flex-col gap-1">
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
