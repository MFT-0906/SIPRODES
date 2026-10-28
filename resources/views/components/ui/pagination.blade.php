@props(['paginator' => null, 'current' => 1, 'last' => 1])

@php
    // TODO(backend): kirim paginator Laravel; tanpa itu komponen memakai parameter ?page= pada URL saat ini.
    $current = $paginator ? $paginator->currentPage() : $current;
    $last = $paginator ? $paginator->lastPage() : $last;
    $url = fn (int $page) => $paginator ? $paginator->url($page) : request()->fullUrlWithQuery(['page' => $page]);

    $pages = collect([1, $last, ...range(max(1, $current - 1), min($last, $current + 1))]);
    if ($current <= 2) {
        $pages = $pages->merge(range(1, min(3, $last)));
    }
    if ($current >= $last - 1) {
        $pages = $pages->merge(range(max(1, $last - 2), $last));
    }

    $items = [];
    $previous = 0;
    foreach ($pages->unique()->sort()->values() as $page) {
        if ($page - $previous > 1) {
            $items[] = null;
        }
        $items[] = $page;
        $previous = $page;
    }

    $box = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-control px-3.5 py-2.5 type-label';
@endphp

@if ($last > 1)
    <nav aria-label="Navigasi halaman" {{ $attributes }}>
        <ul class="flex flex-wrap items-center justify-center gap-1.5">
            <li>
                @if ($current > 1)
                    <a href="{{ $url($current - 1) }}" rel="prev" class="{{ $box }} border border-border bg-surface text-muted hover:bg-primary-soft">
                        <span aria-hidden="true">‹</span><span class="sr-only sm:not-sr-only sm:ml-1">Sebelumnya</span>
                    </a>
                @else
                    <span aria-disabled="true" class="{{ $box }} border border-border bg-surface text-muted opacity-60">
                        <span aria-hidden="true">‹</span><span class="sr-only sm:not-sr-only sm:ml-1">Sebelumnya</span>
                    </span>
                @endif
            </li>

            @foreach ($items as $page)
                <li>
                    @if (is_null($page))
                        <span class="{{ $box }} text-text" aria-hidden="true">…</span>
                    @elseif ($page === $current)
                        <span aria-current="page" class="{{ $box }} bg-primary text-surface"><span class="sr-only">Halaman </span>{{ $page }}</span>
                    @else
                        <a href="{{ $url($page) }}" class="{{ $box }} border border-border bg-surface text-text hover:bg-primary-soft"><span class="sr-only">Halaman </span>{{ $page }}</a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($current < $last)
                    <a href="{{ $url($current + 1) }}" rel="next" class="{{ $box }} border border-border bg-surface text-text hover:bg-primary-soft">
                        <span class="sr-only sm:not-sr-only sm:mr-1">Berikutnya</span><span aria-hidden="true">›</span>
                    </a>
                @else
                    <span aria-disabled="true" class="{{ $box }} border border-border bg-surface text-muted opacity-60">
                        <span class="sr-only sm:not-sr-only sm:mr-1">Berikutnya</span><span aria-hidden="true">›</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
