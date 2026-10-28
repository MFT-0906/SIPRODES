@props(['message' => null, 'tone' => 'sukses']) {{-- letakkan sekali di layout; `message` mengisi notifikasi awal (mis. dari session flash) --}}

{{-- Munculkan notifikasi baru: window.dispatchEvent(new CustomEvent('toast', { detail: { message: '...', tone: 'sukses' } })) --}}
<div
    x-data="toastRegion({{ Js::from($message) }}, {{ Js::from($tone) }})"
    x-on:toast.window="show($event.detail)"
    role="region" aria-label="Notifikasi" aria-live="polite"
    class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col items-center gap-2 sm:left-auto sm:right-6 sm:items-end"
>
    <template x-for="item in items" :key="item.id">
        <div
            x-transition.opacity
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-control border bg-surface p-3.5 shadow-[0_8px_24px_rgb(0_0_0/0.12)]"
            :class="item.tone === 'bahaya' ? 'border-danger' : 'border-success'"
        >
            <span class="mt-0.5 shrink-0" :class="item.tone === 'bahaya' ? 'text-danger' : 'text-success'">
                <x-ui.icon name="check" class="size-5" x-show="item.tone !== 'bahaya'" />
                <x-ui.icon name="alert" class="size-5" x-show="item.tone === 'bahaya'" x-cloak />
            </span>
            <p class="min-w-0 flex-1 type-body-sm text-text" x-text="item.message"></p>
            <button
                type="button" x-on:click="dismiss(item.id)" aria-label="Tutup notifikasi"
                class="relative -m-1 inline-flex size-8 shrink-0 items-center justify-center rounded-[6px] text-text hover:bg-primary-soft after:absolute after:-inset-1"
            >
                <x-ui.icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
