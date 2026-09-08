<x-layout-front title="Blog - Wigati Buku">
    {{-- herosection --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Blog</h4>
            <p class="text-lg mb-8 text-gray-600">Temukan artikel terbaru di Wigati Buku</p>
        </div>
    </section>


    {{-- Article Section --}}
    <section class="py-20 bg-gray-100 px-10" id="articles" x-data="articleSearch()" x-init="fetchArticles()">
        <div class="container mx-auto flex flex-col gap-10">
            {{-- Search Box --}}
            <div class="w-1/2 mx-auto">
                <input type="text" x-model="keyword" @input.debounce.500ms="searchArticles"
                    style="background-image: url('/assets/svgs/ic-search.svg')"
                    class="block w-full py-3.5 pl-4 pr-10 rounded-[50px] font-semibold placeholder:text-grey placeholder:font-normal text-black text-base bg-no-repeat bg-[calc(100%-16px)]  focus:ring-2 focus:ring-primary focus:outline-none focus:border-none transition-all hover:ring-2 hover:ring-red-600"
                    placeholder="Cari artikel...">
            </div>

            {{-- Loading Spinner --}}
            <div class="flex justify-center" x-show="loading">
                <div class="w-10 h-10 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            {{-- Article Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <template x-for="article in articles" :key="article.id">
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                        <img :src="article.featured_image ? `/storage/${article.featured_image}` : '/assets/images/no-image.png'"
                            :alt="article.title" class="w-full h-48 object-cover mb-4 rounded">
                        <h3 class="text-xl font-semibold mb-2" x-text="article.title"></h3>
                        <p class="text-gray-600 mb-4"
                            x-text="article.content.replace(/<[^>]*>?/gm, '').substring(0, 100) + '...'"></p>
                        <a :href="`/article/${article.slug}`" class="text-red-600 hover:underline">Read More</a>
                    </div>
                </template>

                <template x-if="articles.length === 0 && !loading">
                    <div class="bg-white p-6 rounded-lg shadow-lg col-span-full text-center">
                        <p>Ups, Tidak ada artikel</p>
                    </div>
                </template>
            </div>
        </div>
    </section>


    {{-- Section CTA --}}
    <section>
        <div class="py-20 px-10 bg-white container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Ingin tahu lebih banyak tentang Wigati
                Buku?
            </p>
            <div class="text-center">
                <a href="{{ route('front.about') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full transition">
                    About Us
                </a>
            </div>
        </div>
    </section>
    <script>
        function articleSearch() {
            return {
                keyword: '',
                articles: [],
                loading: false,

                apiUrl() {
                    return window.location.protocol + '//' + window.location.host + '/search/articles';
                },

                fetchArticles(url = '') {
                    this.loading = true;
                    fetch(url || this.apiUrl())
                        .then(res => res.json())
                        .then(data => {
                            this.articles = data;
                            this.loading = false;
                        });
                },
                searchArticles() {
                    this.fetchArticles(this.apiUrl() + `?q=${this.keyword}`);
                }
            }
        }
    </script>
</x-layout-front>
