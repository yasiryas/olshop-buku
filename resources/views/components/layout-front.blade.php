@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'canonical' => null,
    'robots' => 'index, follow',
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <x-seo :title="$title ?? null" :description="$description ?? null" :keywords="$keywords ?? null"
        :image="$image ?? null" :canonical="$canonical ?? null" :robots="$robots" />

    @php
        $storeSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BookStore',
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('assets/logo/icon-book.webp'),
            'image' => asset('assets/logo/logo-wigati.webp'),
            'description' => 'Toko buku online Wigati Buku. Belanja buku favorit Anda dengan mudah, cepat, dan aman.',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $websiteSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('search') . '?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $storeSchema !!}</script>
    <script type="application/ld+json">{!! $websiteSchema !!}</script>

    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
