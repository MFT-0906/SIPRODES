{{-- Kerangka saat daftar kartu dimuat. Animasi berdenyut otomatis berhenti bila pengguna memilih reduced-motion. --}}
<div aria-hidden="true" {{ $attributes->class(['animate-pulse overflow-hidden rounded-[14px] border border-border bg-surface']) }}>
    <div class="h-[180px] bg-border opacity-60"></div>
    <div class="flex flex-col gap-2.5 p-5">
        <div class="h-3 w-24 rounded-[6px] bg-border opacity-70"></div>
        <div class="h-4 w-4/5 rounded-[6px] bg-border opacity-70"></div>
        <div class="h-4 w-3/5 rounded-[6px] bg-border opacity-70"></div>
        <div class="h-3 w-11/12 rounded-[6px] bg-border opacity-70"></div>
        <div class="h-3 w-2/5 rounded-[6px] bg-border opacity-70"></div>
    </div>
</div>
