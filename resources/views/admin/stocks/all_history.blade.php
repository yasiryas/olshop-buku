<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('stocks.allHistory') }}"
                        x-data="searchableList('{{ route('stocks.allHistory') }}', 'results-mutations')"
                        @submit.prevent="search()">
                        <input type="text" name="search" placeholder="Search by product name..."
                            value="{{ request('search') }}"
                            x-model="keyword" @input.debounce.500ms="search()"
                            class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
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
