@props(['items', 'aktif', 'label' => 'Bagian halaman']) {{-- items: [kunci => [teks, url]] --}}

<nav aria-label="{{ $label }}" class="-mx-4 overflow-x-auto px-4 lg:mx-0 lg:px-0">
    <ul class="flex gap-6 border-b border-border">
        @foreach ($items as $kunci => [$teks, $url])
            @php $sedang = $aktif === $kunci; @endphp
            <li>
                <a
                    href="{{ $url }}" @if ($sedang) aria-current="page" @endif
                    @class([
                        'relative flex min-h-11 items-center whitespace-nowrap px-1 type-label',
                        'text-primary after:absolute after:inset-x-0 after:-bottom-px after:h-[3px] after:rounded-[2px] after:bg-primary' => $sedang,
                        'text-muted hover:text-text' => ! $sedang,
                    ])
                >{{ $teks }}</a>
            </li>
        @endforeach
    </ul>
</nav>
