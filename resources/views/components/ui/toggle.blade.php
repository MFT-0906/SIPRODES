@props(['name', 'label', 'checked' => false, 'onLabel' => 'Aktif', 'offLabel' => 'Nonaktif', 'id' => null])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $isChecked = session()->hasOldInput() ? (bool) old($name) : $checked;
@endphp

{{-- Dikirim sebagai value="1" saat aktif; backend membacanya sebagai boolean. --}}
<label for="{{ $id }}" {{ $attributes->only('class')->class(['flex min-h-11 cursor-pointer items-center gap-3 has-disabled:cursor-not-allowed has-disabled:opacity-45']) }}>
    <input
        id="{{ $id }}" type="checkbox" role="switch" name="{{ $name }}" value="1" @checked($isChecked)
        {{ $attributes->except('class') }}
        class="peer sr-only"
    >
    <span
        class="relative h-6 w-11 shrink-0 rounded-pill bg-border transition-colors after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-surface after:shadow-sm after:transition-transform peer-checked:bg-primary peer-checked:after:translate-x-5 peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary"
        aria-hidden="true"
    ></span>
    {{-- Teks status agar keadaan tidak hanya dibedakan warna. --}}
    <span class="type-body-sm text-muted peer-checked:hidden"><span class="sr-only">{{ $label }}: </span>{{ $offLabel }}</span>
    <span class="type-body-sm hidden text-muted peer-checked:inline"><span class="sr-only">{{ $label }}: </span>{{ $onLabel }}</span>
</label>
