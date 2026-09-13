<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Retur / Pengembalian') }}
            </h2>
            <form method="GET" action="{{ route('admin.returns.index') }}"
                x-data="searchableList('{{ route('admin.returns.index') }}', 'results-returns')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Cari retur, order, pembeli..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
            </form>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ approveReturn: null, rejectReturn: null }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div id="results-returns">
                @include('admin.partials.returns_list')
            </div>
        </div>

        <x-modal name="approve-return-modal" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Setujui Retur</h2>
                <template x-if="approveReturn">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            Setujui retur #<span x-text="approveReturn.id"></span>?
                            Stok akan dikembalikan dan pesanan ditandai <b>Dikembalikan</b>.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="$dispatch('close-modal', 'approve-return-modal')"
                                class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                                Batal
                            </button>
                            <form method="POST" :action="`/admin/returns/${approveReturn.id}/approve`">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-full">
                                    Ya, Setujui
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>

        <x-modal name="reject-return-modal" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Tolak Retur</h2>
                <template x-if="rejectReturn">
                    <form method="POST" :action="`/admin/returns/${rejectReturn.id}/reject`">
                        @csrf
                        <label class="text-sm font-semibold text-gray-700">Alasan Penolakan</label>
                        <input type="text" name="admin_note" required placeholder="Alasan penolakan"
                            class="mt-1 w-full border rounded-lg px-4 py-2">
                        <div class="flex justify-end gap-3 mt-4">
                            <button type="button" @click="$dispatch('close-modal', 'reject-return-modal')"
                                class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full">
                                Tolak
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </x-modal>
    </div>
</x-app-layout>