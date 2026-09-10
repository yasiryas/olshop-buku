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
    {{ $mutations->appends(['search' => $search])->links() }}
</div>