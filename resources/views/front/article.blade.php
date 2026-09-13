@php
    $metaKeywords = ($article->category->name ?? 'artikel') . ', artikel, blog, Wigati Buku';
@endphp

<x-layout-front
    :title="$article->title . ' - Wigati Buku'"
    :description="strip_tags(Str::limit($article->content, 160))"
    :keywords="$metaKeywords"
    :image="$article->featured_image"
    :canonical="route('front.article.details', $article->slug)">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'description' => Str::limit(strip_tags($article->content), 160),
            'image' => Storage::url($article->featured_image),
            'datePublished' => $article->published_at
                ? $article->published_at->toIso8601String()
                : $article->created_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $article->user->name],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/logo/icon-book.webp')],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('front.article.details', $article->slug),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    {{-- article section --}}
    <section class="py-12 md:py-20 px-4 md:px-10 space-x-6 container mx-auto flex items-center justify-between">
        <div class="container mx-auto w-full md:w-3/6 text-center">
            <h4 class="text-2xl md:text-4xl font-bold mb-4 text-gray-700">{{ $article->title }}</h4>
            <p class="text-base md:text-lg mb-8 text-gray-600">By {{ $article->user->name }} |
                {{ $article->created_at->idShort() }}
            </p>
        </div>
    </section>
    <section class="container mx-auto px-4 md:px-10 mb-12 md:mb-20">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg">
            <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                class="w-full h-64 md:h-96 object-cover mb-4 rounded">
            <div class="prose max-w-none">
                {!! $article->content !!}
            </div>
        </div>
    </section>
    {{-- end article section --}}
    {{-- blog section --}}
    <section class="py-12 md:py-20 px-4 md:px-10 bg-gray-100">
        <div class="container mx-auto ">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 md:mb-10">Latest Article Posts</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($articles as $relatedArticle)
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                        <img src=" {{ Storage::url($relatedArticle->featured_image) }}  " alt="{{ $relatedArticle->title }}"
                            class="w-full h-48 object-cover mb-4 rounded">
                        <h3 class="text-xl font-semibold mb-2">{{ $relatedArticle->title }}</h3>
                        <p class="text-gray-600 mb-4"> {{ Str::limit(strip_tags($relatedArticle->content), 100, '...') }}</p>
                        <a href="{{ route('front.article.details', $relatedArticle->slug) }}"
                            class="text-red-600 hover:underline">Read
                            More</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layout-front>
