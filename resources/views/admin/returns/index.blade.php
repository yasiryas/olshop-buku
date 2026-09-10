<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Retur / Pengembalian') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
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
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                                                <div class="flex flex-wrap gap-2 items-center" x-data="{ rejectNote: false }">
                                                    <form
                                                        action="{{ route('admin.returns.approve', $returnRequest) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Setujui retur ini? Stok akan dikembalikan dan order ditandai Dikembalikan.')">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-xs font-bold bg-green-600 text-white py-2 px-4 rounded-full hover:bg-green-700">
                                                            Setujui & Balikkan Stok
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="rejectNote = !rejectNote"
                                                        class="text-xs font-bold bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700">
                                                        Tolak
                                                    </button>
                                                    <form x-show="rejectNote" x-transition
                                                        action="{{ route('admin.returns.reject', $returnRequest) }}"
                                                        method="POST" class="flex flex-wrap gap-2 items-center w-full">
                                                        @csrf
                                                        <input type="text" name="admin_note" required placeholder="Alasan penolakan"
                                                            class="text-sm border rounded-lg px-3 py-2 flex-1 min-w-[200px]">
                                                        <button type="submit"
                                                            class="text-xs font-bold bg-gray-800 text-white py-2 px-4 rounded-full hover:bg-gray-900">
                                                            Simpan
                                                        </button>
                                                    </form>
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
                        {{ $returns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>