<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\ProductTransaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    protected const TAX_RATE = 0.11;
    protected const INSURANCE_RATE = 0.23;

    protected const SHIPPING_METHODS = [
        ['courier' => 'JNE Reguler', 'cost' => 12000],
        ['courier' => 'J&T Express', 'cost' => 11000],
        ['courier' => 'GoSend Sameday', 'cost' => 25000],
    ];

    protected const PAYMENT_METHODS = ['Transfer Bank BCA', 'Transfer Bank BRI'];

    protected const ZONE_CITIES = [
        ['city' => 'Bandung', 'post_code' => '40111'],
        ['city' => 'Jakarta', 'post_code' => '10110'],
        ['city' => 'Cimahi', 'post_code' => '40512'],
    ];

    protected function calculateSubtotal(array $items): int
    {
        return array_sum(array_map(fn ($item) => $item['product']->price * $item['qty'], $items));
    }

    protected function calculateTotal(array $items, int $shippingCost): int
    {
        $subTotal = $this->calculateSubtotal($items);

        return (int) round($subTotal * (1 + self::TAX_RATE + self::INSURANCE_RATE)) + $shippingCost;
    }

    public function run(): void
    {
        $buyers = User::role('buyer')->get();

        if ($buyers->isEmpty()) {
            return;
        }

        $products = Product::orderBy('id')->get();

        if ($products->isEmpty()) {
            return;
        }

        $productCount = $products->count();
        $shippingCount = count(self::SHIPPING_METHODS);
        $paymentCount = count(self::PAYMENT_METHODS);
        $zoneCount = count(self::ZONE_CITIES);

        $productCursor = 0;
        $orderCounter = 0;

        for ($monthBack = 8; $monthBack >= 0; $monthBack--) {
            $monthStart = now()->startOfMonth()->subMonthsNoOverflow($monthBack);
            $daysInMonth = $monthStart->daysInMonth;

            foreach ($buyers as $buyerIndex => $buyer) {
                $ordersPerBuyer = $this->ordersPerBuyer($monthBack, $buyerIndex);

                for ($order = 0; $order < $ordersPerBuyer; $order++) {
                    $orderCounter++;

                    $status = $this->pickStatus($monthBack, $orderCounter);
                    $shipping = self::SHIPPING_METHODS[($orderCounter + $buyerIndex) % $shippingCount];
                    $zone = self::ZONE_CITIES[($orderCounter + $buyerIndex + $monthBack) % $zoneCount];
                    $payment = self::PAYMENT_METHODS[($orderCounter + $monthBack) % $paymentCount];

                    $itemCount = 1 + ($orderCounter % 2);
                    $items = [];
                    for ($i = 0; $i < $itemCount; $i++) {
                        $product = $products->get(($productCursor + $i + $monthBack) % $productCount);
                        $items[] = [
                            'product' => $product,
                            'qty' => 1 + (($orderCounter + $i) % 2),
                        ];
                    }
                    $productCursor = ($productCursor + $itemCount) % $productCount;

                    $shippingCost = $shipping['cost'];
                    $totalAmount = $this->calculateTotal($items, $shippingCost);

                    $day = 1 + (($orderCounter * 7 + $monthBack * 5) % min($daysInMonth, 28));
                    $hour = 8 + (($orderCounter * 3) % 11);
                    $createdAt = $monthStart->copy()->setDay($day)->setTime($hour, ($orderCounter * 17) % 60, 0);

                    $isPaid = in_array($status, ProductTransaction::PAID_STATUSES, true);

                    $transaction = ProductTransaction::updateOrCreate(
                        [
                            'user_id' => $buyer->id,
                            'total_amount' => $totalAmount,
                            'address' => $zone['city'] . ' Plaza, Jl. Merdeka No. ' . (10 + $orderCounter),
                        ],
                        [
                            'is_paid' => $isPaid,
                            'status' => $status,
                            'shipping_method' => $shipping['courier'],
                            'shipping_cost' => $shippingCost,
                            'payment_method' => $payment,
                            'tracking_number' => in_array($status, ['shipped', 'completed', 'returned']) ? $this->trackingNumber($orderCounter) : null,
                            'rejection_note' => $status === ProductTransaction::STATUS_REJECTED
                                ? 'Bukti transfer tidak sesuai.'
                                : null,
                            'city' => $zone['city'],
                            'post_code' => $zone['post_code'],
                            'phone_number' => $this->phoneNumber($buyerIndex, $orderCounter),
                            'notes' => $order % 3 === 0 ? 'Tolong dibungkus rapi.' : null,
                        ]
                    );

                    if (! $transaction->wasRecentlyCreated) {
                        continue;
                    }

                    $updatedAt = $createdAt->copy()->addDays($this->statusAdvanceDays($status));

                    $transaction->forceFill([
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ])->save();

                    $this->seedDetails($transaction, $items, $createdAt);
                    $this->seedStockAndReturns($transaction, $items, $status, $updatedAt);
                }
            }
        }

        $this->seedCarts($buyers, $products);
    }

    protected function ordersPerBuyer(int $monthBack, int $buyerIndex): int
    {
        if (($buyerIndex + $monthBack) % 3 !== 0) {
            return 0;
        }

        return 1 + (($buyerIndex * 2 + $monthBack) % 2);
    }

    protected function pickStatus(int $monthBack, int $orderCounter): string
    {
        $seed = ($monthBack * 31 + $orderCounter * 7) % 100;

        if ($monthBack >= 5 && $seed < 60) {
            return ProductTransaction::STATUS_COMPLETED;
        }

        if ($monthBack >= 3 && $seed < 75) {
            return ProductTransaction::STATUS_SHIPPED;
        }

        if ($seed < 12) {
            return ProductTransaction::STATUS_REJECTED;
        }

        if ($seed < 35) {
            return ProductTransaction::STATUS_CANCELLED;
        }

        if ($seed < 48) {
            return ProductTransaction::STATUS_RETURNED;
        }

        if ($monthBack <= 1 && $seed < 65) {
            return ProductTransaction::STATUS_PENDING;
        }

        return ProductTransaction::STATUS_PROCESSING;
    }

    protected function statusAdvanceDays(string $status): int
    {
        return match ($status) {
            ProductTransaction::STATUS_COMPLETED => 12 + (rand(0, 9) % 8),
            ProductTransaction::STATUS_SHIPPED => 5 + rand(0, 4),
            ProductTransaction::STATUS_RETURNED => 24 + rand(0, 10),
            ProductTransaction::STATUS_PROCESSING => 1,
            default => 0,
        };
    }

    protected function seedDetails(ProductTransaction $transaction, array $items, Carbon $createdAt): void
    {
        foreach ($items as $item) {
            $detail = TransactionDetail::updateOrCreate(
                [
                    'product_transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                ],
                [
                    'price' => $item['product']->price,
                    'qty' => $item['qty'],
                ]
            );

            if ($detail->wasRecentlyCreated) {
                $detail->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            }
        }
    }

    protected function seedStockAndReturns(
        ProductTransaction $transaction,
        array $items,
        string $status,
        Carbon $updatedAt
    ): void {
        if (in_array($status, ProductTransaction::PAID_STATUSES, true)) {
            foreach ($items as $item) {
                $item['product']->stockMutations()->updateOrCreate(
                    [
                        'type' => 'out',
                        'description' => 'Stock keluar untuk order #' . $transaction->id,
                    ],
                    ['quantity' => $item['qty']]
                );
            }
        }

        if ($status === ProductTransaction::STATUS_RETURNED) {
            foreach ($items as $item) {
                $item['product']->stockMutations()->updateOrCreate(
                    [
                        'type' => 'in',
                        'description' => 'Stok masuk dari retur pesanan #' . $transaction->id,
                    ],
                    ['quantity' => $item['qty']]
                );
            }

            $return = ProductReturn::updateOrCreate(
                ['product_transaction_id' => $transaction->id],
                [
                    'reason' => 'produk_rusak',
                    'description' => 'Barang diterima dalam kondisi tidak sesuai.',
                    'status' => ProductReturn::STATUS_APPROVED,
                    'admin_note' => 'Retur disetujui.',
                ]
            );

            $return->forceFill(['created_at' => $updatedAt, 'updated_at' => $updatedAt])->save();
        }
    }

    protected function trackingNumber(int $orderCounter): string
    {
        return 'WB' . str_pad((string) strtoupper(dechex(1000 + $orderCounter)), 6, '0', STR_PAD_LEFT);
    }

    protected function phoneNumber(int $buyerIndex, int $orderCounter): string
    {
        $prefix = ['0812', '0813', '0856', '0821', '0857', '0838', '0896', '0819', '0822'][($buyerIndex + $orderCounter) % 9];

        return $prefix . str_pad((string) str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT), 8, '0', STR_PAD_LEFT);
    }

    protected function seedCarts(iterable $buyers, iterable $products): void
    {
        foreach ($buyers as $user) {
            if ($user->carts()->count() > 0) {
                continue;
            }

            foreach (collect($products)->take(4) as $product) {
                Cart::updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $product->id],
                    ['quantity' => 1]
                );
            }
        }
    }
}