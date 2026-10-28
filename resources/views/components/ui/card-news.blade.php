@props(['title', 'excerpt', 'date', 'category', 'href', 'image' => null, 'imageAlt' => null])

@php
    // Setiap gambar wajib punya alt; isi `imageAlt` dengan deskripsi foto (kosongkan hanya jika murni dekoratif).
    throw_if(app()->isLocal() && $image && is_null($imageAlt), \InvalidArgumentException::class, 'x-ui.card-news: imageAlt wajib diisi saat image dipakai.');
@endphp

<article {{ $attributes->class(['relative flex flex-col overflow-hidden rounded-card border border-border bg-surface transition-shadow hover:shadow-[0_8px_24px_rgb(0_0_0/0.08)]']) }}>
    @if ($image)
        <img src="{{ $image }}" alt="{{ $imageAlt }}" loading="lazy" class="h-[190px] w-full object-cover">
    @else
        <x-ui.image-placeholder class="h-[190px] w-full" />
    @endif

    <div class="flex flex-1 flex-col gap-2.5 p-5">
        <p class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
            <span class="type-label-sm text-primary">{{ $category }}</span>
            <span class="type-body-sm text-muted">{{ $date }}</span>
        </p>
        <h3 class="type-h3 text-text">{{ $title }}</h3>
        <p class="type-body-sm text-muted">{{ $excerpt }}</p>
        <a href="{{ $href }}" class="mt-auto inline-flex items-center gap-1.5 type-label text-primary after:absolute after:inset-0">
            Baca selengkapnya
            <x-ui.icon name="arrow-right" class="size-4" />
            <span class="sr-only">: {{ $title }}</span>
        </a>
    </div>
</article>
