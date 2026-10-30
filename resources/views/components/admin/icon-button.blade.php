@props(['icon', 'label', 'href' => null, 'tone' => 'netral', 'newTab' => false, 'submit' => false, 'iconClass' => '']) {{-- tombol ikon aksi baris tabel; label wajib untuk pembaca layar --}}

@php
    $kelas = 'relative inline-flex size-10 items-center justify-center rounded-control border bg-surface after:absolute after:-inset-0.5 '
        .($tone === 'bahaya' ? 'border-border text-danger hover:border-danger hover:bg-danger-soft' : 'border-border text-text hover:bg-primary-soft');
@endphp

@if ($href)
    <a
        href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}"
        @if ($newTab) target="_blank" rel="noopener" @endif
        {{ $attributes->class([$kelas]) }}
    >
        <x-ui.icon :name="$icon" class="size-4 {{ $iconClass }}" />
    </a>
@else
    <button type="{{ $submit ? 'submit' : 'button' }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class([$kelas]) }}>
        <x-ui.icon :name="$icon" class="size-4 {{ $iconClass }}" />
    </button>
@endif
