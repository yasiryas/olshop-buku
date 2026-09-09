<x-app-layout>
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/trumbowyg/dist/ui/trumbowyg.min.css') }}">

    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('New articles') }}
            </h2>
            <a href="{{ route('admin.articles.index') }}"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">Manage
                article</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="p-3 w-full rounded-lg bg-red-500 text-white mb-4">
                    @foreach ($errors->all() as $error)
                        <div>
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title')" required autofocus autocomplete="title" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Featured Image -->
                        <div class="mt-4">
                            <x-input-label for="featured_image" :value="__('Featured Image')" />
                            <x-text-input id="featured_image" class="block mt-1 w-full" type="file"
                                name="featured_image" required autofocus autocomplete="featured_image" />
                            <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category')" />
                            <select name="category_id" id="category_id" class="py-2 rounded-lg w-full border-slate-300">
                                <option value="">Select Category</option>
                                @forelse ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!--Content-->
                        <div class="mt-4">
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea name="content" id="content" cols="30" rows="10"
                                class="border  rounded-lg w-full border-slate-300">{{ old('content') }}</textarea>

                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Add New Article') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
        <style>
            .select2-container {
                width: 100%;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
        <script>
            $('#category_id').select2({ width: '100%', placeholder: 'Select Category', allowClear: true });
        </script>
    @endpush
</x-app-layout>
<script src="{{ asset('vendor/trumbowyg/dist/trumbowyg.min.js') }}"></script>
<script>
    $('#content').trumbowyg({
        btns: [
            ['viewHTML'],
            ['undo', 'redo'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
        ]
    });
</script>
