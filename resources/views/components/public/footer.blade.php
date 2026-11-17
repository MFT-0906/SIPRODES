{{--
    Kaki halaman publik yang ramping: hanya hak cipta dan tautan kebijakan.
    Tidak memuat data kontak agar tidak menampilkan kontak yang belum diverifikasi desa.
--}}
<footer class="on-dark bg-primary-dark text-surface">
    <div class="container-page flex flex-col gap-1 py-5 md:flex-row md:items-center md:justify-between md:gap-6">
        <p class="type-body-sm text-surface/85">&copy; {{ now()->year }} {{ config('desa.wilayah.sebutan') }} {{ config('desa.wilayah.nama') }}. Seluruh hak dilindungi.</p>
        <a href="{{ route('kebijakan-privasi') }}" class="inline-flex min-h-11 items-center type-label text-surface underline underline-offset-4 hover:text-accent">Kebijakan Privasi</a>
    </div>
</footer>
