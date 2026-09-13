<x-ui.table :headers="['No. Pesanan', 'Pembeli', 'Tanggal', 'Total', 'Status', 'Resi', 'Aksi']"
    :footer="$product_transactions->hasPages() ? $product_transactions->appends(request()->except('page'))->links() : null">
    @forelse ($product_transactions as $transaction)
        <tr class="{{ $transaction->statusRowColor() }}">
            <td class="cell"><span class="font-medium text-gray-900">#{{ $transaction->id }}</span></td>
            <td class="cell">{{ $transaction->user->name ?? 'N/A' }}</td>
            <td class="cell cell-soft">{{ $transaction->created_at->idShort() }}</td>
            <td class="cell">Rp {{ number_format($transaction->total_amount) }}</td>
            <td class="cell">
                <x-ui.badge :class="$transaction->statusBadgeColor()">
                    {{ $transaction->statusLabel() }}
                </x-ui.badge>
            </td>
            <td class="cell cell-soft">{{ $transaction->tracking_number ?? '-' }}</td>
            <td class="cell">
                <x-ui.pill as="button" type="button" color="btn-pill-primary"
                    @click="openDetail('{{ route('product_transactions.preview', $transaction) }}')">
                    <i class="fas fa-eye text-xs"></i> Detail
                </x-ui.pill>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="cell text-center text-gray-500">Tidak ada pesanan.</td>
        </tr>
    @endforelse
</x-ui.table>