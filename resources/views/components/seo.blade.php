@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'canonical' => null,
    'robots' => 'index, follow',
    'type' => 'website',
])

@php
    $siteName = config('app.name', 'Wigati Buku');
    $pageTitle = $title ? $title : $siteName;
    $pageDescription = $description ?? 'Toko buku online Wigati Buku. Belanja buku favorit Anda dengan mudah, cepat, dan aman.';
    $pageKeywords = $keywords ?? 'buku, toko buku, toko buku online, buku terlaris, novel, buku pelajaran';
    $pageUrl = $canonical ? url($canonical) : url()->current();
    $pageImage = $image ? (Str::startsWith($image, ['http://', 'https://', 'data:']) ? $image : asset($image)) : asset('assets/logo/icon-book.webp');
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="{{ $pageKeywords }}">
<link rel="canonical" href="{{ $pageUrl }}">
<meta name="robots" content="{{ $robots }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:image" content="{{ $pageImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">