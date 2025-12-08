<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Wigati Buku' }}</title>
    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

</head>

<body class="bg-white">
    <x-navbar-front />
    <main class="min-h-screen">
        {{ $slot }}
    </main>
    <x-footer-front />
</body>

</html>
