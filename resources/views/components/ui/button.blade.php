@props([
    'variant' => 'primary', // primary | accent | secondary | ghost | danger | terang
    'size' => 'm', // m | s
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'bg-primary text-surface hover:bg-primary-dark',
        'accent' => 'bg-accent text-text hover:bg-accent-dark',
        'secondary' => 'border-[1.5px] border-primary bg-surface text-primary hover:bg-primary-soft',
        'ghost' => 'text-primary hover:bg-primary-soft',
        'danger' => 'bg-danger text-surface hover:bg-danger-dark',
        // Untuk latar gelap (mis. hero beranda): garis putih tipis, mengisi samar saat disorot.
        'terang' => 'border-[1.5px] border-surface/60 text-surface hover:border-surface hover:bg-surface/10',
    ];
    $sizes = [
        'm' => 'min-h-12 px-5 py-3',
        // Tinggi visual 36 px sesuai Figma; area sentuh diperluas jadi 44 px.
        's' => 'relative min-h-9 px-3.5 py-2 after:absolute after:-inset-1',
    ];
    $inactive = $disabled || $loading;
    $tag = $href ? 'a' : 'button';
    $attrs = $href
        ? ['href' => $inactive ? null : $href, 'role' => $inactive ? 'link' : null, 'aria-disabled' => $inactive ? 'true' : null, 'tabindex' => $inactive ? '-1' : null]
        : ['type' => $type, 'disabled' => $inactive, 'data-submit-loading' => $type === 'submit' ? true : null];
    $attrs['aria-busy'] = $loading ? 'true' : null;
@endphp

<{{ $tag }}
    {{ $attributes->merge($attrs)->class([
        'group inline-flex items-center justify-center gap-2 rounded-control type-label whitespace-nowrap transition-colors',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['m'],
        'opacity-45' => $disabled,
        'opacity-80' => $loading && ! $disabled,
        'pointer-events-none' => $inactive,
    ]) }}
>
    <svg class="hidden size-4 animate-spin group-aria-busy:block" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
        <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
    {{ $slot }}
    @if ($icon)
        <x-ui.icon :name="$icon" :class="$size === 's' ? 'size-4' : 'size-[18px]'" />
    @endif
</{{ $tag }}>
