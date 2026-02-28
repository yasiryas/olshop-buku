<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Wigati Buku</title>
    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('/assets/css/main.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white shadow-md border-b border-gray-200">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a href="{{ route('front.index') }}" class="w-[120px] md:w-[150px]">
                <img src="{{ asset('/assets/logo/logo-wigati.webp') }}" alt="" class="w-full">
            </a>
            <a href="{{ route('front.index') }}" class="text-red-600 font-semibold md:hidden">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col items-center justify-center py-10 px-4">
        <form action="{{ route('login') }}" method="POST"
            class="mx-auto max-w-[345px] w-full p-6 md:p-8 bg-white rounded-2xl md:rounded-3xl shadow-lg">
            @csrf
            <div class="flex flex-col gap-5">
                <p class="text-xl md:text-[22px] font-bold text-center md:text-left">
                    Sign In
                </p>
                <!-- Email Address -->
                <div class="flex flex-col gap-2.5">
                    <label for="email" class="text-base font-semibold">Email Address</label>
                    <input type="email" name="email" id="email__"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Your email address" :value="old('email')" required autofocus
                        autocomplete="username">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Password -->
                <div class="flex flex-col gap-2.5">
                    <label for="password" class="text-base font-semibold">Password</label>
                    <input type="password" name="password" id="password__"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Protect your password" autocomplete="current-password">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="mr-2 rounded text-red-600 focus:ring-red-500">
                        <span class="text-gray-600">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-red-600 hover:underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="inline-flex text-white font-bold text-base bg-red-600 hover:bg-red-700 rounded-full whitespace-nowrap px-[30px] py-3 justify-center items-center transition duration-300">
                    Sign In
                </button>
            </div>
        </form>

        <a href="{{ route('register') }}"
            class="font-semibold text-base mt-[30px] text-gray-600 hover:text-red-600 transition">
            Don't have an account? <span class="text-red-600 underline">Create New Account</span>
        </a>

        <!-- Back to Home -->
        <a href="{{ route('front.index') }}"
            class="hidden md:inline-flex items-center font-semibold text-base mt-4 text-gray-600 hover:text-red-600 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
        </a>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-700 text-white py-4 text-center">
        <p class="text-sm">© {{ date('Y') }} Wigati Buku. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</body>

</html>
