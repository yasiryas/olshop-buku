<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    protected const DELIVERY_FEE = 0;
    protected const TAX_RATE = 0.11;
    protected const INSURANCE_RATE = 0.23;

    protected function calculateTotal(array $items): int
    {
        $subTotal = array_sum(array_map(fn ($item) => $item['product']->price * $item['qty'], $items));

        return (int) round($subTotal * (1 + self::TAX_RATE + self::INSURANCE_RATE));
    }

    public function run(): void
    {
        $buyer = User::where('email', 'buyer@mail.com')->first();
        $danang = User::where('email', 'danang@mail.com')->first();
        $rahma = User::where('email', 'rahma@mail.com')->first();

        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        $transactions = [
            [
                'user' => $buyer,
                'is_paid' => true,
                'address' => 'Jl. Melati No. 10, RT 02 RW 03',
                'city' => 'Surabaya',
                'post_code' => '60243',
                'phone_number' => '081234567890',
                'notes' => 'Dimohon dibungkus rapi',
                'items' => [
                    ['product' => $products[0], 'qty' => 2],
                    ['product' => $products[1], 'qty' => 1],
                ],
            ],
            [
                'user' => $danang,
                'is_paid' => false,
                'address' => 'Jl. Kenanga No. 5, Perum Griya Asri',
                'city' => 'Yogyakarta',
                'post_code' => '55281',
                'phone_number' => '085678901234',
                'notes' => null,
                'items' => [
                    ['product' => $products[2], 'qty' => 3],
                ],
            ],
            [
                'user' => $rahma,
                'is_paid' => false,
                'address' => 'Jl. Anggrek No. 15',
                'city' => 'Bandung',
                'post_code' => '40115',
                'phone_number' => '082198765432',
                'notes' => 'Bisa kirim senin pagi',
                'items' => [
                    ['product' => $products[1], 'qty' => 1],
                    ['product' => $products[4], 'qty' => 2],
                ],
            ],
            [
                'user' => $buyer,
                'is_paid' => true,
                'address' => 'Jl. Melati No. 10, RT 02 RW 03',
                'city' => 'Surabaya',
                'post_code' => '60243',
                'phone_number' => '081234567890',
                'notes' => null,
                'items' => [
                    ['product' => $products[6], 'qty' => 1],
                    ['product' => $products[7], 'qty' => 2],
                ],
            ],
        ];

        foreach ($transactions as $data) {
            if ($data['user'] === null || $data['user']->productTransactions()->count() >= 2) {
                continue;
            }

            $totalAmount = $this->calculateTotal($data['items']);

            $transaction = ProductTransaction::updateOrCreate(
                [
                    'user_id' => $data['user']->id,
                    'total_amount' => $totalAmount,
                    'address' => $data['address'],
                ],
                [
                    'is_paid' => $data['is_paid'],
                    'city' => $data['city'],
                    'post_code' => $data['post_code'],
                    'phone_number' => $data['phone_number'],
                    'notes' => $data['notes'],
                ]
            );

            foreach ($data['items'] as $item) {
                TransactionDetail::updateOrCreate(
                    [
                        'product_transaction_id' => $transaction->id,
                        'product_id' => $item['product']->id,
                    ],
                    [
                        'price' => $item['product']->price,
                        'qty' => $item['qty'],
                    ]
                );
            }
        }

        $this->seedCarts($buyer);
        $this->seedCarts($danang);
    }

    protected function seedCarts(User $user): void
    {
        if ($user === null || $user->carts()->count() > 0) {
            return;
        }

        $products = Product::take(4)->get();

        foreach ($products as $product) {
            Cart::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                ['quantity' => 1]
            );
        }
    }
}