@props(['items' => []]) {{-- [['label' => 'Beranda', 'href' => '/'], ['label' => 'Halaman saat ini']] — butir terakhir adalah halaman aktif --}}

<nav aria-label="Jejak halaman" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 type-body-sm">
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                @if ($loop->last)
                    <span aria-current="page" class="text-text">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['href'] }}" class="relative py-1 text-primary hover:underline after:absolute after:-inset-x-1 after:-inset-y-2">{{ $item['label'] }}</a>
                    <span class="text-muted" aria-hidden="true">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
