<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center gap-6 bg-background px-4 py-10">
    <main id="konten" class="flex w-full max-w-[440px] flex-col items-center gap-6">
        @yield('content')
    </main>
</body>
</html>
