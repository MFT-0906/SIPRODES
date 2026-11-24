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
    'counter' => null, // batas karakter: menampilkan hitungan "n/batas" pada teks bantuan dan memasang maxlength
    'clientValidated' => false, // true: butuh lingkup Alpine dengan `err` (mis. formAspirasi) untuk pesan galat dari klien
    'bisaDilihat' => true, // khusus type=password: tombol tampilkan/sembunyikan kata sandi
])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $dotName = trim(preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $error = $error ?? (isset($errors) && $errors->has($dotName) ? $errors->first($dotName) : null);
    $value = $type === 'password' ? null : old($dotName, $value);
    $describedBy = trim(
        ($error ? $id.'-error ' : '')
        .($clientValidated ? $id.'-error-klien ' : '')
        .(! $error && $help ? $id.'-help' : '')
    );
    $control = 'block w-full rounded-control border bg-surface px-3.5 py-3 type-body text-text placeholder:text-muted transition-shadow focus-visible:outline-none '
        .($error
            ? 'border-danger shadow-[0_0_0_1px_var(--color-danger)]'
            : 'border-border focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)]');
    $key = \Illuminate\Support\Js::from($name);
    $intip = $type === 'password' && $bisaDilihat && ! $multiline;
@endphp

<div
    class="flex flex-col gap-1.5"
    @if ($counter) x-data="{ n: {{ mb_strlen((string) $value) }} }" x-on:input="n = $event.target.value.length" @endif
>
    <label for="{{ $id }}" class="flex items-baseline gap-1.5">
        <span class="type-label text-text">{{ $label }}</span>
        <span class="type-label-sm {{ $required ? 'text-danger' : 'text-muted' }}">{{ $required ? 'Wajib' : 'Opsional' }}</span>
    </label>

    @if ($multiline)
        <textarea
            id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
            @if ($counter) maxlength="{{ $counter }}" @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($clientValidated) x-bind:aria-invalid="err[{{ $key }}] || {{ $error ? 'true' : 'false' }} ? 'true' : null" x-bind:class="err[{{ $key }}] ? 'border-danger! shadow-[0_0_0_1px_var(--color-danger)]!' : ''" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([$control, 'min-h-30 resize-y max-lg:resize-none']) }}
        >{{ $value }}</textarea>
    @else
        @if ($intip)
            {{-- Tombol tampilkan kata sandi: input tetap type=password sampai pengguna memilih melihatnya. --}}
            <div class="relative" x-data="{ lihat: false }">
        @endif
        <input
            id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if (! is_null($value)) value="{{ $value }}" @endif
            @if ($counter) maxlength="{{ $counter }}" @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($clientValidated) x-bind:aria-invalid="err[{{ $key }}] || {{ $error ? 'true' : 'false' }} ? 'true' : null" x-bind:class="err[{{ $key }}] ? 'border-danger! shadow-[0_0_0_1px_var(--color-danger)]!' : ''" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($intip) x-bind:type="lihat ? 'text' : 'password'" @endif
            {{ $attributes->class([$control, 'min-h-11', 'pr-12' => $intip]) }}
        >
        @if ($intip)
                <button
                    type="button" x-on:click="lihat = !lihat"
                    x-bind:aria-pressed="lihat ? 'true' : 'false'" aria-pressed="false"
                    x-bind:aria-label="lihat ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi"
                    aria-controls="{{ $id }}"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-control text-muted transition-colors hover:text-primary focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary"
                >
                    <x-ui.icon name="eye" class="size-5" x-show="!lihat" />
                    <x-ui.icon name="eye-off" class="size-5" x-show="lihat" x-cloak />
                </button>
            </div>
        @endif
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 type-body-sm text-danger">
            <x-ui.icon name="alert" class="mt-0.5 size-4" />
            <span>{{ $error }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="type-body-sm text-muted">
            {{ $help }}
            @if ($counter)
                · <span x-text="n">{{ mb_strlen((string) $value) }}</span>/{{ $counter }}
            @endif
        </p>
    @endif

    @if ($clientValidated)
        <p id="{{ $id }}-error-klien" x-show="err[{{ $key }}]" x-cloak class="flex items-start gap-1.5 type-body-sm text-danger">
            <x-ui.icon name="alert" class="mt-0.5 size-4" />
            <span x-text="err[{{ $key }}]"></span>
        </p>
    @endif
</div>
