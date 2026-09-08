<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#dc2626">
    <link rel="icon" href="{{ asset('assets/logo/icon-modern.png') }}" type="image/webp">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/assets/pwa/apple-touch-icon.png">

    <title>{{ config('app.name', 'Wigati Buku') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/figtree.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Session Messages -->
        @if (session('success'))
            <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
                id="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg"
                id="error-message">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <script>
        // Auto-hide messages after 3 seconds
        setTimeout(function() {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            if (successMsg) successMsg.style.display = 'none';
            if (errorMsg) errorMsg.style.display = 'none';
        }, 3000);

        // Handle Approve Order form submission
        const approveForm = document.getElementById('approveForm');
        if (approveForm) {
            approveForm.addEventListener('submit', function(e) {
                const btn = document.getElementById('approveBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    btn.classList.add('opacity-50');
                }
            });
        }
    </script>

    <!-- PWA Install Prompt -->
    <div id="pwa-install-banner" class="hidden fixed bottom-4 right-4 z-50 bg-white shadow-lg rounded-xl border border-gray-200 px-5 py-4 flex items-center gap-4 max-w-sm">
        <img src="/assets/pwa/icon-192.png" alt="Wigati Buku" class="w-12 h-12 rounded-lg">
        <div class="flex-1">
            <p class="font-semibold text-gray-800 text-sm">Install Wigati Buku</p>
            <p class="text-gray-500 text-xs">Akses lebih cepat seperti aplikasi native.</p>
        </div>
        <button id="pwa-install-btn"
            class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            Install
        </button>
        <button id="pwa-install-dismiss" class="text-gray-400 hover:text-gray-600 ml-1">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    <script>
        let deferredPrompt;

        const installBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const installDismiss = document.getElementById('pwa-install-dismiss');

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredPrompt = event;
            if (!localStorage.getItem('pwa_install_dismissed')) {
                installBanner.classList.remove('hidden');
            }
        });

        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            installBanner.classList.add('hidden');
        });

        installDismiss.addEventListener('click', () => {
            localStorage.setItem('pwa_install_dismissed', '1');
            installBanner.classList.add('hidden');
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((error) => {
                    console.error('Service worker registration failed:', error);
                });
            });
        }
    </script>

    {{ $script ?? '' }}
</body>

</html>
