<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>
            <div class="flex gap-x-3">
                    <form method="GET" action="{{ route('stocks.allHistory') }}"
                        x-data="searchableList('{{ route('stocks.allHistory') }}', 'results-mutations')"
                        @submit.prevent="search()">
                        <input type="text" name="search" placeholder="Search by product name..."
                            value="{{ request('search') }}"
                            x-model="keyword" @input.debounce.500ms="search()"
                            class="border-2 text-slate-400 rounded-full px-4 py-2">
                    </form>
                <a href="{{ route('stocks.index') }}" class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">
                    Stock Mutations
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div id="results-mutations">
                    @include('admin.partials.mutations_list')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
