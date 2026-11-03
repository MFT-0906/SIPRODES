@extends('layouts.auth')

@section('title', 'Buat kata sandi baru')

@section('content')
    <div class="flex w-full flex-col gap-[18px] rounded-[20px] border border-border bg-surface p-6 sm:p-10">
        <x-public.logo tone="terang" class="self-start" />

        <h1 class="type-h2 text-text">Buat kata sandi baru</h1>
        <p class="type-body-sm text-muted">Gunakan minimal {{ config('desa.auth.kata_sandi_min') }} karakter dan jangan sama dengan email Anda.</p>

        <form method="post" action="{{ route('admin.password.update') }}" class="flex flex-col gap-[18px]" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-ui.field name="email" type="email" label="Email" required :value="$email" autocomplete="username" />
            <x-ui.field name="password" type="password" label="Kata sandi baru" required autocomplete="new-password" />
            <x-ui.field name="password_confirmation" type="password" label="Ulangi kata sandi baru" required autocomplete="new-password" />
            <x-ui.button type="submit" icon="lock" class="w-full">Simpan kata sandi</x-ui.button>
        </form>

        <a href="{{ route('admin.login') }}" class="inline-flex min-h-11 items-center self-start rounded-control type-label text-primary hover:underline">← Kembali ke halaman masuk</a>
    </div>
@endsection
