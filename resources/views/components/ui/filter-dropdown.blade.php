@props(['name', 'label', 'options' => [], 'selected' => null]) {{-- options: [nilai => teks]; memilih opsi membuka URL dengan ?{name}={nilai} --}}

@php
    // TODO(backend): controller membaca parameter query ini untuk menyaring daftar.
    $selected = $selected ?? request($name, array_key_first($options));
    $currentText = $options[$selected] ?? (reset($options) ?: '');
@endphp

<div
    x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false"
    {{ $attributes->class(['relative inline-block']) }}
>
    <button
        type="button" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-haspopup="true"
        class="flex min-h-11 items-center gap-2 rounded-control border border-border bg-surface px-3 py-2.5 type-label text-text hover:bg-primary-soft"
    >
        <span>{{ $label }}: {{ $currentText }}</span>
        <x-ui.icon name="chevron-down" class="size-4" />
    </button>

    <ul
        x-show="open" x-cloak
        class="absolute left-0 z-20 mt-1.5 min-w-full rounded-control border border-border bg-surface p-1.5 shadow-[0_8px_24px_rgb(0_0_0/0.08)]"
    >
        @foreach ($options as $value => $text)
            @php $active = (string) $selected === (string) $value; @endphp
            <li>
                <a
                    href="{{ request()->fullUrlWithQuery([$name => $value, 'page' => null]) }}"
                    @if ($active) aria-current="true" @endif
                    class="flex min-h-11 items-center justify-between gap-3 rounded-[6px] px-3 py-2.5 type-body whitespace-nowrap hover:bg-primary-soft {{ $active ? 'bg-primary-soft text-primary' : 'text-text' }}"
                >
                    {{ $text }}
                    @if ($active)
                        <x-ui.icon name="check" class="size-4" />
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
