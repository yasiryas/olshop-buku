<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->hasRole('owner') ? __('Orders') : __('Orders') }}
            </h2>
            <form action="{{ route('product_transactions.index') }}">
                <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}"
                    class="border-2 text-slate-400 rounded-full px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-indigo-700 text-white rounded-full"><i
                        class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
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
            <div class="mt-5">
                {{ $product_transactions->appends(request()->query())->links() }}
            </div>
        </div>
</x-app-layout>
