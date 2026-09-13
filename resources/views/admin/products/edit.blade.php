<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Produk') }}
            </h2>
            <a href="{{ route('admin.categories.index') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Kelola
                Kategori</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                value="{{ $product->name }}" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Harga (Rp)')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="text" name="price"
                                placeholder="Contoh: 50.000"
                                value="{{ number_format($product->price, 0, '', '.') }}" required autofocus autocomplete="price" />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Kategori')" />
                            <select name="category_id" id="category_id" x-select2 class="mt-1 block w-full">
                                <option value="">Pilih Kategori</option>
                                @forelse ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        @if ($product->category_id == $category->id) @selected(true) @endif>
                                        {{ $category->name }}</option>
                                @empty
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!--About-->
                        <div class="mt-4">
                            <x-input-label for="about" :value="__('Deskripsi')" />
                            <textarea name="about" id="about" cols="30" rows="10"
                                class="border  rounded-lg w-full border-slate-300">{{ $product->about }}</textarea>
                            <x-input-error :messages="$errors->get('about')" class="mt-2" />
                        </div>

                        <!-- Photo -->
                        <div class="mt-4">
                            <x-input-label for="photo" :value="__('Foto')" />
                            <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->photo }}"
                                class="w-[50px] h-[50px]" />
                            <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo"
                                autofocus autocomplete="photo" />
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Perbarui Produk') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const priceInput = document.getElementById('price');
        priceInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    </script>
</x-app-layout>
