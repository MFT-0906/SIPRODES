@props(['judul', 'pengguna', 'breadcrumb' => null])

{{-- Topbar admin (Figma 17:188). Di HP dilengkapi tombol pembuka menu samping (lingkup Alpine `open` dari layout). --}}
<div class="flex items-center justify-between gap-3 border-b border-border bg-surface px-4 py-3 lg:px-8 lg:py-4">
    <div class="flex min-w-0 items-center gap-3">
        <button
            type="button" x-ref="pemicu" x-on:click="open = true"
            x-bind:aria-expanded="open" aria-controls="menu-samping-admin" aria-label="Buka menu"
            class="flex size-11 shrink-0 items-center justify-center rounded-[10px] border border-border text-text lg:hidden"
        >
            <x-ui.icon name="menu" class="size-[22px]" />
        </button>
        <div class="flex min-w-0 flex-col gap-0.5 leading-[1.4]">
            <p class="hidden truncate type-label-sm text-muted sm:block">{{ $breadcrumb ?? 'Dashboard / '.$judul }}</p>
            <p class="truncate type-h3 text-text">{{ $judul }}</p>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <a
            href="{{ route('home') }}" target="_blank" rel="noopener"
            class="flex min-h-11 items-center gap-1.5 rounded-control border border-border px-3 py-2 type-label text-text hover:bg-background"
        >
            <x-ui.icon name="eye" class="size-4" />
            <span class="sr-only sm:not-sr-only">Lihat situs publik</span>
            <span class="sr-only">(buka di tab baru)</span>
        </a>

        <div class="relative" x-data="{ akun: false }" x-on:keydown.escape="akun = false" x-on:click.outside="akun = false">
            <button
                type="button" x-on:click="akun = ! akun" x-bind:aria-expanded="akun" aria-haspopup="menu"
                class="flex min-h-11 items-center gap-2 rounded-full border border-border px-2.5 py-1.5 type-label text-text hover:bg-background"
            >
                <span class="flex size-7 items-center justify-center rounded-full bg-primary text-surface">
                    <x-ui.icon name="user" class="size-3.5" />
                </span>
                <span class="sr-only sm:not-sr-only">{{ $pengguna['name'] }}</span>
                <x-ui.icon name="chevron-down" class="hidden size-4 sm:block" />
            </button>
            <div
                x-show="akun" x-cloak role="menu"
                class="absolute right-0 top-full z-30 mt-2 w-48 rounded-control border border-border bg-surface p-1 shadow-[0_8px_24px_rgb(15_51_33/0.12)]"
            >
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" role="menuitem" class="flex min-h-11 w-full items-center gap-2 rounded-[6px] px-3 type-label text-text hover:bg-background">
                        <x-ui.icon name="logout" class="size-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
