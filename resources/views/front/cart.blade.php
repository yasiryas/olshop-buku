<x-layout-front title="Carts - Wigati Buku">

    {{-- Hero Section --}}
    <section class="py-16 px-10 container mx-auto text-center">
        <h4 class="text-4xl font-bold mb-4 text-gray-700">Carts</h4>
        <p class="text-lg mb-6 text-gray-600">Checkout sekarang juga!</p>
    </section>

    {{-- Error Handling --}}
    @error('error')
        <div class="invalid-feedback text-red-500 text-center mb-4">
            {{ $message }}
        </div>
    @enderror

    {{-- MAIN WRAPPER --}}
    <div class="container mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 px-4 lg:px-10 mb-10">

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
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-lg hover:bg-gray-300">
                                    −
                                </button>

                                {{-- GANTI INPUT DENGAN DISPLAY --}}
                                <div class="w-16 text-center border rounded-lg py-1 bg-gray-50">
                                    <span x-text="quantity"></span>
                                </div>

                                {{-- PLUS --}}
                                <button type="button" @click="increase"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-lg hover:bg-gray-300">
                                    +
                                </button>

                            </div>

                        </div>

                        {{-- RIGHT: DELETE --}}
                        <div class="flex items-start">
                            <form action="{{ route('carts.destroy', $cart) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:bg-red-100 p-2 rounded-full">
                                    <img src="{{ asset('/assets/svgs/ic-trash-can-filled.svg') }}" class="w-6 h-6">
                                </button>
                            </form>
                        </div>

                    </div>
                @empty
                    <p class="text-gray-500 text-center">Ups, belum ada produk yang ditambahkan!</p>
                @endforelse

            </div>
        </div>

        {{-- RIGHT: PAYMENT + DELIVERY --}}
        <div class="space-y-6">
            <form action="{{ route('product_transactions.store') }}" method="POST" enctype="multipart/form-data"
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
                            <p id="checkoutDeliveryFee"></p>
                        </li>
                        <li class="flex justify-between font-bold text-lg">
                            <p>Grand Total</p>
                            <p class="text-primary" id="checkoutGrandTotal"></p>
                        </li>
                    </ul>
                </div>

                {{-- PAYMENT METHOD --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-4">Payment Method</h3>
                    <div x-data="{ selected: null }" class="space-y-3">
                        @forelse ($paymentMethods as $pm)
                            <label
                                class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                                <input type="radio" name="payment_method" value="{{ $pm['code'] }}"
                                    data-acc="{{ $pm['acc_number'] }}" data-name="{{ $pm['acc_name'] }}"
                                    class="absolute opacity-0" @change="selected = { number: $event.target.dataset.acc, name: $event.target.dataset.name, label: $event.target.closest('label').querySelector('.pm-name').textContent.trim() }"
                                    {{ $loop->first ? 'checked' : '' }}>
                                <img src="{{ asset('/assets/svgs/ic-receipt-text-filled.svg') }}">
                                <p class="font-semibold pm-name">{{ $pm['name'] }}</p>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada metode pembayaran. Hubungi admin.</p>
                        @endforelse

                        <div x-show="selected" x-transition class="bg-gray-100 rounded-lg p-4 border border-gray-300">
                            <p class="font-semibold text-lg" x-text="selected?.label"></p>
                            <p class="font-bold text-xl" x-text="'Nomor Rekening: ' + (selected?.number ?? '-')"></p>
                            <p class="text-gray-600" x-text="'a.n ' + (selected?.name ?? '-')"></p>
                        </div>
                    </div>
                </div>

                {{-- SHIPPING METHOD --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-4">Metode Pengiriman</h3>

                    @if ($biteshipConfigured)
                        <div x-data="biteshipShipping()" x-init="$nextTick(() => autoFetch())">
                            <p class="text-xs text-gray-500 mb-3">Masukkan kode pos tujuan lalu cek ongkir real-time (Biteship).</p>
                            <div class="flex gap-2">
                                <input type="number" x-model="postCode" placeholder="Kode pos tujuan"
                                    class="w-full border rounded-lg px-4 py-2 text-sm">
                                <button type="button" @click="fetchRates()" :disabled="loading"
                                    class="shrink-0 bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm hover:bg-indigo-900">
                                    <span x-show="!loading">Cek Ongkir</span>
                                    <span x-show="loading">...</span>
                                </button>
                            </div>
                            <p x-show="error" x-text="error" class="text-red-500 text-xs mt-2"></p>
                            <div id="biteship-results" class="space-y-3 mt-3">
                                <template x-for="rate in rates" :key="rate.rate_id">
                                    <label
                                        class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                                        <input type="radio" name="shipping_method" :value="rate.rate_id"
                                            :data-cost="rate.cost" :data-eta="rate.eta" class="absolute opacity-0"
                                            @change="calculatePrice()">
                                        <div>
                                            <p class="font-semibold" x-text="rate.courier + ' - ' + rate.service"></p>
                                            <p class="text-sm text-gray-500"
                                                x-text="(rate.cost > 0 ? 'Rp ' + Number(rate.cost).toLocaleString('id') : 'Gratis') + ' · ' + rate.eta"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div id="manual-rates" class="space-y-3 mt-4 hidden">
                            <p class="text-xs text-gray-500 font-semibold">Tarif manual (fallback):</p>
                            @forelse ($shippingRates as $sr)
                                <label
                                    class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                                    <input type="radio" name="shipping_method" value="{{ $sr['code'] }}"
                                        data-cost="{{ $sr['cost'] }}" data-eta="{{ $sr['eta'] }}" class="absolute opacity-0"
                                        @change="calculatePrice()">
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
                    @else
                        <div class="space-y-3">
                            @forelse ($shippingRates as $sr)
                                <label
                                    class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
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

                {{-- DELIVERY --}}
                <div class="bg-white rounded-2xl p-6 shadow">
                    <h3 class="text-lg font-bold mb-4">Delivery to</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                class="w-full border rounded-lg px-4 py-2 @error('address') border-red-500 @enderror">

                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

<div>
                            <label class="font-semibold">City</label>
                            @if ($biteshipConfigured)
                                <input type="text" name="city" value="{{ old('city') }}"
                                    class="w-full border rounded-lg px-4 py-2 @error('city') border-red-500 @enderror">
                            @else
                                <select name="city" id="citySelect"
                                    class="w-full border rounded-lg px-4 py-2 @error('city') border-red-500 @enderror">
                                    @forelse ($shippingZones as $zone)
                                        <option value="{{ $zone['city'] }}"
                                            data-costs="{{ json_encode($zone['costs']) }}" {{ old('city', $shippingZones[0]['city'] ?? '') === $zone['city'] ? 'selected' : '' }}>
                                            {{ $zone['city'] }}
                                        </option>
                                    @empty
                                        <option value="">Belum ada zona pengiriman</option>
                                    @endforelse
                                </select>
                            @endif

                            @error('city')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-semibold">Post Code</label>
                            <input type="number" name="post_code" value="{{ old('post_code') }}"
                                class="w-full border rounded-lg px-4 py-2 @error('post_code') border-red-500 @enderror">

                            @error('post_code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-semibold">Phone Number</label>
                            <input type="number" name="phone_number" value="{{ old('phone_number') }}"
                                class="w-full border rounded-lg px-4 py-2 @error('phone_number') border-red-500 @enderror">

                            @error('phone_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-semibold">Add. Notes</label>
                            <textarea name="notes" class="w-full border rounded-lg px-4 py-2"></textarea>
                        </div>

                        <div>
                            <label class="font-semibold">Proof of Payment</label>
                            <input type="file" name="proof"
                                class="w-full border rounded-lg px-4 py-2 @error('proof') border-red-500 @enderror">

                            @error('proof')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button class="w-full bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-800">
                            Confirm
                        </button>
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
         * TERAPKAN TARIF PER KOTA.
         * Baca zona terpilih, sesuaikan data-cost tiap kurir, lalu hitung ulang.
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

        @if ($biteshipConfigured)
            /**
             * ONGKIR Biteship: cek tarif real-time lalu pasang radio kurir.
             * Bila API gagal / kosong, tampilkan tarif manual (fallback).
             */
            document.addEventListener('alpine:init', () => {
                Alpine.data('biteshipShipping', () => ({
                    postCode: '',
                    loading: false,
                    error: '',
                    rates: [],
                    autoFetch() {
                        const pc = document.querySelector('input[name="post_code"]');
                        if (pc && pc.value.trim()) {
                            this.postCode = pc.value.trim();
                            this.fetchRates();
                        }
                    },
                    fetchRates() {
                        const pc = (this.postCode || document.querySelector('input[name="post_code"]')?.value || '').trim();
                        if (!pc) {
                            this.error = 'Isi kode pos tujuan dulu.';
                            return;
                        }
                        this.loading = true;
                        this.error = '';
                        this.rates = [];
                        document.querySelectorAll('input[name="shipping_method"]').forEach(r => r.checked = false);
                        fetch('{{ route('carts.rates') }}?post_code=' + encodeURIComponent(pc), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }).then(res => res.json()).then(data => {
                            this.rates = Array.isArray(data) ? data : [];
                            const manual = document.getElementById('manual-rates');
                            if (manual) manual.classList.add('hidden');
                            if (this.rates.length) this.error = '';
                            this.$nextTick(() => {
                                const first = document.querySelector('#biteship-results input[name="shipping_method"]:not([checked])');
                                if (this.rates.length && first) {
                                    first.checked = true;
                                    calculatePrice();
                                }
                            });
                            if (!this.rates.length) this.fallback();
                        }).catch(() => {
                            this.fallback();
                        }).finally(() => {
                            this.loading = false;
                        });
                    },
                    fallback() {
                        this.error = 'Ongkir API tidak tersedia, memakai tarif manual.';
                        const manual = document.getElementById('manual-rates');
                        if (manual) manual.classList.remove('hidden');
                        const zoneSelect = document.getElementById('citySelect');
                        if (zoneSelect) setZoneShippingCosts();
                        calculatePrice();
                    }
                }));
            });
        @endif


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
         * Ini berguna kalau kamu menambahkan produk via JS/AJAX.
         */
        const cartContainer = document.querySelector('.lg\\:col-span-2 .space-y-4') || document.querySelector('.space-y-4');
        if (cartContainer) {
            const mo = new MutationObserver((mutations) => {
                // recalc saat ada perubahan children
                calculatePrice();
            });
            mo.observe(cartContainer, {
                childList: true,
                subtree: true
            });
        }
    </script>



</x-layout-front>
