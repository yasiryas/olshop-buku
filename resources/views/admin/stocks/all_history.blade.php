<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>
            <div class="flex gap-x-3">
                <form method="GET" action="{{ route('stocks.allHistory') }}" class="flex gap-x-3">
                    <input type="text" name="search" placeholder="Search by product name..."
                        value="{{ request('search') }}" class="border-2 text-slate-400 rounded-full px-3 py-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-700 text-white rounded-full">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
                <a href="{{ route('stocks.index') }}" class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">
                    Stock Mutations
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white flex flex-col gap-y-5 p-10 shadow-sm sm:rounded-lg">
                    @forelse ($mutations as $m)
                        <div class="item-card flex flex-row justify-between items-center border-b pb-4">
                            <div class="flex flex-row items-center gap-x-3 w-64">
                                <div>
                                    <h3 class="text-lg font-bold text-indigo-900">{{ $m->product->name }}</h3>
                                </div>
                            </div>
                            <p class="w-40 text-base text-slate-500">
                                Tanggal: <br>{{ $m->created_at }}
                            </p>
                            <div class="w-32 text-center">
                                <p class="text-xs text-slate-500">Tipe</p>
                                <p class="font-bold {{ $m->type == 'in' ? 'text-indigo-600' : 'text-yellow-400' }}">
                                    {{ strtoupper($m->type) }}
                                </p>
                            </div>
                            <div class="w-32 text-center">
                                <p class="text-xs text-slate-500">Jumlah</p>
                                <p class="text-xl font-bold text-indigo-600">
                                    {{ $m->quantity }}
                                </p>
                            </div>
                            <div class="w-64 text-center">
                                <p class="text-xs text-slate-500">Deskripsi</p>
                                <p class="text-base text-indigo-900">
                                    {{ $m->description }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-600">
                            Ups, belum ada history stock.
                        </p>
                    @endforelse
                </div>
                <div class="mt-5">
                    {{ $mutations->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
