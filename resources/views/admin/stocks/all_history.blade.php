<x-app-layout>
    <x-toast />
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>
            <a href="{{ route('stocks.index') }}" class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">
                Stock Mutations
            </a>
        </div>
    </x-slot>

    <div x-data="stockModal()">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white flex flex-col gap-y-5 p-10 shadow-sm sm:rounded-lg">
                    @forelse ($mutations as $m)
                        <div class="item-card flex flex-row justify-between items-center border-b pb-4">
                            <div class="flex flex-row items-center gap-x-3 w-64">
                                <div>
                                    <h3 class="text-lg font-bold text-indigo-900">{{ $m->product->name }}</h3>
                                    {{-- <p class="text-sm text-slate-500">
                                        Rp {{ number_format($m->product->price) }}
                                    </p> --}}
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
            </div>
        </div>
    </div>
</x-app-layout>
