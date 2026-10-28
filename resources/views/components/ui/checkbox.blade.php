@props(['name', 'label', 'value' => '1', 'checked' => false, 'id' => null])

@php
    $dotName = trim(preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name.'-'.$value);
    $isChecked = session()->hasOldInput()
        ? in_array((string) $value, array_map('strval', (array) old($dotName, [])), true)
        : $checked;
@endphp

<label for="{{ $id }}" {{ $attributes->only('class')->class(['flex min-h-11 cursor-pointer items-center gap-2.5 has-disabled:cursor-not-allowed has-disabled:opacity-45']) }}>
    <span class="relative grid size-5 shrink-0 place-items-center">
        <input
            id="{{ $id }}" type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($isChecked)
            {{ $attributes->except('class') }}
            class="peer size-5 appearance-none rounded-[4px] border-[1.5px] border-muted bg-surface checked:border-primary checked:bg-primary"
        >
        <x-ui.icon name="check" class="pointer-events-none absolute size-3.5 text-surface opacity-0 peer-checked:opacity-100" />
    </span>
    <span class="type-body text-text">{{ $label }}</span>
</label>
