@props(['cols', 'aktif' => false]) {{-- baris tabel responsif: kartu bertumpuk di HP, grid di layar lebar --}}

<tr
    style="--cols: {{ $cols }}"
    {{ $attributes->class([
        'flex flex-col gap-3 border-t border-border px-4 py-4 lg:grid lg:[grid-template-columns:var(--cols)] lg:items-center lg:gap-4 lg:px-5',
        'bg-primary-soft' => $aktif,
    ]) }}
>
    {{ $slot }}
</tr>
