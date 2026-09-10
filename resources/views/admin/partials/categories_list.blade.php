<div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
    @forelse($categories as $category)
        <div class="item-card flex flex-row justify-between items-center">
            <div class="flex flex-row items-center gap-x-3">
                <img src="{{ Storage::url($category->icon) }}" alt="" class="w-[50px] h-[50px]">
                <h3 class="text-xl font-bold text-indigo-900">{{ $category->name }}</h3>
            </div>
            <div class="flex flex-row items-center gap-x-3">
                <button type="button" @click="openEdit('{{ $category->id }}')"
                    class="font-bold py-3 px-5 rounded-full text-white bg-yellow-500">Edit</button>
                <button type="button" @click="openDelete('{{ $category->id }}')"
                    class="font-bold py-3 px-5 rounded-full text-white bg-red-700">Delete</button>
            </div>
        </div>
    @empty
    @endforelse
</div>
<div class="mt-5">
    {{ $categories->appends(['search' => $search])->links() }}
</div>