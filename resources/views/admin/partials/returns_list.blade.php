<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($returns as $returnRequest)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $returnRequest->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <a href="{{ route('product_transactions.show', $returnRequest->transaction) }}"
                                    class="text-indigo-600 hover:underline">#{{ $returnRequest->transaction->id }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $returnRequest->transaction->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $returnRequest->reason }}
                                @if ($returnRequest->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ $returnRequest->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full text-white {{ $returnRequest->statusBadgeColor() }}">
                                    {{ $returnRequest->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($returnRequest->admin_note)
                                    <p class="text-xs text-gray-500 mb-2">Catatan: {{ $returnRequest->admin_note }}</p>
                                @endif
                                @if ($returnRequest->status === 'requested')
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <button type="button"
                                            @click="approveReturn = { id: '{{ $returnRequest->id }}' }; $dispatch('open-modal', 'approve-return-modal')"
                                            class="text-xs font-bold bg-green-600 text-white py-2 px-4 rounded-full hover:bg-green-700">
                                            Setujui & Balikkan Stok
                                        </button>
                                        <button type="button"
                                            @click="rejectReturn = { id: '{{ $returnRequest->id }}' }; $dispatch('open-modal', 'reject-return-modal')"
                                            class="text-xs font-bold bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700">
                                            Tolak
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada pengajuan retur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">
            {{ $returns->appends(['search' => $search ?? null])->links() }}
        </div>
    </div>
</div>