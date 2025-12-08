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

                @forelse ($my_carts as $cart)
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
                        <p>Delivery (Promo)</p>
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

                <div class="grid grid-cols-2 gap-4">

                    {{-- Manual --}}
                    <div x-data="{ payment: '' }" class="space-y-4">
                        <label
                            class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                            <input type="radio" name="payment_method" value="manual" class="absolute opacity-0"
                                x-model="payment">
                            <img src="{{ asset('/assets/svgs/ic-receipt-text-filled.svg') }}">
                            <p class="font-semibold">Manual</p>
                        </label>

                        <div x-show="payment === 'manual'" x-transition
                            class="bg-gray-100 rounded-lg p-4 border border-gray-300">
                            <p class="font-semibold text-lg">Nomor Rekening:</p>
                            <p class="font-bold text-xl">12345678</p>
                            <p class="text-gray-600">a.n Wigati Buku</p>
                        </div>
                    </div>

                    {{-- Credit (disabled) --}}
                    <label
                        class="relative rounded-xl bg-gray-50 p-3 flex gap-2 items-center opacity-50 cursor-not-allowed">
                        <input type="radio" disabled>
                        <img src="{{ asset('/assets/svgs/ic-card-filled.svg') }}">
                        <p class="font-semibold">Credits</p>
                    </label>

                </div>
            </div>

            {{-- DELIVERY --}}
            <div class="bg-white rounded-2xl p-6 shadow">
                <h3 class="text-lg font-bold mb-4">Delivery to</h3>

                <form action="{{ route('product_transactions.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

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
                        <input type="text" name="city" value="{{ old('city') }}"
                            class="w-full border rounded-lg px-4 py-2 @error('city') border-red-500 @enderror">

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

                </form>
            </div>

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
            if (elDel) elDel.textContent = 'Rp 0';

            const tax = subTotal * 0.11;
            const insurance = subTotal * 0.23;

            if (elTax) elTax.textContent = 'Rp ' + tax.toLocaleString('id');
            if (elIns) elIns.textContent = 'Rp ' + insurance.toLocaleString('id');

            const grand = subTotal + tax + insurance;
            if (elGrand) elGrand.textContent = 'Rp ' + grand.toLocaleString('id');
        }

        // run after full load (defensive: wait a tick to let Blade-rendered attrs settle)
        document.addEventListener("alpine:initialized", () => {
            setTimeout(() => {
                calculatePrice();
            }, 50);
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
