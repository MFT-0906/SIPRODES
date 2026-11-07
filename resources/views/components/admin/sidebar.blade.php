@props(['menu', 'pengguna', 'peranLabel', 'aspirasiBaru' => 0])

{{-- Sidebar admin (Figma 17:78). Dipakai sebagai kolom tetap di desktop dan laci di HP. --}}
<div {{ $attributes->class(['flex h-full flex-col border-r border-border bg-surface']) }}>
    <div class="border-b border-border p-5">
        <x-public.logo tone="terang" caption="Dashboard Admin" :href="route('admin.dashboard')" />
    </div>

    <nav aria-label="Menu admin" class="petunjuk-gulir flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto p-3">
        @foreach ($menu as $grup)
            <p class="pb-1 pl-3 pt-2.5 type-label-sm uppercase text-muted">{{ $grup['label'] }}</p>
            @foreach ($grup['items'] as $item)
                @php
                    $aktif = request()->routeIs(...$item['match']);
                    $jumlah = isset($item['badge']) ? (int) $aspirasiBaru : 0;
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @if ($aktif) aria-current="page" @endif
                    @class([
                        'flex min-h-11 items-center gap-3 rounded-control px-3 py-2.5 type-label',
                        'bg-primary-soft text-primary' => $aktif,
                        'text-text hover:bg-background' => ! $aktif,
                    ])
                >
                    <x-ui.icon :name="$item['icon']" class="size-5 shrink-0" />
                    <span class="min-w-0 flex-1">{{ $item['label'] }}</span>
                    @if ($jumlah > 0)
                        <span class="rounded-full bg-danger px-2 py-0.5 type-label-sm text-surface">
                            {{ $jumlah }}<span class="sr-only"> aspirasi baru</span>
                        </span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="flex items-center gap-2.5 border-t border-border p-4">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
            <x-ui.icon name="user" class="size-[18px]" />
        </span>
        <div class="min-w-0 flex-1 leading-[1.4]">
            <p class="truncate type-label text-text">{{ $pengguna['name'] }}</p>
            <p class="type-label-sm text-muted">{{ $peranLabel }}</p>
        </div>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" aria-label="Keluar" class="flex size-11 items-center justify-center rounded-control text-text hover:bg-background">
                <x-ui.icon name="logout" class="size-[18px]" />
            </button>
        </form>
    </div>
</div>
