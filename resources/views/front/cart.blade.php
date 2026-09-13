<x-layout-front title="Carts - Wigati Buku" robots="noindex, nofollow">

    {{-- Hero Section --}}
    <section class="py-16 px-10 container mx-auto text-center">
        <h4 class="text-4xl font-bold mb-4 text-gray-700">Carts</h4>
        <p class="text-lg mb-6 text-gray-600">Checkout sekarang juga!</p>
    </section>

    {{-- MAIN WRAPPER --}}
    <div class="container mx-auto grid grid-cols-1 lg:grid-cols-5 gap-8 px-4 lg:px-10 mb-10">

        {{-- LEFT: CART ITEMS --}}
        <div class="lg:col-span-2 bg-gray-50 rounded-2xl p-6 shadow">
            <h2 class="text-xl font-bold mb-4">Items</h2>

            <div class="space-y-4">

                @forelse ($carts as $cart)
                    <div class="flex gap-4 bg-white rounded-xl p-4 shadow-sm" data-cart-item>

                        {{-- LEFT: IMAGE --}}
                        <div>
                            <img src="{{ Storage::url($cart->product->photo) }}"
                                class="w-[90px] h-[90px] object-contain rounded-lg border">
                        </div>

                        {{-- CENTER: PRODUCT INFO + QTY --}}
                        <div class="flex-1 flex flex-col justify-between">

                            {{-- Product Name --}}
                            <a href="{{ route('front.product.details', $cart->product->slug) }}"
                                class="text-base font-semibold block truncate hover:text-blue-500 w-[200px]">
                                {{ $cart->product->name }}
                            </a>

                            {{-- Price --}}
                            <p class="text-sm text-gray-500 product-price" data-price="{{ $cart->product->price }}"
                                data-qty="{{ $cart->quantity }}">
                                Rp {{ number_format($cart->product->price) }}
                            </p>

                            {{-- QTY ALPINE --}}
                            <div x-data="cartQty({
                                id: '{{ $cart->id }}',
                                quantity: {{ $cart->quantity ?? 1 }},
                                max: {{ $cart->product->stock ?? 0 }},
                                updateUrl: '{{ route('carts.update', $cart) }}',
                                token: '{{ csrf_token() }}'
                            })" class="flex items-center gap-2 mt-2">

                                {{-- MINUS --}}
                                <button type="button" @click="decrease"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300">
                                    −
                                </button>

                                {{-- GANTI INPUT DENGAN DISPLAY --}}
                                <div class="w-16 text-center border rounded-lg py-1 bg-gray-50">
                                    <span x-text="quantity"></span>
                                </div>

                                {{-- PLUS --}}
                                <button type="button" @click="increase"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300">
                                    +
                                </button>

                            </div>

                        </div>

                        {{-- RIGHT: DELETE --}}
                        <div class="flex items-start">
                            <x-confirm-modal action="{{ route('carts.destroy', $cart) }}" method="DELETE"
                                title="Hapus produk ini?" message="Produk akan dikeluarkan dari keranjang Anda."
                                confirmText="Hapus" icon="fa-trash-can" class="hover:bg-red-100 p-2 rounded-full">
                                <img src="{{ asset('/assets/svgs/ic-trash-can-filled.svg') }}" class="w-6 h-6">
                            </x-confirm-modal>
                        </div>

                    </div>
                @empty
                    <p class="text-gray-500 text-center">Ups, belum ada produk yang ditambahkan!</p>
                @endforelse

            </div>
        </div>

        {{-- RIGHT: CHECKOUT FLOW --}}
        <div class="lg:col-span-3 space-y-6">
            <form action="{{ route('product_transactions.store') }}" method="POST" enctype="multipart/form-data"
                x-data="checkoutFlow({
                    agenWeb: {{ $agenWebConfigured ? 'true' : 'false' }},
                    rateUrl: '{{ route('carts.rates') }}',
                    locUrl: '{{ route('carts.locations') }}',
                    token: '{{ csrf_token() }}',
                    savedAddresses: {{ Js::from($userAddresses->map->only(['id', 'label', 'recipient_name', 'phone', 'address', 'province', 'city', 'district', 'postal_code', 'is_default'])->values()) }}
                })"
                class="space-y-6">
                @csrf

                {{-- DETAIL PAYMENT --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-4">Details Payment</h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between">
                            <p>Sub Total</p>
                            <p id="checkoutSubTotal"></p>
                        </li>
                        <li class="flex justify-between">
                            <p>PPN 11%</p>
                            <p id="checkoutTax"></p>
                        </li>
                        <li class="flex justify-between">
                            <p>Insurance 23%</p>
                            <p id="checkoutInsurance"></p>
                        </li>
                        <li class="flex justify-between">
                            <p>Ongkir</p>
                            <p class="text-indigo-700" id="checkoutDeliveryFee"></p>
                        </li>
                        <li class="flex justify-between font-bold text-lg border-t border-gray-200 pt-3">
                            <p>Grand Total</p>
                            <p class="text-primary" id="checkoutGrandTotal"></p>
                        </li>
                    </ul>
                </div>

                {{-- ALAMAT PENGIRIMAN --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <div class="flex items-center gap-2 mb-1">
                        <img src="{{ asset('/assets/svgs/ic-location.svg') }}" class="w-5 h-5 mt-1">
                        <h3 class="text-lg font-bold">Alamat Pengiriman</h3>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Kecamatan harus dipilih dari saran; nama, HP, dan alamat bebas ketik.</p>

                    {{-- ADDRESS BOOK --}}
                    <template x-if="savedAddresses.length && !addNew">
                        <div class="space-y-2 mb-4">
                            <template x-for="addr in savedAddresses" :key="addr.id">
                                <label
                                    class="relative flex items-start gap-3 rounded-xl border p-3 cursor-pointer bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                                    <input type="radio" name="saved_address_pick" :value="addr.id"
                                        :checked="savedId === addr.id"
                                        class="absolute opacity-0" @change="fillFromSaved(addr)">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-sm" x-text="addr.label"></p>
                                            <span x-show="addr.is_default"
                                                class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">Utama</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5" x-text="addr.recipient_name + ' · ' + addr.phone"></p>
                                        <p class="text-xs text-gray-500 mt-1 truncate" x-text="addr.address"></p>
                                        <p class="text-xs text-gray-400" x-text="addr.district + ', ' + addr.city + ' · ' + addr.province"></p>
                                    </div>
                                </label>
                            </template>
                            <button type="button" @click="startNew"
                                class="w-full rounded-xl border border-dashed border-gray-300 py-2.5 text-sm font-semibold text-blue-700 hover:border-blue-400 hover:bg-blue-50">
                                + Tambah Alamat Baru
                            </button>
                        </div>
                    </template>
                    <template x-if="addNew">
                        <button type="button" @click="cancelNew"
                            class="mb-3 text-xs font-semibold text-blue-700 underline">← Pilih ulang dari alamat tersimpan</button>
                    </template>

                    {{-- RECIPIENT + PHONE --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-semibold text-sm">Nama Penerima</label>
                            <input type="text" name="recipient_name" x-model="recipientName"
                                :value="recipientName"
                                placeholder="Nama penerima"
                                class="w-full border rounded-lg px-4 py-2 mt-1 text-sm" required>
                        </div>
                        <div>
                            <label class="font-semibold text-sm">No. HP / WhatsApp</label>
                            <input type="text" name="phone_number" x-model="phone"
                                :value="phone"
                                placeholder="08xxxxxxxxxx"
                                class="w-full border rounded-lg px-4 py-2 mt-1 text-sm" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="font-semibold text-sm">Alamat Lengkap (jalan, RT/RW, patokan)</label>
                        <textarea name="address" x-model="fullAddress" :value="fullAddress" rows="3"
                            placeholder="Contoh: Jl. Merdeka No. 12, RT 03/RW 05, dekat Pasar Segar"
                            class="w-full border rounded-lg px-4 py-2 mt-1 text-sm resize-none" required></textarea>
                    </div>

                    @if ($agenWebConfigured)
                        {{-- AUTOCOMPLETE KECAMATAN --}}
                        <div class="mt-4 relative">
                            <label class="font-semibold text-sm">Kecamatan Pengiriman <span class="text-red-500">*</span></label>
                            <div class="relative mt-1">
                                <input type="text" x-model="districtQuery"
                                    @input.debounce.400ms="searchLocations()"
                                    @focus="districtQuery.length >= 2 && suggestions.length ? locOpen = true : null"
                                    placeholder="Ketik nama kecamatan, lalu pilih dari saran…"
                                    class="w-full border rounded-lg px-4 py-2 pr-10 text-sm">
                                <i x-show="searchingLoc" class="fas fa-spinner fa-spin absolute right-3 top-3 text-gray-400"></i>
                            </div>

                            <div x-show="locOpen" x-cloak x-transition
                                class="absolute z-30 w-full mt-1 bg-white rounded-xl border border-gray-200 shadow-lg max-h-64 overflow-auto">
                                <template x-if="searchingLoc">
                                    <p class="px-4 py-3 text-sm text-gray-500">Mencari…</p>
                                </template>
                                <template x-if="!searchingLoc && !suggestions.length">
                                    <p class="px-4 py-3 text-sm text-gray-500">Tidak ada kecamatan cocok. Ketik minimal 2 huruf.</p>
                                </template>
                                <template x-for="s in suggestions" :key="s.postal_code + s.district">
                                    <button type="button" @click="pickSuggestion(s)"
                                        class="w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-50 last:border-0">
                                        <p class="text-sm font-medium" x-text="s.district"></p>
                                        <p class="text-xs text-gray-500" x-text="s.city + ', ' + s.province + ' · ' + s.postal_code"></p>
                                    </button>
                                </template>
                            </div>

                            {{-- LOCATION RESOLVED PILL --}}
                            <div x-show="addressLocationReady" x-cloak x-transition
                                class="mt-2 rounded-lg bg-green-50 border border-green-200 px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs text-green-700 font-semibold"
                                        x-text="district + ' · ' + city + ', ' + province"></p>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <label class="font-semibold text-xs">Kode Pos</label>
                                    <input type="text" name="post_code" x-model="postal" readonly
                                        class="w-28 border rounded-lg px-3 py-1.5 text-sm bg-green-50">
                                </div>
                                <button type="button" @click="districtQuery=''; postal=''; locOpen=true"
                                    class="mt-1 text-[11px] font-semibold text-green-700 underline">Ganti lokasi</button>
                            </div>
                        </div>

                        {{-- SAVE TO ADDRESS BOOK --}}
                        <div class="mt-4 rounded-xl bg-gray-50 p-3 flex items-center justify-between gap-3">
                            <label class="text-sm cursor-pointer">
                                <input type="checkbox" x-model="saveAddress" class="mr-2 accent-blue-600">
                                Simpan untuk checkout berikutnya
                            </label>
                            <input type="text" x-model="addressLabel" placeholder="Label: Rumah / Kantor"
                                class="w-36 border rounded-lg px-3 py-1.5 text-xs">
                        </div>
                        <input type="hidden" name="province" :value="province">
                        <input type="hidden" name="city" :value="city">
                        <input type="hidden" name="district" :value="district">
                        <input type="hidden" name="agenweb_city_id" :value="cityId">
                        <input type="hidden" name="saved_address_id" :value="addNew ? '' : savedId">
                        <input type="hidden" name="save_address" value="1" :disabled="!saveAddress">
                        <input type="hidden" name="address_label" :value="addressLabel">
                    @endif
                </div>

                {{-- METODE PENGIRIMAN --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-truck text-blue-500"></i>
                        <h3 class="text-lg font-bold">Metode Pengiriman</h3>
                    </div>

                    @if ($agenWebConfigured)
                        <p x-show="!addressLocationReady" class="text-xs text-gray-400 mt-2">
                            Pilih kecamatan tujuan dulu agar ongkir terhitung otomatis.
                        </p>

                        <p x-show="addressLocationReady && loading" class="text-sm text-gray-500 mt-2 flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i> Menghitung ongkir…
                        </p>

                        <p x-show="error" x-text="error" class="text-red-500 text-xs mt-2"></p>

                        <template x-if="addressLocationReady && !loading && rates.length && !listOpen && selectedRate">
                            <div x-cloak x-transition class="mt-3">
                                <div class="rounded-xl border-2 border-indigo-500 bg-indigo-50 p-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-check-circle text-indigo-600"></i>
                                        <div>
                                            <p class="font-semibold text-sm"
                                                x-text="selectedRate.courier + ' \u2013 ' + selectedRate.service"></p>
                                            <p class="text-sm text-gray-600"
                                                x-text="(selectedRate.cost > 0 ? 'Rp ' + Number(selectedRate.cost).toLocaleString('id') : 'Gratis') + ' \u00b7 ' + selectedRate.eta"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="listOpen = true"
                                        class="shrink-0 text-xs font-semibold text-indigo-700 underline">Ganti ongkir</button>
                                </div>
                            </div>
                        </template>

                        <div x-cloak x-show="rates.length && listOpen" class="mt-3 space-y-2">
                            <template x-for="rate in rates" :key="rate.rate_id">
                                <label
                                    class="relative rounded-lg bg-gray-50 p-2.5 flex gap-2.5 items-center cursor-pointer border has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="shipping_method" :value="rate.rate_id"
                                        :checked="selectedRateId === rate.rate_id"
                                        :data-cost="rate.cost" :data-eta="rate.eta"
                                        class="absolute opacity-0" @change="selectRate(rate)">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm truncate"
                                            x-text="rate.courier + ' \u2013 ' + rate.service"></p>
                                        <p class="text-xs text-gray-500"
                                            x-text="(rate.cost > 0 ? 'Rp ' + Number(rate.cost).toLocaleString('id') : 'Gratis') + ' \u00b7 ' + rate.eta"></p>
                                    </div>
                                    <template x-if="selectedRateId === rate.rate_id">
                                        <i class="fas fa-check-circle text-lg text-indigo-600 shrink-0"></i>
                                    </template>
                                </label>
                            </template>
                        </div>

                        <div id="manual-rates" x-show="manualShown" x-cloak class="mt-3 space-y-2">
                            <p class="text-xs text-gray-500 font-semibold">Tarif manual (fallback):</p>
                            @forelse ($shippingRates as $sr)
                                <label
                                    class="relative rounded-lg bg-gray-50 p-2.5 flex gap-2.5 items-center cursor-pointer border has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="shipping_method" value="{{ $sr['code'] }}"
                                        data-cost="{{ $sr['cost'] }}" data-eta="{{ $sr['eta'] }}" class="absolute opacity-0"
                                        @change="calculatePrice()">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm truncate">{{ $sr['courier'] }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $sr['cost'] > 0 ? 'Rp ' . number_format($sr['cost']) : 'Gratis' }} · {{ $sr['eta'] }}
                                        </p>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Belum ada kurir. Hubungi admin.</p>
                            @endforelse
                        </div>
                    @else
                        <div class="space-y-3 mt-3">
                            <div>
                                <label class="font-semibold text-sm">Kota</label>
                                <select name="city" id="citySelect"
                                    class="w-full border rounded-lg px-4 py-2 mt-1 text-sm @error('city') border-red-500 @enderror">
                                    @forelse ($shippingZones as $zone)
                                        <option value="{{ $zone['city'] }}"
                                            data-costs="{{ json_encode($zone['costs']) }}" {{ old('city', $user->city ?? $shippingZones[0]['city'] ?? '') === $zone['city'] ? 'selected' : '' }}>
                                            {{ $zone['city'] }}
                                        </option>
                                    @empty
                                        <option value="">Belum ada zona pengiriman</option>
                                    @endforelse
                                </select>
                            </div>

                            <div>
                                <label class="font-semibold text-sm">Kode Pos</label>
                                <input type="number" name="post_code" value="{{ old('post_code', $user->post_code ?? '') }}"
                                    class="w-full border rounded-lg px-4 py-2 mt-1 text-sm @error('post_code') border-red-500 @enderror">
                            </div>

                            <div>
                                <label class="font-semibold text-sm">Kecamatan (opsional)</label>
                                <input type="text" name="district" value="{{ old('district', '') }}"
                                    class="w-full border rounded-lg px-4 py-2 mt-1 text-sm">
                                <input type="hidden" name="province" value="">
                            </div>

                            @forelse ($shippingRates as $sr)
                                <label
                                    class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-indigo-500">
                                    <input type="radio" name="shipping_method" value="{{ $sr['code'] }}"
                                        data-cost="{{ $sr['cost'] }}" data-eta="{{ $sr['eta'] }}" class="absolute opacity-0"
                                        @change="calculatePrice()" {{ $loop->first ? 'checked' : '' }}>
                                    <div>
                                        <p class="font-semibold">{{ $sr['courier'] }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $sr['cost'] > 0 ? 'Rp ' . number_format($sr['cost']) : 'Gratis' }} · {{ $sr['eta'] }}
                                        </p>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Belum ada kurir. Hubungi admin.</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                {{-- PAYMENT METHOD --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-4">Payment Method</h3>
                    <div class="space-y-3">
                        @forelse ($paymentMethods as $pm)
                            <label
                                class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                                <input type="radio" name="payment_method" value="{{ $pm['code'] }}"
                                    data-acc="{{ $pm['acc_number'] }}" data-name="{{ $pm['acc_name'] }}"
                                    data-pm-name="{{ $pm['name'] }}"
                                    class="absolute opacity-0" @change="onPaymentChange($event)"
                                    {{ $loop->first ? 'checked' : '' }}>
                                <img src="{{ asset('/assets/svgs/ic-receipt-text-filled.svg') }}">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold pm-name">{{ $pm['name'] }}</p>
                                    <p class="text-sm text-gray-500">
                                        No. Rek {{ $pm['acc_number'] }} · a.n {{ $pm['acc_name'] }}
                                    </p>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada metode pembayaran. Hubungi admin.</p>
                        @endforelse

                        <div x-show="payment" x-cloak x-transition class="bg-gray-100 rounded-lg p-4 border border-gray-300">
                            <p class="font-semibold text-lg" x-text="payment?.label"></p>
                            <p class="font-bold text-xl" x-text="'Nomor Rekening: ' + (payment?.number ?? '-')"></p>
                            <p class="text-gray-600" x-text="'a.n ' + (payment?.name ?? '-')"></p>
                        </div>
                    </div>
                </div>

                {{-- KONFIRMASI --}}
                <div x-data="{ showConfirm: false }" @keydown.escape.window="showConfirm = false"
                    class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-3">Konfirmasi</h3>
                    <div>
                        <label class="font-semibold text-sm">Bukti Transfer <span class="text-gray-400 font-normal">(wajib sebelum pesanan diproses)</span></label>
                        <input type="file" name="proof" @change="onProofChange($event)" accept="image/png,image/jpeg"
                            class="w-full border rounded-lg px-4 py-2 mt-1 text-sm @error('proof') border-red-500 @enderror">
                        <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pesanan bisa dibuat <strong>sebelum</strong> unggah bukti, namun
                            <strong>tidak akan diproses</strong> sampai bukti transfer diunggah dan diverifikasi.
                            Bukti bisa diunggah nanti lewat halaman detail pesanan.
                        </p>
                    </div>

                    @if ($agenWebConfigured)
                        <ul class="text-xs text-gray-500 mt-3 space-y-1">
                            <li x-show="!addressLocationReady"><i class="fas fa-circle text-[5px] align-middle mr-1"></i>Lengkapi alamat &amp; pilih kecamatan</li>
                            <li x-show="addressLocationReady && !shippingReady"><i class="fas fa-circle text-[5px] align-middle mr-1"></i>Pilih kurir</li>
                            <li x-show="!payment"><i class="fas fa-circle text-[5px] align-middle mr-1"></i>Pilih metode pembayaran</li>
                            <li x-show="!proofBytes"><i class="fas fa-circle text-[5px] align-middle mr-1"></i>Unggah bukti transfer (opsional saat checkout — wajib sebelum pesanan diproses)</li>
                        </ul>
                    @endif

                    <button type="button" @click="canConfirm && (showConfirm = true)"
                        :disabled="!canConfirm"
                        :class="canConfirm ? 'bg-indigo-700 hover:bg-indigo-900 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full text-white font-bold py-3 rounded-full mt-3 transition">
                        Confirm Pesanan
                    </button>

                    <div x-show="showConfirm" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center">
                        <div class="absolute inset-0 bg-black bg-opacity-50" @click="showConfirm = false"></div>
                        <div x-transition class="relative bg-white rounded-2xl shadow-lg p-6 w-80 text-center z-10">
                            <button type="button" @click="showConfirm = false" aria-label="Tutup"
                                class="absolute top-3 right-3 p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div class="text-indigo-600 text-4xl mb-3">
                                <i class="fas fa-bag-shopping"></i>
                            </div>
                            <p class="font-bold text-lg text-gray-800">Buat pesanan ini?</p>
                            <p class="text-sm text-gray-500 mt-1">Pastikan alamat, kurir, dan pembayaran sudah benar.</p>
                            <div class="flex justify-center gap-3 mt-5">
                                <button type="button" @click="showConfirm = false"
                                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300">
                                    Batal
                                </button>
                                <button type="button" @click="$el.closest('form').submit()"
                                    class="px-5 py-2 bg-indigo-600 text-white rounded-full font-semibold hover:bg-indigo-700">
                                    Ya, Konfirmasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- error message --}}
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let firstErrorField = document.querySelector('.border-red-500');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstErrorField.focus();
                }
            });
        </script>
    @endif

    <!-- SCRIPTS PERBAIKAN (REPLACE ALL EXISTING SCRIPT) -->
    <script>
        // Safe parse helpers
        const asFloat = (v) => {
            const n = parseFloat(v);
            return Number.isFinite(n) ? n : 0;
        };
        const asInt = (v) => {
            const n = parseInt(v);
            return Number.isFinite(n) ? n : 0;
        };

        /**
         * HITUNG ULANG SUMMARY PAYMENT
         * berdasarkan price × qty -- sangat defensive (no NaN)
         */
        function calculatePrice() {
            let subTotal = 0;
            document.querySelectorAll('.product-price').forEach(item => {
                const price = asFloat(item.dataset.price ?? item.getAttribute('data-price'));
                const qty = asInt(item.dataset.qty ?? item.getAttribute('data-qty'));
                subTotal += price * qty;
            });

            // update DOM (pakai 0 kalau tidak ada)
            const elSub = document.getElementById('checkoutSubTotal');
            const elTax = document.getElementById('checkoutTax');
            const elIns = document.getElementById('checkoutInsurance');
            const elDel = document.getElementById('checkoutDeliveryFee');
            const elGrand = document.getElementById('checkoutGrandTotal');

            if (elSub) elSub.textContent = 'Rp ' + subTotal.toLocaleString('id');

            const checkedShip = document.querySelector('input[name="shipping_method"]:checked');
            const shippingCost = checkedShip ? asFloat(checkedShip.dataset.cost) : 0;
            if (elDel) elDel.textContent = shippingCost > 0 ? 'Rp ' + shippingCost.toLocaleString('id') : 'Gratis';

            const tax = subTotal * 0.11;
            const insurance = subTotal * 0.23;

            if (elTax) elTax.textContent = 'Rp ' + tax.toLocaleString('id');
            if (elIns) elIns.textContent = 'Rp ' + insurance.toLocaleString('id');

            const grand = subTotal + tax + insurance + shippingCost;
            if (elGrand) elGrand.textContent = 'Rp ' + grand.toLocaleString('id');
        }

        // run after full load (defensive: wait a tick to let Blade-rendered attrs settle)
        document.addEventListener("alpine:initialized", () => {
            setTimeout(() => {
                setZoneShippingCosts();
                calculatePrice();
            }, 50);
        });

        /**
         * TERAPKAN TARIF PER KOTA (manual zones).
         */
        function setZoneShippingCosts() {
            const zoneSelect = document.getElementById('citySelect');
            if (!zoneSelect || !zoneSelect.selectedIndex) return;

            let costs = {};
            try {
                costs = JSON.parse(zoneSelect.options[zoneSelect.selectedIndex].dataset.costs || '{}');
            } catch (e) {
                costs = {};
            }

            document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
                if (typeof costs[radio.value] === 'number' && costs[radio.value] >= 0) {
                    radio.dataset.cost = String(costs[radio.value]);
                }
            });

            calculatePrice();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const zoneSelect = document.getElementById('citySelect');
            if (zoneSelect) {
                zoneSelect.addEventListener('change', setZoneShippingCosts);
            }
        });

        /**
         * CHECKOUT FLOW (Alpine):
         * - Address book (alamat tersimpan) + alamat baru
         * - Autocomplete kecamatan (wajib dari saran) → kode pos otomatis
         * - Ongkir real-time dihitung otomatis, kurir terpilih otomatis termurah
         * - Tombol Konfirmasi baru aktif saat alamat + kurir + pembayaran + bukti lengkap
         */
        document.addEventListener('alpine:init', () => {
            const DRAFT_KEY = 'wigati_checkout_draft';

            Alpine.data('checkoutFlow', (config) => ({
                agenWeb: config.agenWeb,
                rateUrl: config.rateUrl,
                locUrl: config.locUrl,
                token: config.token,
                savedAddresses: config.savedAddresses || [],

                savedId: '',
                addNew: false,
                recipientName: '',
                phone: '',
                fullAddress: '',
                district: '',
                city: '',
                province: '',
                postal: '',
                cityId: '',

                districtQuery: '',
                suggestions: [],
                locOpen: false,
                searchingLoc: false,

                loading: false,
                error: '',
                rates: [],
                selectedRateId: '',
                listOpen: true,
                manualShown: false,

                payment: null,
                proofBytes: 0,

                saveAddress: true,
                addressLabel: '',

                get addressLocationReady() {
                    return !!(this.province && this.city && this.district && this.postal);
                },

                get shippingReady() {
                    return !!document.querySelector('input[name="shipping_method"]:checked');
                },

                get selectedRate() {
                    return this.rates.find((r) => r.rate_id === this.selectedRateId) || null;
                },

                get canConfirm() {
                    return this.addressLocationReady && this.shippingReady && !!this.payment;
                },

                init() {
                    this.restoreDraft();
                    if (!this.district) {
                        if (this.savedAddresses.length) {
                            const def = this.savedAddresses.find(a => a.is_default) || this.savedAddresses[0];
                            this.fillFromSaved(def);
                        }
                    } else if (!this.rates.length && this.addressLocationReady) {
                        this.fetchRates();
                    }

                    this.syncDefaultPayment();
                    this.$nextTick(() => this.syncRestoredRadios());

                    setInterval(() => this.saveDraft(), 1000);
                },

                syncDefaultPayment() {
                    if (this.payment) return;
                    const checked = document.querySelector('input[name="payment_method"]:checked');
                    if (!checked) return;
                    this.payment = {
                        code: checked.value,
                        label: checked.dataset.pmName || '',
                        number: checked.dataset.acc || '',
                        name: checked.dataset.name || ''
                    };
                },

                restoreDraft() {
                    let d = null;
                    try {
                        d = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null');
                    } catch (e) {
                        d = null;
                    }
                    if (!d || !d.district) return;

                    this.savedId = d.savedId || '';
                    this.addNew = d.addNew || false;
                    this.recipientName = d.recipientName || '';
                    this.phone = d.phone || '';
                    this.fullAddress = d.fullAddress || '';
                    this.district = d.district || '';
                    this.city = d.city || '';
                    this.province = d.province || '';
                    this.postal = d.postal || '';
                    this.cityId = d.cityId || '';
                    this.districtQuery = d.districtQuery || '';
                    this.payment = d.payment || null;
                    this.saveAddress = d.saveAddress !== false;
                    this.addressLabel = d.addressLabel || '';
                    this.rates = Array.isArray(d.rates) ? d.rates : [];
                    this.selectedRateId = d.selectedRateId || '';
                },

                saveDraft() {
                    const hasAddress = this.district || this.recipientName || this.fullAddress;
                    if (!hasAddress) return;

                    try {
                        localStorage.setItem(DRAFT_KEY, JSON.stringify({
                            savedId: this.savedId,
                            addNew: this.addNew,
                            recipientName: this.recipientName,
                            phone: this.phone,
                            fullAddress: this.fullAddress,
                            district: this.district,
                            city: this.city,
                            province: this.province,
                            postal: this.postal,
                            cityId: this.cityId,
                            districtQuery: this.districtQuery,
                            payment: this.payment,
                            saveAddress: this.saveAddress,
                            addressLabel: this.addressLabel,
                            rates: this.rates,
                            selectedRateId: this.selectedRateId,
                        }));
                    } catch (e) {
                        /* localStorage tidak tersedia: abaikan */
                    }
                },

                syncRestoredRadios() {
                    if (this.payment && this.payment.code) {
                        const radio = document.querySelector('input[name="payment_method"][value="' + this.payment.code + '"]');
                        if (radio) radio.checked = true;
                    }
                    if (this.selectedRateId) {
                        const rate = document.querySelector('input[name="shipping_method"][value="' + this.selectedRateId + '"]');
                        if (rate) rate.checked = true;
                    }
                    calculatePrice();
                },

                fillFromSaved(addr) {
                    this.savedId = String(addr.id);
                    this.addNew = false;
                    this.recipientName = addr.recipient_name || '';
                    this.phone = addr.phone || '';
                    this.fullAddress = addr.address || '';
                    this.district = addr.district || '';
                    this.city = addr.city || '';
                    this.province = addr.province || '';
                    this.postal = addr.postal_code || '';
                    this.districtQuery = this.district ? this.district + ', ' + this.city : '';
                    this.locOpen = false;
                    if (this.addressLocationReady) this.fetchRates();
                },

                startNew() {
                    this.savedId = '';
                    this.addNew = true;
                    this.recipientName = '';
                    this.phone = '';
                    this.fullAddress = '';
                    this.district = '';
                    this.city = '';
                    this.province = '';
                    this.postal = '';
                    this.cityId = '';
                    this.districtQuery = '';
                    this.suggestions = [];
                    this.rates = [];
                    this.selectedRateId = '';
                    this.listOpen = false;
                    this.manualShown = false;
                    this.$nextTick(() => {
                        const first = document.querySelector('input[name="recipient_name"]');
                        if (first) first.focus();
                    });
                },

                cancelNew() {
                    this.addNew = false;
                    if (this.savedAddresses.length) {
                        this.fillFromSaved(this.savedAddresses[0]);
                    } else {
                        this.addNew = true;
                    }
                },

                searchLocations() {
                    this.locOpen = true;
                    const q = (this.districtQuery || '').trim();
                    if (q.length < 2) {
                        this.suggestions = [];
                        this.locOpen = false;
                        return;
                    }
                    this.searchingLoc = true;
                    fetch(this.locUrl + '?q=' + encodeURIComponent(q), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.suggestions = Array.isArray(data) ? data : [];
                            if (this.suggestions.length) this.locOpen = true;
                        })
                        .catch(() => { this.suggestions = []; })
                        .finally(() => { this.searchingLoc = false; });
                },

                pickSuggestion(s) {
                    this.district = s.district;
                    this.city = s.city;
                    this.province = s.province;
                    this.postal = s.postal_code;
                    this.cityId = s.city_id || '';
                    this.districtQuery = s.district + ', ' + s.city;
                    this.locOpen = false;
                    this.refreshShipping();
                },

                refreshShipping() {
                    if (!this.addressLocationReady) {
                        this.rates = [];
                        this.selectedRateId = '';
                        this.listOpen = false;
                        this.manualShown = false;
                        calculatePrice();
                        return;
                    }
                    this.fetchRates();
                },

                fetchRates() {
                    this.loading = true;
                    this.error = '';
                    this.rates = [];
                    this.selectedRateId = '';
                    const params = new URLSearchParams();
                    if (this.cityId) params.set('city_id', this.cityId);
                    if (this.postal) params.set('post_code', this.postal);
                    const query = params.toString();
                    fetch(this.rateUrl + (query ? '?' + query : ''), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.rates = Array.isArray(data) ? data : [];
                            this.manualShown = false;
                            if (this.rates.length) {
                                this.error = '';
                                this.selectedRateId = this.rates[0].rate_id;
                                this.listOpen = false;
                                this.$nextTick(() => calculatePrice());
                            } else {
                                this.fallback();
                            }
                        })
                        .catch(() => this.fallback())
                        .finally(() => { this.loading = false; });
                },

                fallback() {
                    this.error = '';
                    this.selectedRateId = '';
                    this.manualShown = true;
                    this.listOpen = false;
                    const firstManual = document.querySelector('#manual-rates input[name="shipping_method"]');
                    if (firstManual) {
                        const checked = document.querySelector('#manual-rates input[name="shipping_method"]:checked');
                        if (!checked) firstManual.checked = true;
                    }
                    calculatePrice();
                },

                selectRate(rate) {
                    this.selectedRateId = rate.rate_id;
                    this.listOpen = false;
                    calculatePrice();
                },

                onPaymentChange(evt) {
                    const t = evt.target;
                    this.payment = {
                        code: t.value,
                        label: t.dataset.pmName || '',
                        number: t.dataset.acc || '',
                        name: t.dataset.name || ''
                    };
                },

                onProofChange(evt) {
                    const file = evt.target.files && evt.target.files[0];
                    this.proofBytes = file ? file.size : 0;
                }
            }));
        });

        /**
         * Alpine component (cartQty)
         * Defensive: check priceEl existence, update dataset safely, and recalc
         */
        document.addEventListener("alpine:init", () => {
            Alpine.data("cartQty", ({
                id,
                quantity,
                max,
                updateUrl,
                token
            }) => ({
                // coerce to numbers
                quantity: Number(quantity ?? 0),
                max: Number(max ?? 0),
                isUpdating: false,

                init() {
                    // --- FIX PENTING (qty awal selalu 0 tanpa ini)
                    let wrapper = this.$root.closest('[data-cart-item]');
                    const priceEl = wrapper ? wrapper.querySelector('.product-price') : null;

                    if (priceEl) {
                        priceEl.dataset.qty = this.quantity;
                    }

                    calculatePrice();
                },

                increase() {
                    if (this.quantity < this.max) {
                        this.quantity++;
                        this.updateServer();
                    }
                },

                decrease() {
                    if (this.quantity > 1) {
                        this.quantity--;
                        this.updateServer();
                    }
                },

                updateServer() {
                    if (this.isUpdating) return;
                    this.isUpdating = true;

                    fetch(updateUrl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": token,
                                "X-HTTP-Method-Override": "PUT",
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest"
                            },
                            body: JSON.stringify({
                                quantity: this.quantity
                            })
                        })
                        .then(res => res.json())
                        .then(() => {
                            // Temukan product-price element yang berada di dalam blok item cart yang sama
                            // Kita naik ke ancestor yang jelas: .flex.gap-4.bg-white (wrapper item)
                            let wrapper = this.$root.closest('[data-cart-item]');

                            const priceEl = wrapper ? wrapper.querySelector('.product-price') :
                                null;

                            if (priceEl) {
                                priceEl.dataset.qty = this.quantity;
                            } else {
                                // kalau tidak ditemukan, coba cari nearest .product-price di document (defensive)
                                const anyPrice = document.querySelector('.product-price');
                                if (anyPrice) anyPrice.dataset.qty = asInt(anyPrice.dataset.qty) ||
                                    0;
                            }

                            // recalc
                            calculatePrice();
                        })
                        .catch(err => {
                            console.error("Error update cart qty:", err);
                        })
                        .finally(() => {
                            this.isUpdating = false;
                        });
                }
            }));
        });

        /**
         * OPTIONAL: Watch for DOM changes (add/remove cart rows) and recalc automatically.
         */
        const cartContainer = document.querySelector('.lg\\:col-span-2 .space-y-4') || document.querySelector('.space-y-4');
        if (cartContainer) {
            const mo = new MutationObserver((mutations) => {
                calculatePrice();
            });
            mo.observe(cartContainer, {
                childList: true,
                subtree: true
            });
        }
    </script>

    <style>
        input::placeholder,
        textarea::placeholder,
        select::placeholder {
            color: #b6bfcd;
            font-style: italic;
            opacity: 1;
        }
    </style>

</x-layout-front>