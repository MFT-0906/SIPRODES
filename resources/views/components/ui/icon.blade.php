@props(['name', 'label' => null])

@php
    // Ikon diambil dari SVG Figma (resources/svg/icons); warna mengikuti currentColor.
    $path = resource_path('svg/icons/'.$name.'.svg');
    $svg = preg_match('/^[a-z-]+$/', $name) && is_file($path) ? file_get_contents($path) : null;
    $sized = preg_match('/(^|\s)(size|w|h)-/', (string) $attributes->get('class'));
    $html = $attributes
        ->merge(['class' => $sized ? '' : 'size-6'])
        ->merge([
            'aria-hidden' => $label ? null : 'true',
            'role' => $label ? 'img' : null,
            'aria-label' => $label,
            'focusable' => 'false',
        ])
        ->class(['shrink-0'])
        ->toHtml();
@endphp

@if ($svg)
{!! preg_replace_callback('/<svg /', fn () => '<svg '.$html.' ', $svg, 1) !!}
@endif
