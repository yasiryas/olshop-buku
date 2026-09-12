<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak | Wigati Buku</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-6">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto mb-6 h-16 w-16 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636M12 3v10" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-2">Akses Ditolak</h1>
            <p class="mb-6 text-gray-600">
                {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('front.index') }}"
                    class="px-4 py-2 rounded-full text-sm font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                    Kembali ke Toko
                </a>
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="px-4 py-2 rounded-full text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Ke Dashboard
                    </a>
                @endauth
            </div>
        </div>
    </div>
</body>
</html>