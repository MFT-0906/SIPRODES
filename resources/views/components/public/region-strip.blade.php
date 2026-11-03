@php
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
@endphp

<div role="region" aria-label="Wilayah administrasi" class="border-b border-border bg-background">
    <div class="container-page flex min-h-10 items-center gap-2 py-2.5">
        <x-ui.icon name="map-pin" class="size-4 text-primary" />
        <p class="type-label-sm text-text">
            <span class="md:hidden">{{ $identitas['wilayah_strip_pendek'] }}</span>
            <span class="hidden md:inline">{{ $identitas['wilayah_strip'] }}</span>
        </p>
    </div>
</div>
