@extends('layouts.admin')

@section('title', 'Aspirasi Warga')
@section('judul', 'Aspirasi Warga')
@section('breadcrumb', 'Dashboard / Layanan / Aspirasi Warga')

@php
    use App\Support\Format;
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $waktu = fn (string $w) => Carbon::parse($w)->locale('id')->translatedFormat('j M Y, H.i');
    $a = $dipilih ?? $bawaan;
    $chip = fn (string $kunci) => route('admin.aspirasi.index', array_filter(['status' => $kunci === 'semua' ? null : $kunci, 'q' => request('q')]));
    $kosong = [
        'baru' => ['Tidak ada aspirasi baru', 'Semua aspirasi sudah dibaca atau diselesaikan. Pilih filter lain untuk melihat riwayat.'],
        'dibaca' => ['Tidak ada aspirasi yang sudah dibaca', 'Aspirasi yang sudah dibaca akan muncul di sini.'],
        'selesai' => ['Belum ada aspirasi selesai', 'Aspirasi yang sudah ditindaklanjuti akan muncul di sini.'],
        'semua' => $semuaKosong ? ['Belum ada aspirasi', 'Aspirasi dari warga akan muncul di sini. Isinya hanya terlihat oleh perangkat desa yang berwenang.'] : ['Tidak ada aspirasi yang cocok', 'Coba kata kunci lain atau pilih filter lain.'],
    ][$filter];
@endphp

@section('content')
    <div class="flex flex-col gap-4 lg:gap-6">
        {{-- Filter status --}}
        <nav aria-label="Filter status aspirasi" class="-mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0">
            <x-ui.chip :href="$chip('semua')" :active="$filter === 'semua'" class="shrink-0 {{ $dipilih ? 'max-lg:hidden' : '' }}">Semua</x-ui.chip>
            <x-ui.chip :href="$chip('baru')" :active="$filter === 'baru'" class="shrink-0">Baru ({{ $jumlahBaru }})</x-ui.chip>
            <x-ui.chip :href="$chip('dibaca')" :active="$filter === 'dibaca'" class="shrink-0">Sudah Dibaca</x-ui.chip>
            <x-ui.chip :href="$chip('selesai')" :active="$filter === 'selesai'" class="shrink-0">Selesai</x-ui.chip>
        </nav>

        @if ($daftar->isEmpty())
            <x-ui.empty-state icon="message" :title="$kosong[0]" :description="$kosong[1]" />
        @else
            <div class="grid items-start gap-6 lg:grid-cols-[408px_minmax(0,1fr)]">
                {{-- Daftar aspirasi --}}
                <section aria-label="Daftar aspirasi" class="flex-col gap-3 lg:flex lg:gap-0 lg:overflow-hidden lg:rounded-[16px] lg:border lg:border-border lg:bg-surface {{ $dipilih ? 'hidden' : 'flex' }}">
                    <form method="get" action="{{ route('admin.aspirasi.index') }}" role="search" class="lg:p-0">
                        @if ($filter !== 'semua')
                            <input type="hidden" name="status" value="{{ $filter }}">
                        @endif
                        <x-ui.search name="q" label="Cari nama atau isi pesan" placeholder="Cari nama atau isi pesan…" class="lg:[&_input]:rounded-none lg:[&_input]:border-0 lg:[&_input]:border-b" />
                    </form>
                    <ul class="flex flex-col gap-3 lg:gap-0">
                        @foreach ($daftar as $item)
                            @php $sedang = $a && $a['id'] === $item['id'] && ! ($dipilih === null && false); @endphp
                            <li>
                                <a
                                    href="{{ route('admin.aspirasi.index', array_filter(['status' => $filter === 'semua' ? null : $filter, 'q' => request('q'), 'id' => $item['id']])) }}"
                                    @if ($sedang) aria-current="true" @endif
                                    @class([
                                        'flex items-center gap-3 rounded-[12px] border border-border bg-surface p-4 hover:bg-primary-soft lg:rounded-none lg:border-0 lg:border-t lg:border-l-[3px]',
                                        'lg:border-l-primary lg:bg-primary-soft' => $sedang,
                                        'lg:border-l-transparent' => ! $sedang,
                                    ])
                                >
                                    <div class="flex min-w-0 flex-1 flex-col gap-1">
                                        <p class="flex flex-wrap items-center gap-2 type-label text-text">{{ $item['nama'] }} <x-admin.status-badge :status="$item['status']" /></p>
                                        <p class="type-body-sm text-muted">{{ Str::limit($item['pesan'], 72) }}</p>
                                        <p class="type-label-sm text-muted">{{ $waktu($item['diterima']) }}</p>
                                    </div>
                                    <x-ui.icon name="chevron-right" class="size-4 shrink-0 text-muted lg:hidden" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Detail aspirasi: di HP tampil bila dipilih, di layar lebar selalu tampil --}}
                @if ($a)
                    <section aria-labelledby="detail-aspirasi-judul" class="flex-col gap-5 rounded-[16px] border border-border bg-surface p-5 lg:flex lg:p-7 {{ $dipilih ? 'flex' : 'hidden' }}">
                        <a href="{{ route('admin.aspirasi.index', array_filter(['status' => $filter === 'semua' ? null : $filter])) }}" class="inline-flex min-h-11 items-center self-start type-label text-primary hover:underline lg:hidden">← Kembali ke daftar</a>

                        <div class="flex items-start gap-3">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary" aria-hidden="true"><x-ui.icon name="user" class="size-5" /></span>
                            <div class="min-w-0 flex-1">
                                <h2 id="detail-aspirasi-judul" class="type-h3 text-text">{{ $a['nama'] }}</h2>
                                <p class="type-body-sm text-muted">Dikirim {{ $waktu($a['diterima']) }} WIB</p>
                            </div>
                            <x-admin.status-badge :status="$a['status']" />
                        </div>

                        {{-- NIK hanya tampil disamarkan; aspirasi tidak pernah tampil di halaman publik. --}}
                        <p class="flex items-center gap-2 rounded-[10px] bg-background px-3.5 py-3 type-body-sm text-muted">
                            <x-ui.icon name="lock" class="size-4 shrink-0" />
                            NIK: {{ Format::nikSamar($a['nik']) ?? 'tidak diisi pengirim' }}
                        </p>

                        <div class="flex flex-col gap-1.5">
                            <p class="type-label-sm uppercase text-muted">Isi pesan</p>
                            <p class="type-body-lg text-text">{{ $a['pesan'] }}</p>
                        </div>

                        <form method="post" action="{{ route('admin.aspirasi.status', $a['id']) }}" x-data="{ status: {{ Js::from(old('status', $a['status'])) }} }" class="flex flex-col gap-5">
                            @csrf
                            @method('put')

                            <fieldset class="rounded-[12px] bg-background p-4">
                                <legend class="sr-only">Ubah status</legend>
                                <p class="mb-3 type-label text-text" aria-hidden="true">Ubah status</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($statusLabel as $nilai => $nama)
                                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-control border bg-surface px-3.5 type-label text-text has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary"
                                            :class="status === '{{ $nilai }}' ? 'border-2 border-primary' : 'border-border'">
                                            <input type="radio" name="status" value="{{ $nilai }}" x-model="status" class="sr-only">
                                            <span class="size-1.5 rounded-full {{ ['baru' => 'bg-info', 'dibaca' => 'bg-warning', 'selesai' => 'bg-success'][$nilai] }}" aria-hidden="true"></span>
                                            {{ $nama }}
                                            <x-ui.icon name="check" class="size-4 text-primary" x-show="status === '{{ $nilai }}'" />
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <x-ui.field name="catatan" label="Catatan balasan / tindak lanjut" multiline :rows="4" :value="$a['catatan']" placeholder="Tulis catatan tindak lanjut (internal)…" help="Disimpan di riwayat penanganan. Tidak dikirim otomatis ke warga." />

                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <x-ui.button type="submit" variant="ghost">Simpan Catatan</x-ui.button>
                                <x-ui.button type="submit" name="aksi" value="dibaca" variant="secondary">Tandai Sudah Dibaca</x-ui.button>
                                <x-ui.button type="submit" name="aksi" value="selesai" icon="check">Simpan &amp; Tandai Selesai</x-ui.button>
                            </div>
                        </form>

                        <div class="flex flex-col gap-2 border-t border-border pt-4">
                            <p class="type-label-sm uppercase text-muted">Riwayat penanganan</p>
                            <ul class="flex flex-col gap-2">
                                @foreach (array_reverse($a['riwayat']) as $r)
                                    <li class="flex items-start gap-2 type-body-sm text-muted">
                                        <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0" />
                                        <span>{{ $waktu($r['waktu']) }} — {{ $r['teks'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </div>
@endsection
