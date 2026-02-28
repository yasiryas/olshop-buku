<x-layout-front title="Transaksi - Wigati Buku">
    {{-- herosection --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">My Order</h4>
            <p class="text-lg mb-8 text-gray-600">Daftar Transaksi</p>
        </div>
    </section>
    <section class="container mx-auto px-10 mb-20">
        <div class="py-12 bg-grey-100">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-grey-100 flex flex-col gap-y-5 p-10 overflow-hidden shadow-lg border sm:rounded-lg">
                    @forelse ($product_transactions as $transaction)
                        <div class="item-card flex flex-row justify-between items-center">
                            <a href="{{ route('product_transactions.show', $transaction) }}">
                                <div>
                                    <p class="text-base text-slate-500">
                                        Date
                                    </p>
                                    <h3 class="text-xl font-bold text-indigo-900">
                                        {{ $transaction->created_at->format('d F Y') }}</h3>
                                </div>
                            </a>
                            <div class="hidden md:flex flex-col">
                                <p class="text-base text-slate-500">
                                    Buyer
                                </p>
                                <h3 class="text-xl font-bold text-indigo-900">
                                    {{ $transaction->user->name }}</h3>
                            </div>
                            <div class="md:flex flex-row items-center gap-x-3 hidden">
                                <div>
                                    <p class="text-base text-slate-500">
                                        Total Transaksi
                                    </p>
                                    <h3 class="text-xl font-bold text-indigo-900">Rp.
                                        {{ number_format($transaction->total_amount) }}
                                    </h3>
                                </div>
                            </div>

                            @if ($transaction->is_paid)
                                <span class="font-bold py-1 px-5 rounded-full text-white bg-green-500">
                                    <p class="text-white font-bold text-sm">Success</p>
                                </span>
                            @else
                                <span class="font-bold py-1 px-5 rounded-full text-white bg-orange-500">
                                    <p class="text-white font-bold text-sm">Pending</p>
                                </span>
                            @endif

                            <div class="hidden md:flex flex-row items-center gap-x-3">
                                <a href="{{ route('product_transactions.show', $transaction) }}"
                                    class="font-bold py-3 px-5 rounded-full text-white bg-blue-700">View
                                    Details</a>
                            </div>
                        </div>
                        <hr class="my-3">
                    @empty
                        <p>Ups, transaksi terbaru belum tersedia!</p>
                    @endforelse
                </div>
            </div>
    </section>
</x-layout-front>
