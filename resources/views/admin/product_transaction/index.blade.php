<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pesanan') }}
            </h2>
            <form action="{{ route('product_transactions.index') }}"
                x-data="searchableList('{{ route('product_transactions.index') }}', 'results-orders')"
                @submit.prevent="search()"
                class="flex flex-col sm:flex-row sm:items-end flex-wrap gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Periode</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="from" x-model="from" value="{{ request('from') }}"
                            class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                        <span class="text-gray-400 text-sm">s/d</span>
                        <input type="date" name="to" x-model="to" value="{{ request('to') }}"
                            class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                    <select name="status" x-model="status" @change="search()" x-select2
                        class="min-w-[180px]">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\ProductTransaction::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cari</label>
                    <input type="text" name="search" placeholder="Cari no. pesanan..." value="{{ request('search') }}"
                        x-model="keyword" @input.debounce.500ms="search()"
                        class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                </div>

                <button type="button" @click="resetFilters()"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-900 border-2 border-gray-300 rounded-full px-4 py-2 bg-white transition">
                    <i class="fas fa-rotate-left mr-1"></i>Reset
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="orderDetail">
            <div id="results-orders">
                @include('admin.partials.orders_list')
            </div>
        </div>
</x-app-layout>
