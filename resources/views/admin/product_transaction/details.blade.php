<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->hasRole('owner|admin') ? __('Details') : __('Details') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="item-card flex gap-y-3 flex-col md:flex-row justify-between md:items-center">
                    <div class="flex flex-col md:flex-row md:items-center gap-x-3">
                        <div>
                            <p class="text-base text-slate-500">
                                Total Transaksi
                            </p>
                            <h3 class="text-xl font-bold text-indigo-900">Rp.
                                {{ number_format($product_transaction->total_amount) }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-x-3">
                        <p class="text-base text-slate-500">
                            Date
                        </p>
                        <h3 class="text-xl font-bold text-indigo-900">
                            {{ $product_transaction->created_at->format('d F Y') }}</h3>
                    </div>
                    @if ($product_transaction->is_paid)
                        <span class="font-bold py-1 px-5 rounded-full w-fit text-white bg-green-500">
                            <p class="text-white font-bold text-sm">Success</p>
                        </span>
                    @else
                        <span class="font-bold py-1 px-5 rounded-full w-fit text-white bg-orange-500">
                            <p class="text-white font-bold text-sm">Pending</p>
                        </span>
                    @endif
                </div>
                <hr class="my-3">
                <h3 class="text-xl font-bold text-indigo-900">
                    List of Item
                </h3>

                <div class="grid-cols-1 md:grid-cols-4 grid gap-y-10  md:gap-x-10">
                    <div class="flex flex-col gap-y-5 col-span-2">
                        @forelse ($product_transaction->transactionDetails as $list_product)
                            <div class="item-card flex flex-row justify-between items-center">
                                <div class="flex flex-row items-center gap-x-3">
                                    <img src="{{ Storage::url($list_product->product->photo) }}" alt=""
                                        class="w-[50px] h-[50px]">
                                    <div>
                                        <h3 class="text-xl font-bold text-indigo-900">
                                            {{ $list_product->product->name }}
                                        </h3>
                                        <p class="text-base text-slate-500">
                                            Rp. {{ $list_product->product->price }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-500">
                                    {{ $list_product->qty }} Pcs
                                </p>
                            </div>
                        @empty
                            <p>Ups, transaksi terbaru belum tersedia!</p>
                        @endforelse


                        <h3 class="text-xl font-bold text-indigo-900">
                            Detail of Payment
                        </h3>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">
                                    Address
                                </p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->address }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">
                                    City
                                </p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->city }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">
                                    Post Code
                                </p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->post_code }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">
                                    Phone Number
                                </p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->phone_number }}
                                </h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">
                                    Note
                                </p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->notes }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-y-5 col-span-2 items-center">
                        <h3 class="text-xl font-bold text-indigo-900">Proof of Payment</h3>
                        <img src="{{ Storage::url($product_transaction->proof) }}"
                            alt="{{ Storage::url($product_transaction->proof) }}"
                            class="w-[300px] bg-white-500 h-[400px] ">
                    </div>
                </div>
                <hr class="my-3">
                @role('owner|admin')
                    @if ($product_transaction->is_paid)
                        <a href="#"
                            class="w-fit font-bold bg-green-500 text-white py-3 px-5 rounded-full hover:bg-green-900">
                            WhatsApp Customer
                        </a>
                    @else
                        <form method="POST" action="{{ route('product_transactions.update', $product_transaction->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="font-bold bg-indigo-700 text-white py-3 px-5 rounded-full hover:bg-indigo-900">
                                Approve Order
                            </button>
                        </form>
                    @endif
                @endrole
                @role('buyer')
                    <a class="w-fit font-bold bg-indigo-700 text-white py-3 px-5 rounded-full hover:bg-indigo-900">
                        Contact Admin
                    </a>
                @endrole
            </div>
        </div>
    </div>
</x-app-layout>
