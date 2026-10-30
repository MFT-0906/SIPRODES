@props(['heads', 'cols', 'caption']) {{-- heads: [[label, 'left'|'right'], ...]; cols: nilai grid-template-columns untuk layar lebar; slot: baris <x-admin.tr> --}}

<table class="block w-full">
    <caption class="sr-only">{{ $caption }}</caption>
    <thead class="block">
        <tr style="--cols: {{ $cols }}" class="hidden border-t border-border bg-background px-5 py-3 type-label-sm text-muted lg:grid lg:[grid-template-columns:var(--cols)] lg:gap-4">
            @foreach ($heads as [$label, $rata])
                <th scope="col" class="{{ $rata === 'right' ? 'text-right' : 'text-left' }} font-[inherit]">{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="block">
        {{ $slot }}
    </tbody>
</table>
