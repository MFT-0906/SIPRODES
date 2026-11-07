@props(['name', 'title', 'description' => null, 'size' => 'md', 'open' => false]) {{-- slot `footer` opsional; buka dengan $dispatch('open-modal', 'nama'), tutup dengan $dispatch('close-modal', 'nama') --}}

@php
    $sizes = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-xl'];
    $titleId = 'modal-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name).'-title';
@endphp

<div
    x-data="{ open: {{ $open ? 'true' : 'false' }} }"
    x-on:open-modal.window="if ($event.detail === {{ Js::from($name) }}) open = true"
    x-on:close-modal.window="if ($event.detail === {{ Js::from($name) }}) open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open" x-cloak x-trap.noscroll="open"
    role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}" @if ($description) aria-describedby="{{ $titleId }}-desc" @endif
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div class="fixed inset-0 bg-text/50" aria-hidden="true"></div>

    {{-- Pembungkus yang bisa digulir: dialog yang lebih tinggi daripada layar tetap terjangkau sampai tombol terakhirnya. --}}
    <div class="relative grid min-h-full place-items-center p-4" x-on:click.self="open = false">
        <div {{ $attributes->class(['relative w-full rounded-card bg-surface p-6 shadow-[0_16px_48px_rgb(0_0_0/0.2)]', $sizes[$size] ?? $sizes['md']]) }}>
            <button
                type="button" x-on:click="open = false" aria-label="Tutup dialog"
                class="absolute right-3 top-3 inline-flex size-11 items-center justify-center rounded-control text-text hover:bg-primary-soft"
            >
                <x-ui.icon name="x" class="size-5" />
            </button>

            <h2 id="{{ $titleId }}" class="type-h3 pr-10 text-text">{{ $title }}</h2>
            @if ($description)
                <p id="{{ $titleId }}-desc" class="type-body-sm mt-2 text-muted">{{ $description }}</p>
            @endif

            @if (! $slot->isEmpty())
                <div class="mt-4">{{ $slot }}</div>
            @endif

            @isset($footer)
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
