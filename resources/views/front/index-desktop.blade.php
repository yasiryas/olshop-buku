<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Wigati Buku</title>
    <link rel="shortcut icon" href="{{ asset('/assets/logo/icon-book.webp') }}" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body>
    {{-- navigation --}}
    <nav>
        <div class="container mx-auto flex items-center justify-between p-4">
            <a href="#" class="w-[180px]"><img src="{{ asset('/assets/logo/logo-wigati.webp') }}"
                    alt=""></a>
            <div class="flex space-x-6 font-semibold">
                <a href="#" class="hover:text-red-600">Home</a>
                <a href="#" class="hover:text-red-600">Product</a>
                <a href="#" class="hover:text-red-600">Blog</a>
                <a href="#" class="hover:text-red-600">About Us</a>
                <a href="#" class="hover:text-red-600">Contact</a>
            </div>

            {{-- button --}}
            <div class="relative" x-data="{ open: false }">
                <!-- Tombol -->
                @guest
                    <a href="{{ route('login') }}"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login</a>
                @endguest
                @auth
                    <button @click="open = !open"
                        class="bg-red-600 text-white px-4 py-2 rounded flex items-center font-semibold"> <i
                            class="fas fa-user mr-2"></i>
                        {{ Auth::user()->name }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                @endauth
                <!-- Dropdown -->
                <div x-show="open" @click.outside="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg" x-transition>
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-100 font-semibold">
                        Dashboard
                    </a>
                    @role('buyer')
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-100 font-semibold">
                            Cart
                        </a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-100 font-semibold">
                            Status Pembelian
                        </a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-100 font-semibold">
                            Profile
                        </a>
                    @endrole
                    <x-logout-confirm class="w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold">
                            Logout
                        </x-logout-confirm>
                </div>
            </div>
        </div>
    </nav>

    {{-- herosection --}}
    <section class=" py-20 px-10 space-x-6 container mx-auto flex items-center justify-between">
        <div class="container mx-auto w-3/6">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Ribuan judul buku dari berbagai genre</h4>
            <p class="text-lg mb-8 text-gray-600">fiksi, nonfiksi, bisnis, pendidikan, hingga buku anak. Lengkap.
                Terjangkau. Cepat
                sampai</p>
            <a href="#products" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded"><i
                    class="fa-solid fa-bag-shopping mr-2"></i> Shop
                Now</a>
        </div>
        <div class="container mx-auto w-3/6">
            <img src="{{ asset('/assets/images/herosection.webp') }}" alt="">
        </div>
    </section>

    {{-- Categories Section --}}
    <section class="py-10 bg-gray-100 px-10 space-x-6">
        <div class="container mx-auto">
            <div id="categoriesSlider" class="relative flex justify-center">
                @forelse ($categories as $category)
                    <a href="{{ route('front.product.category', $category->id) }}" class="hover:rotate-6 transition">
                        <div class="inline-flex gap-2.5 items-center py-3 px-3.5 relative bg-white rounded-xl mr-4">
                            <img src="{{ Storage::url($category->icon) }}" class="size-10" alt="">
                            <p class="text-gray-600 font-semibold">
                                {{ $category->name }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <p>Ups, Tidak ada kategori</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    {{-- end categories section --}}
    {{-- product section --}}
    <section class="py-20 bg-gray-50 px-10" id="products">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-10 text-gray-600">Featured Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-center">
                @forelse ($products as $product)
                    <a href="{{ route('front.product.details', $product->slug) }}" class="hover:scale-105 transition">
                        <div class=" bg-white p-6 rounded-lg shadow-lg text-center">
                            <img src=" {{ Storage::url($product->photo) }} " alt="{{ $product->name }}"
                                class="w-full h-48 object-cover mb-4 rounded">
                            <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                            <span class="text-red-600 font-bold mb-2"> Rp {{ number_format($product->price) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <p>Ups, Tidak ada produk</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    {{-- end product section --}}

    {{-- testimonial section --}}
    <section class="py-20 bg-gray-100 px-10">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-10">What Our Customers Say</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Hendry - Jogja</h3>
                    <p class="text-gray-600 mb-4">Recomend banget nih tokonya! jangan lupa checkout ya :)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Dinda - Jakarta</h3>
                    <p class="text-gray-600 mb-4">Belanja di sini nyaman banget! Koleksinya lengkap dan pengirimannya
                        cepat.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Reza - Bandung</h3>
                    <p class="text-gray-600 mb-4">Prosesnya gampang, nggak ribet. Dan yang paling saya suka, ada ulasan
                        asli dari pembaca lain. Jadi bisa pilih buku dengan percaya diri.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Andy - Surabaya</h3>
                    <p class="text-gray-600 mb-4">Koleksinya lengkap banget, dari novel sampai buku motivasi. Harganya
                        juga nggak bikin kantong jebol.</p>
                </div>
            </div>
        </div>
    </section>
    {{-- Cta section --}}
    <section class="py-20 px-10">
        <div class="container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Bayangkan… di ujung jari kamu ada ribuan
                buku
                inspiratif,
                novel seru,
                panduan bisnis, buku anak, hingga koleksi langka. Semua bisa kamu dapatkan tanpa harus keluar rumah,
                dengan harga bersahabat dan pelayanan terbaik.</p>
            <div class="flex justify-center">
                <a href="#" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded">Shop
                    Now</a>
            </div>
        </div>
    </section>

    {{-- blog section --}}
    <section class="py-20 px-10 bg-gray-50">
        <div class="container mx-auto ">
            <h2 class="text-3xl font-bold text-center mb-10">Latest Article Posts</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($articles as $article)
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                        <img src=" {{ Storage::url($article->featured_image) }}  " alt="{{ $article->title }}"
                            class="w-full h-48 object-cover mb-4 rounded">
                        <h3 class="text-xl font-semibold mb-2">{{ $article->title }}</h3>
                        <p class="text-gray-600 mb-4"> {{ Str::limit(strip_tags($article->content), 100, '...') }}</p>
                        <a href="#"" class="text-red-600 hover:underline">Read
                            More</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- footer --}}
    <footer class="bg-gray-600 text-white py-4">
        <div class="container mx-auto text-center">
            <p class="text-sm">© {{ date('Y') }} Olshop Buku. All rights reserved.</p>

        </div>
    </footer>
</body>

</html>
