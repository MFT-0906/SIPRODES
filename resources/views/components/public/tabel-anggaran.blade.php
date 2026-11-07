@props(['judul', 'rows', 'total' => null, 'totalLabel' => 'Jumlah', 'id'])

@php
    use App\Support\Format;

    $jumlah = $total;
    $persen = fn ($nilai) => ! is_null($nilai) && $jumlah ? Format::persen($nilai / $jumlah * 100) : '—';
    // Di HP hanya 4 baris pertama yang tampil; sisanya dibuka lewat "Lihat rincian lengkap".
    $batasHp = 4;
    $adaLipat = count($rows) > $batasHp;
@endphp

{{-- Tabel tetap menjadi acuan utama angka; di HP baris berubah menjadi kartu bertumpuk. --}}
<section aria-labelledby="{{ $id }}" x-data="{ semua: false }" {{ $attributes->class(['overflow-hidden rounded-[14px] border border-border bg-surface']) }}>
    <h3 id="{{ $id }}" class="type-h3 px-4 py-4 text-text md:px-6 md:py-[18px]">{{ $judul }}</h3>

    <table role="table" class="w-full border-collapse max-md:block">
        <thead class="max-md:sr-only">
            <tr role="row" class="border-t border-border bg-background type-label-sm text-muted">
                <th role="columnheader" scope="col" class="px-6 py-3.5 text-left font-medium">URAIAN</th>
                <th role="columnheader" scope="col" class="w-[150px] px-3 py-3.5 text-right font-medium">ANGGARAN (Rp)</th>
                <th role="columnheader" scope="col" class="w-[60px] py-3.5 pl-3 pr-6 text-right font-medium">%</th>
            </tr>
        </thead>
        <tbody class="max-md:block">
            @foreach ($rows as $baris)
                <tr
                    role="row"
                    @if ($adaLipat && $loop->index >= $batasHp) x-bind:class="{ 'max-md:grid!': semua }" @endif
                    class="border-t border-border type-body-sm text-text max-md:grid max-md:grid-cols-[1fr_auto] max-md:gap-x-4 max-md:gap-y-0.5 max-md:px-4 max-md:py-3 {{ $adaLipat && $loop->index >= $batasHp ? 'max-md:hidden' : '' }}"
                >
                    <td role="cell" class="px-6 py-3.5 max-md:col-span-2 max-md:p-0 max-md:type-label">{{ $baris['uraian'] }}</td>
                    <td role="cell" class="px-3 py-3.5 text-right tabular-nums max-md:p-0 max-md:text-left">{{ Format::rupiah($baris['anggaran']) }}</td>
                    <td role="cell" class="py-3.5 pl-3 pr-6 text-right tabular-nums max-md:p-0 max-md:text-muted">{{ $persen($baris['anggaran']) }}</td>
                </tr>
            @endforeach

            <tr
                role="row"
                @if ($adaLipat) x-bind:class="{ 'max-md:grid!': semua }" @endif
                class="border-t border-border bg-primary-soft type-label text-text max-md:grid max-md:grid-cols-[1fr_auto] max-md:gap-x-4 max-md:px-4 max-md:py-3 {{ $adaLipat ? 'max-md:hidden' : '' }}"
            >
                <td role="cell" class="px-6 py-3.5 max-md:col-span-2 max-md:p-0">{{ $totalLabel }}</td>
                <td role="cell" class="px-3 py-3.5 text-right tabular-nums max-md:p-0 max-md:text-left">{{ Format::rupiah($total) }}</td>
                <td role="cell" class="py-3.5 pl-3 pr-6 text-right tabular-nums max-md:p-0 max-md:text-muted">100%</td>
            </tr>
        </tbody>
    </table>

    @if ($adaLipat)
        <div class="border-t border-border md:hidden">
            <button
                type="button" x-on:click="semua = ! semua" x-bind:aria-expanded="semua"
                class="flex min-h-12 w-full items-center justify-center gap-1.5 type-label text-primary"
            >
                <span x-text="semua ? 'Tampilkan lebih sedikit' : 'Lihat rincian lengkap'">Lihat rincian lengkap</span>
                <x-ui.icon name="chevron-down" class="size-4 transition-transform" x-bind:class="{ 'rotate-180': semua }" />
            </button>
        </div>
    @endif
</section>
