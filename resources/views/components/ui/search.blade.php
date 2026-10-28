@props(['name' => 'q', 'label' => 'Cari', 'placeholder' => 'Cari…', 'value' => null, 'id' => null])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $value = $value ?? request($name);
@endphp

<div {{ $attributes->only('class')->class(['relative']) }}>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-[18px] -translate-y-1/2 text-text" />
    <input
        id="{{ $id }}" type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
        {{ $attributes->except('class') }}
        class="block h-11 w-full rounded-control border border-border bg-surface pl-10 pr-3 type-body text-text transition-shadow placeholder:text-muted focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)] focus-visible:outline-none"
    >
</div>
