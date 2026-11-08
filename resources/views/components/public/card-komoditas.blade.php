@props(['nama', 'deskripsi', 'unggulan' => false, 'musimPanen' => null, 'fotoKeterangan' => 'Foto komoditas', 'foto' => null, 'peluang' => []])

<article {{ $attributes->class(['group flex flex-col overflow-hidden rounded-[14px] border border-border bg-surface']) }}>
    @if ($foto)
        <img src="{{ $foto }}" alt="{{ $fotoKeterangan }}" loading="lazy" class="h-[180px] w-full object-cover">
    @else
        <x-ui.sampul jenis="komoditas" :nama="$nama" class="h-[180px] w-full" />
    @endif

    <div class="flex flex-1 flex-col gap-2.5 p-5">
        <div class="flex flex-wrap items-center gap-2">
            <h3 class="type-h3 text-text">{{ $nama }}</h3>
            @if ($unggulan)
                <x-ui.badge tone="aksen">Unggulan</x-ui.badge>
            @endif
        </div>
        <p class="type-body-sm text-muted">{{ $deskripsi }}</p>

        <p class="flex items-center gap-2 rounded-control bg-background px-3 py-2.5 type-body-sm text-text">
            <x-ui.icon name="calendar" class="size-4 shrink-0" />
            <span>Musim panen: {{ $musimPanen ?? 'belum tersedia' }}</span>
        </p>

        @if ($peluang)
            <div class="flex flex-col gap-1.5">
                <p class="type-label text-text">Peluang yang bisa dikembangkan</p>
                <ul class="flex flex-col gap-1.5">
                    @foreach ($peluang as $butir)
                        <li class="flex items-start gap-2 type-body-sm text-text">
                            <x-ui.icon name="check" class="mt-0.5 size-4 shrink-0 text-primary" />
                            <span>{{ $butir }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</article>
