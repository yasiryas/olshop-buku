<nav x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = window.scrollY > 10"
    :class="scrolled ? 'shadow-md border-b border-gray-200' : 'shadow-none border-transparent'"
    class="bg-white sticky top-0 shadow z-50">
    <div class="container mx-auto flex items-center justify-between p-4">
        <a href="#" class="w-[120px] md:w-[150px]">
            <img src="{{ asset('/assets/logo/icon-modern.png') }}" alt="" class="w-full">
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex space-x-6 font-semibold">
            <a href="{{ route('front.index') }}"
                class="relative pb-1 {{ request()->routeIs('front.index') ? 'text-red-600 after:w-full' : 'text-gray-800 after:w-0' }}
              hover:text-red-600 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-red-600 after:transition-all after:duration-300 hover:after:w-full">
                Home
            </a>

            <a href="{{ route('front.product') }}"
                class="relative pb-1 {{ request()->routeIs('front.product') || request()->routeIs('front.product.details') || request()->routeIs('front.product.category') || request()->routeIs('front.search') ? 'text-red-600 after:w-full' : 'text-gray-800 after:w-0' }}
              hover:text-red-600 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-red-600 after:transition-all after:duration-300 hover:after:w-full">
                Product
            </a>

            <a href="{{ route('front.blog') }}"
                class="relative pb-1 {{ request()->routeIs('front.blog') || request()->routeIs('front.article.details') ? 'text-red-600 after:w-full' : 'text-gray-800 after:w-0' }}
              hover:text-red-600 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-red-600 after:transition-all after:duration-300 hover:after:w-full">
                Blog
            </a>

            <a href="{{ route('front.about') }}"
                class="relative pb-1 {{ request()->routeIs('front.about') ? 'text-red-600 after:w-full' : 'text-gray-800 after:w-0' }}
              hover:text-red-600 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-red-600 after:transition-all after:duration-300 hover:after:w-full">
                About Us
            </a>

            <a href="{{ route('front.contact') }}"
                class="relative pb-1 {{ request()->routeIs('front.contact') ? 'text-red-600 after:w-full' : 'text-gray-800 after:w-0' }}
              hover:text-red-600 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-red-600 after:transition-all after:duration-300 hover:after:w-full">
                Contact
            </a>
        </div>

        <!-- Desktop Auth Buttons -->
        <div class="hidden md:block relative" x-data="{ open: false }">
            @guest
                <a href="{{ route('login') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            @endguest

            @auth
                <button @click="open = !open"
                    class="bg-red-600 text-white px-4 py-2 rounded flex items-center font-semibold">
                    <i class="fas fa-user mr-2"></i> {{ Auth::user()->name }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg z-50">
                    @role('admin|penulis|owner')
                        <a href="{{ route('dashboard') }}"
                            class="block px-4 py-2 hover:bg-gray-100 font-semibold">Dashboard</a>
                    @endrole

                    @role('buyer')
                        <a href="{{ route('carts.index') }}" class="block px-4 py-2 hover:bg-gray-100 font-semibold">Cart</a>
                        <a href="{{ route('product_transactions.index') }}"
                            class="block px-4 py-2 hover:bg-gray-100 font-semibold">Status Pembelian</a>
                    @endrole

                    <x-logout-confirm class="w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold">
                        Logout
                    </x-logout-confirm>
                </div>
            @endauth
        </div>

        <!-- Mobile Hamburger Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-800 p-2">
            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden bg-white border-t border-gray-200">
        <div class="flex flex-col p-4 space-y-3">
            <a href="{{ route('front.index') }}"
                class="block py-2 px-4 {{ request()->routeIs('front.index') ? 'text-red-600 bg-red-50' : 'text-gray-800' }} rounded">
                <i class="fas fa-home mr-2"></i> Home
            </a>
            <a href="{{ route('front.product') }}"
                class="block py-2 px-4 {{ request()->routeIs('front.product') || request()->routeIs('front.product.details') || request()->routeIs('front.product.category') ? 'text-red-600 bg-red-50' : 'text-gray-800' }} rounded">
                <i class="fas fa-book mr-2"></i> Product
            </a>
            <a href="{{ route('front.blog') }}"
                class="block py-2 px-4 {{ request()->routeIs('front.blog') || request()->routeIs('front.article.details') ? 'text-red-600 bg-red-50' : 'text-gray-800' }} rounded">
                <i class="fas fa-blog mr-2"></i> Blog
            </a>
            <a href="{{ route('front.about') }}"
                class="block py-2 px-4 {{ request()->routeIs('front.about') ? 'text-red-600 bg-red-50' : 'text-gray-800' }} rounded">
                <i class="fas fa-info-circle mr-2"></i> About Us
            </a>
            <a href="{{ route('front.contact') }}"
                class="block py-2 px-4 {{ request()->routeIs('front.contact') ? 'text-red-600 bg-red-50' : 'text-gray-800' }} rounded">
                <i class="fas fa-envelope mr-2"></i> Contact
            </a>
        </div>

        <!-- Mobile Auth Section -->
        <div class="border-t border-gray-200 p-4">
            @guest
                <a href="{{ route('login') }}"
                    class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            @endguest

            @auth
                <div class="space-y-2">
                    <p class="text-sm text-gray-600 font-semibold px-4">Logged in as: {{ Auth::user()->name }}</p>
                    @role('admin|penulis|owner')
                        <a href="{{ route('dashboard') }}" class="block py-2 px-4 text-gray-800 hover:bg-gray-100 rounded">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    @endrole
                    @role('buyer')
                        <a href="{{ route('carts.index') }}" class="block py-2 px-4 text-gray-800 hover:bg-gray-100 rounded">
                            <i class="fas fa-shopping-cart mr-2"></i> Cart
                        </a>
                        <a href="{{ route('product_transactions.index') }}"
                            class="block py-2 px-4 text-gray-800 hover:bg-gray-100 rounded">
                            <i class="fas fa-receipt mr-2"></i> Status Pembelian
                        </a>
                    @endrole
                    <x-logout-confirm
                        class="w-full text-left block py-2 px-4 text-red-600 hover:bg-red-50 rounded font-semibold">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </x-logout-confirm>
                </div>
            @endauth
        </div>
    </div>
</nav>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
