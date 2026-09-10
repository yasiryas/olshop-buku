<x-layout-front title="Transaksi - Wigati Buku">
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">My Order</h4>
            <p class="text-lg mb-8 text-gray-600">Daftar Transaksi</p>
        </div>
    </section>
    <section class="container mx-auto px-10 mb-20">
        <div class="py-12 bg-grey-100">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                @include('front.partials.orders_list', ['product_transactions' => $product_transactions])
            </div>
        </div>
    </section>
</x-layout-front>