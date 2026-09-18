<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('front.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:-my-px sm:ms-10 sm:flex sm:space-x-10">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @role('owner|admin')
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('Laporan') }}
                        </x-nav-link>
                    @endrole

                    @role('owner|admin')
                        <x-nav-dropdown label="Katalog"
                            :active="request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('stocks.*')">
                            <x-dropdown-link :href="route('admin.products.index')"
                                :class="request()->routeIs('admin.products.*') ? 'text-gray-900 bg-gray-50' : ''">
                                {{ __('Kelola Produk') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.categories.index')"
                                :class="request()->routeIs('admin.categories.*') ? 'text-gray-900 bg-gray-50' : ''">
                                {{ __('Kelola Kategori') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('stocks.index')"
                                :class="request()->routeIs('stocks.*') ? 'text-gray-900 bg-gray-50' : ''">
                                {{ __('Kelola Logistik') }}
                            </x-dropdown-link>
                        </x-nav-dropdown>
                    @endrole

                    @role('owner|admin')
                        <x-nav-dropdown label="Transaksi"
                            :active="request()->routeIs('product_transactions.*') || request()->routeIs('admin.returns.*')">
                            <x-dropdown-link :href="route('product_transactions.index')"
                                :class="request()->routeIs('product_transactions.*') ? 'text-gray-900 bg-gray-50' : ''">
                                {{ __('Pesanan') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.returns.index')"
                                :class="request()->routeIs('admin.returns.*') ? 'text-gray-900 bg-gray-50' : ''">
                                {{ __('Retur') }}
                            </x-dropdown-link>
                        </x-nav-dropdown>
                    @endrole

                    @role('owner|penulis')
                        <x-nav-link :href="route('admin.articles.index')" :active="request()->routeIs('admin.articles.*')">
                            {{ __('Kelola Artikel') }}
                        </x-nav-link>
                    @endrole

                    @role('owner')
                        <x-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                            {{ __('Kelola Staff') }}
                        </x-nav-link>
                    @endrole

                    @role('admin')
                        <x-nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')">
                            {{ __('Pelanggan') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="me-4">
                    <x-notification-bell />
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @role('owner')
                            <x-dropdown-link :href="route('admin.settings.edit')">
                                {{ __('Pengaturan Toko') }}
                            </x-dropdown-link>
                        @endrole
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <x-logout-confirm :class="'block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out'">
                            {{ __('Keluar') }}
                        </x-logout-confirm>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('front.index')" :active="request()->routeIs('front.index')">
                {{ __('Toko') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @role('owner|admin')
                <x-responsive-nav-link :href="route('admin.products.index')">
                    {{ __('Kelola Produk') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.categories.index')">
                    {{ __('Kelola Kategori') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('stocks.index')">
                    {{ __('Kelola Logistik') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('product_transactions.index')">
                    {{ __('Pesanan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.returns.index')">
                    {{ __('Retur') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')">
                    {{ __('Laporan') }}
                </x-responsive-nav-link>
            @endrole
            @role('owner|penulis')
                <x-responsive-nav-link :href="route('admin.articles.index')">
                    {{ __('Kelola Artikel') }}
                </x-responsive-nav-link>
            @endrole
            @role('owner')
                <x-responsive-nav-link :href="route('admin.staff.index')">
                    {{ __('Kelola Staff') }}
                </x-responsive-nav-link>
            @endrole
            @role('admin')
                <x-responsive-nav-link :href="route('admin.customers.index')">
                    {{ __('Pelanggan') }}
                </x-responsive-nav-link>
            @endrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <x-logout-confirm :class="'block w-full text-left px-4 py-2 text-base leading-6 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out'">
                            {{ __('Keluar') }}
                        </x-logout-confirm>
            </div>
        </div>
    </div>
</nav>
