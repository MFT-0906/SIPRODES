@props(['items' => [], 'gelap' => false]) {{-- [['label' => 'Beranda', 'href' => '/'], ['label' => 'Halaman saat ini']] — butir terakhir adalah halaman aktif; gelap: warna untuk latar gelap --}}

<nav aria-label="Jejak halaman" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 type-body-sm">
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                @if ($loop->last)
                    <span aria-current="page" @class(['text-text' => ! $gelap, 'text-surface' => $gelap])>{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['href'] }}" @class(['relative py-1 hover:underline after:absolute after:-inset-x-1 after:-inset-y-2', 'text-primary' => ! $gelap, 'text-accent' => $gelap])>{{ $item['label'] }}</a>
                    <span @class(['text-muted' => ! $gelap, 'text-surface/60' => $gelap]) aria-hidden="true">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
