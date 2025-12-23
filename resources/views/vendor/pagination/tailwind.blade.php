@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="mt-6">

        {{-- MOBILE --}}
        <div class="flex justify-center gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-full cursor-not-allowed">
                    Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="px-4 py-2 text-sm text-indigo-600 border border-indigo-300 rounded-full hover:bg-indigo-50 transition">
                    Prev
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="px-4 py-2 text-sm text-indigo-600 border border-indigo-300 rounded-full hover:bg-indigo-50 transition">
                    Next
                </a>
            @else
                <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-full cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>

        {{-- DESKTOP --}}
        <div class="hidden sm:flex justify-center">
            <div class="inline-flex items-center gap-1">

                {{-- PREVIOUS --}}
                @if ($paginator->onFirstPage())
                    <span
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                        ‹
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50 transition">
                        ‹
                    </a>
                @endif

                {{-- PAGE NUMBERS --}}
                @foreach ($elements as $element)
                    {{-- DOTS --}}
                    @if (is_string($element))
                        <span class="w-9 h-9 flex items-center justify-center text-gray-400">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- LINKS --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    class="w-9 h-9 flex items-center justify-center rounded-full bg-indigo-600 text-white font-semibold shadow-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                    class="w-9 h-9 flex items-center justify-center rounded-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50 transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- NEXT --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50 transition">
                        ›
                    </a>
                @else
                    <span
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                        ›
                    </span>
                @endif

            </div>
        </div>
    </nav>
@endif
