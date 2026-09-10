<div class="flex flex-col gap-y-5 p-10 overflow-hidden shadow-lg border sm:rounded-lg">
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

            @if ($transaction->status === 'cancelled')
                <span class="font-bold py-1 px-5 rounded-full text-white bg-red-500">
                    <p class="text-white font-bold text-sm">Cancelled</p>
                </span>
            @elseif ($transaction->is_paid || $transaction->status === 'approved')
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
                    class="font-bold py-3 px-5 rounded-full text-white bg-blue-700 hover:bg-blue-800 transition">View
                    Details</a>
            </div>
        </div>
        <hr class="my-3">
    @empty
        <div class="p-4 text-center">
            <p class="text-gray-500 font-semibold">Ups, transaksi belum tersedia!</p>
        </div>
    @endforelse

    @if ($product_transactions->hasPages())
        <div class="mt-4">
            {{ $product_transactions->links() }}
        </div>
    @endif
</div>