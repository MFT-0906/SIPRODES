@props(['title', 'excerpt', 'date', 'category', 'href', 'image' => null, 'imageAlt' => null, 'utama' => false]) {{-- utama: kartu besar mendatar untuk berita terbaru --}}

@php
    // Setiap gambar wajib punya alt; isi `imageAlt` dengan deskripsi foto (kosongkan hanya jika murni dekoratif).
    throw_if(app()->isLocal() && $image && is_null($imageAlt), \InvalidArgumentException::class, 'x-ui.card-news: imageAlt wajib diisi saat image dipakai.');
@endphp

<article {{ $attributes->class([
    'group relative flex flex-col overflow-hidden rounded-card border border-border bg-surface transition-[box-shadow,border-color] duration-300 hover:border-primary/40 hover:shadow-[0_12px_32px_rgb(20_73_26/0.12)]',
    'md:flex-row' => $utama,
]) }}>
    @php $ukuranSampul = $utama ? 'h-[220px] md:h-auto md:min-h-[300px] md:w-[55%] md:shrink-0' : 'h-[190px]'; @endphp
    @if ($image)
        <div class="{{ $ukuranSampul }} overflow-hidden"><img src="{{ $image }}" alt="{{ $imageAlt }}" loading="lazy" class="size-full object-cover transition-transform duration-500 group-hover:scale-105"></div>
    @else
        <x-ui.sampul jenis="berita" :nama="$category" class="{{ $ukuranSampul }} w-full" />
    @endif

    <div @class(['flex flex-1 flex-col gap-2.5 p-5', 'md:justify-center md:gap-3 md:p-8' => $utama])>
        @if ($utama)
            <p class="type-label-sm text-warning">Berita terbaru</p>
        @endif
        <p class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
            <span class="type-label-sm text-primary">{{ $category }}</span>
            <span class="type-body-sm text-muted">{{ $date }}</span>
        </p>
        <h3 @class(['text-text transition-colors group-hover:text-primary', 'type-h3' => ! $utama, 'type-h3 md:type-h1' => $utama])>{{ $title }}</h3>
        <p @class(['text-muted', 'type-body-sm' => ! $utama, 'type-body-sm md:type-body' => $utama])>{{ $excerpt }}</p>
        <a href="{{ $href }}" class="mt-auto inline-flex items-center gap-1.5 type-label text-primary after:absolute after:inset-0">
            Baca selengkapnya
            <x-ui.icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
            <span class="sr-only">: {{ $title }}</span>
        </a>
    </div>
</article>
