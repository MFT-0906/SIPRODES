@props(['name', 'label', 'value', 'checked' => false, 'id' => null])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name.'-'.$value);
    $isChecked = session()->hasOldInput() ? (string) old($name) === (string) $value : $checked;
@endphp

<label for="{{ $id }}" {{ $attributes->only('class')->class(['flex min-h-11 cursor-pointer items-center gap-2.5 has-disabled:cursor-not-allowed has-disabled:opacity-45']) }}>
    <input
        id="{{ $id }}" type="radio" name="{{ $name }}" value="{{ $value }}" @checked($isChecked)
        {{ $attributes->except('class') }}
        class="size-5 shrink-0 appearance-none rounded-full border-[1.5px] border-muted bg-surface checked:border-primary checked:bg-primary checked:shadow-[inset_0_0_0_3px_var(--color-surface)]"
    >
    <span class="type-body text-text">{{ $label }}</span>
</label>
