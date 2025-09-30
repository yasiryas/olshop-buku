<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Wigati Buku</title>
    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('/assets/css/main.css') }}">
</head>

<body>
    <img src="{{ asset('/assets/logo/logo.webp') }}" class="mb-[53px] w-[100px]" alt="">
    <div class="flex flex-col items-center justify-center py-10 h-screen">
        <form action="{{ route('login') }}" method="POST"
            class="mx-auto max-w-[345px] w-full p-6 bg-white rounded-3xl mt-auto" id="deliveryForm">
            @csrf
            <div class="flex flex-col gap-5">
                <p class="text-[22px] font-bold">
                    Sign In
                </p>
                <!-- Email Address -->
                <div class="flex flex-col gap-2.5">
                    <label for="email" class="text-base font-semibold">Email Address</label>
                    <input type="email" name="email" id="email__"
                        style="background-image: url('/assets/svgs/ic-email.svg');"
                        class="form-input bg-[url('/public/assets/svgs/ic-email.svg')]" placeholder="Your email address"
                        :value="old('email')" required autofocus autocomplete="username">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Password -->
                <div class="flex flex-col gap-2.5">
                    <label for="password" class="text-base font-semibold">Password</label>
                    <input type="password" name="password" id="password__"
                        style="background-image: url('/assets/svgs/ic-lock.svg');"
                        class="form-input bg-[url('/public/assets/svgs/ic-lock.svg')]"
                        placeholder="Protect your password" autocomplete="current-password">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex text-white font-bold text-base bg-primary rounded-full whitespace-nowrap px-[30px] py-3 justify-center items-center">
                    Sign In
                </button>
            </div>
        </form>
        <a href="{{ route('register') }}" class="font-semibold text-base mt-[30px] underline">
            Create New Account
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</body>

</html>
