@props(['name', 'position', 'photo' => null, 'photoAlt' => null])

@php
    throw_if(app()->isLocal() && $photo && is_null($photoAlt), \InvalidArgumentException::class, 'x-ui.card-official: photoAlt wajib diisi saat photo dipakai.');
@endphp

<article {{ $attributes->class(['flex flex-col items-center gap-1.5 rounded-[14px] border border-border bg-surface px-4 py-6 text-center']) }}>
    <div class="mb-1.5 flex size-24 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-primary">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $photoAlt }}" loading="lazy" class="size-full object-cover">
        @else
            <x-ui.icon name="user" class="size-10" />
        @endif
    </div>
    <h3 class="type-label text-text">{{ $name }}</h3>
    <p class="type-body-sm text-muted">{{ $position }}</p>
</article>
