@props([
    'name',
    'label',
    'type' => 'text',
    'multiline' => false,
    'required' => false,
    'help' => null,
    'error' => null,
    'value' => null,
    'rows' => 5,
    'id' => null,
])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $dotName = trim(preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $error = $error ?? (isset($errors) && $errors->has($dotName) ? $errors->first($dotName) : null);
    $value = $type === 'password' ? null : old($dotName, $value);
    $describedBy = trim(($error ? $id.'-error ' : '').(! $error && $help ? $id.'-help' : ''));
    $control = 'block w-full rounded-control border bg-surface px-3.5 py-3 type-body text-text placeholder:text-muted transition-shadow focus-visible:outline-none '
        .($error
            ? 'border-danger shadow-[0_0_0_1px_var(--color-danger)]'
            : 'border-border focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)]');
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="flex items-baseline gap-1.5">
        <span class="type-label text-text">{{ $label }}</span>
        <span class="type-label-sm {{ $required ? 'text-danger' : 'text-muted' }}">{{ $required ? 'Wajib' : 'Opsional' }}</span>
    </label>

    @if ($multiline)
        <textarea
            id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([$control, 'min-h-30 resize-y']) }}
        >{{ $value }}</textarea>
    @else
        <input
            id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if (! is_null($value)) value="{{ $value }}" @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([$control, 'min-h-11']) }}
        >
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 type-body-sm text-danger">
            <x-ui.icon name="alert" class="mt-0.5 size-4" />
            <span>{{ $error }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="type-body-sm text-muted">{{ $help }}</p>
    @endif
</div>
