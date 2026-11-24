@extends('layouts.auth')

@section('title', 'Masuk ke Dashboard')

@section('content')
    <div class="flex w-full flex-col gap-5 rounded-[20px] border border-border bg-surface p-6 shadow-[0_16px_40px_rgb(15_51_33/0.08)] sm:p-10">
        {{-- Di desktop identitas sudah tampil di panel kiri. --}}
        <x-public.logo tone="terang" class="self-start lg:hidden" />

        <div class="flex flex-col gap-1.5">
            <h1 class="type-h2 text-text">Masuk ke Dashboard</h1>
            <p class="type-body-sm text-muted">Khusus perangkat desa yang memiliki akun.</p>
        </div>

        @if (session('status_masuk'))
            <x-ui.alert tone="sukses">{{ session('status_masuk') }}</x-ui.alert>
        @endif

        {{-- Pesan gagal bersifat umum: tidak menyebut apakah email atau kata sandi yang salah. --}}
        @if (session('gagal_masuk'))
            <x-ui.alert tone="bahaya">{{ session('gagal_masuk') }}</x-ui.alert>
        @endif

        @if (\App\Support\GoogleLogin::aktif())
            {{-- Tautan biasa (GET), bukan form: pengalihan ke Google tidak terhalang aturan form-action CSP. --}}
            {{-- Logo "G" resmi diunduh dari panduan merek Google (developers.google.com/identity/branding-guidelines)
                 dan disimpan sebagai public/images/google-g.svg. Tanpa berkas itu tombol tampil tanpa logo. --}}
            <a
                href="{{ route('admin.login.google') }}"
                class="flex min-h-12 w-full items-center justify-center gap-3 rounded-control border border-border bg-surface px-4 shadow-[0_1px_2px_rgb(15_51_33/0.08)] type-label text-text transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
            >
                @if (is_file(public_path('images/google-g.svg')))
                    <img src="{{ asset('images/google-g.svg') }}" alt="" loading="lazy" class="size-5 shrink-0">
                @endif
                Masuk dengan Google
            </a>
            <p class="-mt-2 type-body-sm text-muted">Pakai akun Google dengan email yang didaftarkan Super Admin.</p>

            <div class="flex items-center gap-3 type-label-sm text-muted" aria-hidden="true">
                <span class="h-px flex-1 bg-border"></span>atau dengan kata sandi<span class="h-px flex-1 bg-border"></span>
            </div>
        @endif

        <form method="post" action="{{ route('admin.login.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf
            <x-ui.field
                name="login" label="Email atau username" required
                placeholder="contoh: nama@gmail.com" autocomplete="username" autofocus
            />
            <x-ui.field
                name="password" type="password" label="Kata sandi" required
                autocomplete="current-password"
                :help="session('gagal_masuk') ? 'Demi keamanan, akun dikunci sementara setelah beberapa kali gagal masuk.' : null"
            />

            <div class="flex items-center justify-between gap-3">
                <x-ui.checkbox name="ingat" label="Ingat saya" />
                <a href="{{ route('admin.password.request') }}" class="inline-flex min-h-11 items-center rounded-control type-label text-primary hover:underline">Lupa kata sandi?</a>
            </div>

            <x-ui.button type="submit" icon="arrow-right" class="w-full">Masuk</x-ui.button>
        </form>

        <p class="flex items-center gap-1.5 type-label-sm text-muted">
            <x-ui.icon name="lock" class="size-3.5 shrink-0" />
            Koneksi aman · tautan atur ulang dikirim ke email Anda
        </p>
    </div>

    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-control type-label text-primary hover:underline lg:hidden">← Kembali ke situs publik</a>
@endsection
