<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage categories') }}
            </h2>
            <form method="GET" action="{{ route('admin.categories.index') }}">
                <input type="text" name="search" placeholder="Search categories..." value="{{ request('search') }}"
                    class="border-2 text-slate-400 rounded-full px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-indigo-700 text-white rounded-full"><i
                        class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <a href="{{ route('admin.categories.create') }}"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">Add
                category</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
                @forelse($categories as $category)
                    <div class="item-card flex flex-row justify-between items-center">
                        <img src="{{ Storage::url($category->icon) }}" alt="" class="w-[50px] h-[50px]">

                        <h3 class="text-xl font-bold text-indigo-900">{{ $category->name }}</h3>
                        <div class="flex flex-row items-center gap-x-3">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                                class="font-bold py-3 px-5 rounded-full text-white bg-yellow-500">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button class="font-bold py-3 px-5 rounded-full text-white bg-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <div class="mt-5">
                {{ $categories->appends(request()->query())->links() }}
            </div>
        </div>
</x-app-layout>
