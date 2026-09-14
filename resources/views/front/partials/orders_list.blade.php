<x-ui.table :headers="['No. Pesanan', 'Tanggal', 'Total', 'Status', 'Resi', 'Aksi']"
    :footer="$product_transactions->hasPages() ? $product_transactions->links() : null">
    @forelse ($product_transactions as $transaction)
        <tr class="{{ $transaction->statusRowColor() }}">
            <td class="cell"><span class="font-medium text-gray-900">#{{ $transaction->id }}</span></td>
            <td class="cell cell-soft">{{ $transaction->created_at->idShort() }}</td>
            <td class="cell">{{ rupiah($transaction->total_amount) }}</td>
            <td class="cell">
                <x-ui.badge :class="$transaction->statusBadgeColor()">
                    {{ $transaction->statusLabel() }}
                </x-ui.badge>
            </td>
            <td class="cell cell-soft">{{ $transaction->tracking_number ?? '-' }}</td>
            <td class="cell">
                <x-ui.pill as="a" href="{{ route('product_transactions.show', $transaction) }}" color="btn-pill-primary">
                    <i class="fas fa-eye text-xs"></i> Detail
                </x-ui.pill>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="cell text-center text-gray-500">Ups, transaksi belum tersedia!</td>
        </tr>
    @endforelse
</x-ui.table>