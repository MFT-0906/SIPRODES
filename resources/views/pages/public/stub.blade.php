@extends('layouts.public')

@section('title', $title)

{{-- Halaman sementara: isi sebenarnya dibuat pada fase 4–6. --}}
@section('content')
    <div class="container-page py-10 md:py-16">
        <h1 class="type-h1 text-text">{{ $title }}</h1>
    </div>
@endsection
