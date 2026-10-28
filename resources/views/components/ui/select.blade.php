@props([
    'name',
    'label',
    'options' => [], // [nilai => teks]; atau isi slot dengan <option>
    'selected' => null,
    'placeholder' => 'Pilih salah satu',
    'required' => false,
    'penanda' => true, // false menyembunyikan teks Wajib/Opsional (mis. pemilih tahun)
    'help' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $error = $error ?? (isset($errors) && $errors->has($name) ? $errors->first($name) : null);
    $selected = old($name, $selected);
    $describedBy = trim(($error ? $id.'-error ' : '').(! $error && $help ? $id.'-help' : ''));
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="flex items-baseline gap-1.5">
        <span class="type-label text-text">{{ $label }}</span>
        @if ($penanda)
            <span class="type-label-sm {{ $required ? 'text-danger' : 'text-muted' }}">{{ $required ? 'Wajib' : 'Opsional' }}</span>
        @endif
    </label>

    <div class="relative">
        {{-- Memakai <select> asli agar daftar opsi dapat dioperasikan dengan keyboard dan pembaca layar. --}}
        <select
            id="{{ $id }}" name="{{ $name }}"
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([
                'block min-h-11 w-full appearance-none rounded-control border bg-surface py-3 pl-3.5 pr-10 type-body text-text transition-shadow focus-visible:outline-none has-[option[value=""]:checked]:text-muted',
                'border-danger shadow-[0_0_0_1px_var(--color-danger)]' => $error,
                'border-border focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)]' => ! $error,
            ]) }}
        >
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $selected === (string) $value) class="text-text">{{ $text }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <x-ui.icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 size-[18px] -translate-y-1/2 text-muted" />
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 type-body-sm text-danger">
            <x-ui.icon name="alert" class="mt-0.5 size-4" />
            <span>{{ $error }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="type-body-sm text-muted">{{ $help }}</p>
    @endif
</div>
