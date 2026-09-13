<x-ui.table :headers="['Retur', 'Order', 'Pembeli', 'Alasan', 'Status', 'Aksi']"
    :footer="$returns->hasPages() ? $returns->appends(['search' => $search ?? null])->links() : null">
    @forelse ($returns as $returnRequest)
        <tr>
            <td class="cell"><span class="font-medium text-gray-900">#{{ $returnRequest->id }}</span></td>
            <td class="cell">
                <a href="{{ route('product_transactions.show', $returnRequest->transaction) }}"
                    class="text-indigo-600 hover:underline">#{{ $returnRequest->transaction->id }}</a>
            </td>
            <td class="cell cell-soft">{{ $returnRequest->transaction->user->name ?? 'N/A' }}</td>
            <td class="cell cell-soft whitespace-normal">
                {{ $returnRequest->reason }}
                @if ($returnRequest->description)
                    <div class="text-xs text-gray-500 mt-1">{{ $returnRequest->description }}</div>
                @endif
            </td>
            <td class="cell">
                <x-ui.badge :class="$returnRequest->statusBadgeColor()">
                    {{ $returnRequest->statusLabel() }}
                </x-ui.badge>
            </td>
            <td class="cell">
                @if ($returnRequest->admin_note)
                    <p class="text-xs text-gray-500 mb-2">Catatan: {{ $returnRequest->admin_note }}</p>
                @endif
                @if ($returnRequest->status === 'requested')
                    <div class="flex flex-wrap gap-2 items-center">
                        <x-ui.pill as="button" type="button" color="btn-pill-success"
                            @click="approveReturn = { id: '{{ $returnRequest->id }}' }; $dispatch('open-modal', 'approve-return-modal')">
                            <i class="fas fa-check text-xs"></i> Setujui &amp; Balikkan Stok
                        </x-ui.pill>
                        <x-ui.pill as="button" type="button" color="btn-pill-danger"
                            @click="rejectReturn = { id: '{{ $returnRequest->id }}' }; $dispatch('open-modal', 'reject-return-modal')">
                            <i class="fas fa-times text-xs"></i> Tolak
                        </x-ui.pill>
                    </div>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="cell text-center text-gray-500">Belum ada pengajuan retur.</td>
        </tr>
    @endforelse
</x-ui.table>