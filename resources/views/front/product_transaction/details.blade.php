<x-layout-front title="Detail Transaksi - Wigati Buku">
    <section class="py-16 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Detail Pesanan #{{ $product_transaction->id }}</h4>
            <p class="text-lg mb-8 text-gray-600">Pantau status pesanan Anda di sini.</p>
        </div>
    </section>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-grey-100 flex flex-col gap-y-5 p-10 overflow-hidden shadow-lg sm:rounded-lg border">

                {{-- Header: total / date / status --}}
                <div class="item-card flex gap-y-3 flex-col md:flex-row justify-between md:items-center">
                    <div>
                        <p class="text-base text-slate-500">Total Transaksi</p>
                        <h3 class="text-xl font-bold text-indigo-900">Rp {{ number_format($product_transaction->total_amount) }}</h3>
                    </div>
                    <div>
                        <p class="text-base text-slate-500">Date</p>
                        <h3 class="text-xl font-bold text-indigo-900">{{ $product_transaction->created_at->format('d F Y') }}</h3>
                    </div>
                    <span class="font-bold py-1 px-5 rounded-full w-fit text-white {{ $product_transaction->statusBadgeColor() }}">
                        {{ $product_transaction->statusLabel() }}
                    </span>
                </div>

                {{-- Tracking progress --}}
                @if (in_array($product_transaction->status, ['processing', 'shipped', 'completed']))
                    @php
                        $flow = [
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'completed' => 'Selesai',
                        ];
                        $flowKeys = array_keys($flow);
                        $currentIndex = array_search($product_transaction->status, $flowKeys);
                    @endphp
                    <div class="flex items-center gap-2">
                        @foreach ($flow as $i => $label)
                            @php
                                $stepIndex = array_search($i, $flowKeys);
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm {{ $stepIndex < $currentIndex ? 'bg-green-500' : ($stepIndex === $currentIndex ? 'bg-indigo-600' : 'bg-gray-200') }}">
                                    <i class="fas {{ $stepIndex < $currentIndex ? 'fa-check' : 'fa-circle' }}"></i>
                                </div>
                                <span class="text-xs text-gray-600">{{ $label }}</span>
                            </div>
                            @if (!$loop->last)
                                <div class="flex-1 h-0.5 {{ $stepIndex < $currentIndex ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                            @endif
                        @endforeach
                    </div>
                @elseif (in_array($product_transaction->status, ['rejected', 'cancelled']))
                    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        <p class="text-base font-bold text-grey-800">
                            {{ $product_transaction->status === 'rejected' ? 'Pesanan Ditolak' : 'Pesanan Dibatalkan' }}
                        </p>
                        @if ($product_transaction->status === 'rejected' && $product_transaction->rejection_note)
                            <p class="text-sm text-red-700 mt-1">Alasan: {{ $product_transaction->rejection_note }}</p>
                        @endif
                    </div>
                @endif

                {{-- Resi --}}
                @if (in_array($product_transaction->status, ['shipped', 'completed']) && $product_transaction->tracking_number)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-slate-500">Nomor Resi ({{ $product_transaction->shipping_method }})</p>
                        <p class="text-2xl font-bold text-indigo-900">{{ $product_transaction->tracking_number }}</p>
                    </div>
                @endif

                <hr class="my-3">
                <h3 class="text-xl font-bold text-indigo-900">List of Item</h3>

                <div class="grid-cols-1 md:grid-cols-4 grid gap-y-10 md:gap-x-10">
                    <div class="flex flex-col gap-y-5 col-span-2">
                        @forelse ($product_transaction->transactionDetails as $list_product)
                            <div class="item-card flex flex-row justify-between items-center">
                                <div class="flex flex-row items-center gap-x-3">
                                    <img src="{{ Storage::url($list_product->product->photo) }}" alt=""
                                        class="w-[50px] h-[50px]">
                                    <div>
                                        <h3 class="text-xl font-bold text-indigo-900">{{ $list_product->product->name }}</h3>
                                        <p class="text-base text-slate-500">Rp {{ number_format($list_product->product->price) }}</p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-500">{{ $list_product->qty }} Pcs</p>
                            </div>
                        @empty
                            <p>Ups, transaksi terbaru belum tersedia!</p>
                        @endforelse

                        <h3 class="text-xl font-bold text-indigo-900">Detail Pembayaran</h3>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Metode Pembayaran</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->payment_method ?? '-' }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Pengiriman</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->shipping_method ?? '-' }}
                                    <span class="text-base font-normal">({{
                                        $product_transaction->shipping_cost ? 'Rp ' . number_format($product_transaction->shipping_cost) : 'Gratis'
                                    }})</span>
                                </h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Address</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->address }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">City</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->city }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Post Code</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->post_code }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Phone Number</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->phone_number }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Note</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->notes }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-y-5 col-span-2 items-center">
                        <h3 class="text-xl font-bold text-indigo-900">Proof of Payment</h3>
                        <img src="{{ Storage::url($product_transaction->proof) }}" alt=""
                            class="w-[300px] bg-white-500 h-[400px] object-contain">
                    </div>
                </div>

                <hr class="my-4">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    {{-- Batal ketika masih pending --}}
                    @if ($product_transaction->status === 'pending')
                        <form action="{{ route('product_transactions.destroy', $product_transaction->id) }}" method="POST"
                            onsubmit="return confirm('Batalkan pesanan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="font-bold bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-full transition">
                                <i class="fas fa-times-circle mr-2"></i>Batalkan Pesanan
                            </button>
                        </form>
                    @else
                        <span class="text-sm text-gray-400">
                            Pesanan berstatus <strong>{{ $product_transaction->statusLabel() }}</strong> tidak dapat dibatalkan.
                        </span>
                    @endif

                    {{-- Contact admin via WA (opsional, bukan pengganti tracking) --}}
                    <a href="https://wa.me/{{ \App\Support\WaNotifier::phoneToWa(\App\Support\StoreSettings::waContact()) }}?text={{ urlencode('Halo Admin, saya ingin tanya soal pesanan #' . $product_transaction->id . '.') }}"
                        target="_blank"
                        class="w-fit font-bold bg-indigo-700 text-white py-3 px-6 rounded-full hover:bg-indigo-900 transition">
                        <i class="fab fa-whatsapp mr-2"></i>Hubungi Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout-front>