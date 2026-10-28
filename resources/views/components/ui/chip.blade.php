@props(['active' => false, 'href' => null])

@php
    $classes = 'inline-flex min-h-11 items-center justify-center rounded-pill px-4 py-2.5 type-label whitespace-nowrap transition-colors '
        .($active
            ? 'bg-primary text-surface'
            // aria-current dapat diubah klien (mis. navigasi bagian dengan scrollspy) tanpa mengganti kelas.
            : 'border border-border bg-surface text-text hover:bg-primary-soft aria-[current=true]:border-primary aria-[current=true]:bg-primary aria-[current=true]:text-surface');
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($active) aria-current="true" @endif {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="button" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
@endif
