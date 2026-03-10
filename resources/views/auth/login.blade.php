@extends('layouts.guest')

@section('content')
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
                    placeholder="Your email address" :value="old('email')" required autofocus autocomplete="username">
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

    <a href="{{ route('register') }}" class="font-semibold text-base mt-[30px] text-gray-600 hover:text-red-600 transition">
        Don't have an account? <span class="text-red-600 underline">Create New Account</span>
    </a>

    <!-- Back to Home -->
    <a href="{{ route('front.index') }}"
        class="hidden md:inline-flex items-center font-semibold text-base mt-4 text-gray-600 hover:text-red-600 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Home
    </a>
@endsection
