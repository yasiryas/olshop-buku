<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Orders') }}
            </h2>
            <form action="{{ route('product_transactions.index') }}"
                x-data="searchableList('{{ route('product_transactions.index') }}', 'results-orders')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 text-slate-400 rounded-full px-4 py-2">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div id="results-orders">
                @include('admin.partials.orders_list')
            </div>
        </div>
</x-app-layout>
