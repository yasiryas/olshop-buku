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
    @if ($errors->any())
        <div
            class="max-w-6xl mx-auto bg-red-100 border border-red-400 text-red-700 px-6 py-4 my-4 rounded-lg shadow mb-6">
            <div class="font-bold text-lg mb-2">Oops! Ada beberapa masalah:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Wrapper: Desktop 2 Kolom --}}
    <div class="container mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 px-4 lg:px-10 mb-10">
        {{-- Kiri: Cart Items --}}
        <div class="lg:col-span-2 bg-gray-50 rounded-2xl p-6 shadow">
            <h2 class="text-xl font-bold mb-4">Items</h2>
            <div class="space-y-4">
                @forelse ($my_carts as $cart)
                    <!-- Product Item -->
                    <div class="flex gap-4 bg-white rounded-xl p-4 items-center shadow-sm">
                        <img src="{{ Storage::url($cart->product->photo) }}"
                            class="w-[90px] h-[90px] object-contain rounded-lg border" alt="">
                        <div class="flex justify-between w-full">
                            <div>
                                <a href="{{ route('front.product.details', $cart->product->slug) }}"
                                    class="text-base font-semibold block w-[200px] truncate hover:text-blue-500">
                                    {{ $cart->product->name }}
                                </a>
                                <p class="text-sm text-gray-500 product-price" data-price="{{ $cart->product->price }}">
                                    Rp {{ number_format($cart->product->price) }}
                                </p>
                            </div>
                            <form action="{{ route('carts.destroy', $cart) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:bg-red-100 p-2 rounded-full">
                                    <img src="{{ asset('/assets/svgs/ic-trash-can-filled.svg') }}" class="w-6 h-6"
                                        alt="Delete">
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center">Ups, belum ada produk yang ditambahkan!</p>
                @endforelse
            </div>
        </div>

        {{-- Kanan: Detail Payment & Delivery --}}
        <div class="space-y-6">
            {{-- Detail Payment --}}
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

            {{-- Payment Method --}}
            <div class="bg-white rounded-2xl p-6 shadow">
                <h3 class="text-lg font-bold mb-4">Payment Method</h3>
                <div class="grid grid-cols-2 gap-4">
                    {{-- <label
                        class="relative rounded-xl bg-gray-50 flex gap-2 p-3 items-center has-[:checked]:ring-2 has-[:checked]:ring-blue-500">
                        <input type="radio" name="payment_method" id="manualMethod" class="absolute opacity-0">
                        <img src="{{ asset('/assets/svgs/ic-receipt-text-filled.svg') }}" alt="">
                        <p class="text-base font-semibold">Manual</p>
                    </label> --}}
                    <div x-data="{ payment: '' }" class="space-y-4">
                        <label
                            class="relative rounded-xl bg-gray-50 flex gap-2 p-3 items-center has-[:checked]:ring-2 has-[:checked]:ring-blue-500 cursor-pointer">
                            <input type="radio" name="payment_method" value="manual" id="manualMethod"
                                class="absolute opacity-0" x-model="payment">
                            <img src="{{ asset('/assets/svgs/ic-receipt-text-filled.svg') }}" alt="">
                            <p class="text-base font-semibold">Manual</p>
                        </label>

                        <div x-show="payment === 'manual'" x-transition
                            class="mt-4 p-4 bg-gray-100 rounded-lg border border-gray-300">
                            <p class="text-lg font-semibold">Nomor Rekening:</p>
                            <p class="text-xl font-bold text-gray-800">12345678</p>
                            <p class="text-gray-600">a.n Wigati Buku</p>
                        </div>
                    </div>

                    <label
                        class="relative rounded-xl bg-gray-50 flex gap-2 p-3 items-center has-[:checked]:ring-2 has-[:checked]:ring-blue-500 disabled">
                        <input type="radio" name="payment_method" id="creditMethod" class="absolute opacity-0"
                            disabled>
                        <img src="{{ asset('/assets/svgs/ic-card-filled.svg') }}" alt="">
                        <p class="text-base font-semibold">Credits</p>
                    </label>
                </div>
            </div>

            {{-- Delivery Form --}}
            <div class="bg-white rounded-2xl p-6 shadow">
                <h3 class="text-lg font-bold mb-4">Delivery to</h3>
                <form action="{{ route('product_transactions.store') }}" enctype="multipart/form-data" method="POST"
                    class="space-y-4">
                    @csrf
                    @method('POST')

                    <div>
                        <label class="block mb-1 font-semibold">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="w-full border rounded-lg px-4 py-2" placeholder="Your address">
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold">City</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                            class="w-full border rounded-lg px-4 py-2" placeholder="Your city">
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold">Post Code</label>
                        <input type="number" name="post_code" value="{{ old('post_code') }}"
                            class="w-full border rounded-lg px-4 py-2" placeholder="Post code">
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold">Phone Number</label>
                        <input type="number" name="phone_number" value="{{ old('phone_number') }}"
                            class="w-full border rounded-lg px-4 py-2" placeholder="Your phone number">
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold">Add. Notes</label>
                        <textarea name="notes" class="w-full border rounded-lg px-4 py-2" placeholder="Add your note"></textarea>
                    </div>

                    <div>
                        <label class="block mb-1 font-semibold">Proof of Payment</label>
                        <input type="file" name="proof" class="w-full border rounded-lg px-4 py-2">
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-800 transition">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('/assets/scripts/global.js') }}"></script>
    <script>
        function calculatePrice() {
            let subTotal = 0;
            let tax = 0;
            let insurance = 0;
            let deliveryFee = 0;
            let grandTotal = 0;

            document.querySelectorAll('.product-price').forEach(item => {
                subTotal += parseFloat(item.getAttribute('data-price'));
            });

            document.getElementById('checkoutDeliveryFee').textContent = 'Rp ' + deliveryFee.toLocaleString('id');
            document.getElementById('checkoutSubTotal').textContent = 'Rp ' + subTotal.toLocaleString('id');

            tax = (11 / 100) * subTotal;
            document.getElementById('checkoutTax').textContent = 'Rp ' + tax.toLocaleString('id');

            insurance = (23 / 100) * subTotal;
            document.getElementById('checkoutInsurance').textContent = 'Rp ' + insurance.toLocaleString('id');

            grandTotal = subTotal + tax + insurance;
            document.getElementById('checkoutGrandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id');
        }

        document.addEventListener('DOMContentLoaded', calculatePrice);
    </script>
</x-layout-front>
