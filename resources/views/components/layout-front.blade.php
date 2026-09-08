<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Wigati Buku' }}</title>
    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="bg-white">
    <x-navbar-front />
    <main class="min-h-screen">
        {{ $slot }}
    </main>
    <x-footer-front />
    <x-toast />
</body>

</html>
