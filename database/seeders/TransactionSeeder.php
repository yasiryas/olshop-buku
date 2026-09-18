<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\ProductTransaction;
use App\Models\StockMutation;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    /** Rata-rata nilai transaksi yang dipakai untuk mencapai target pendapatan bulanan. */
    protected const AVG_ORDER_TOTAL = 680000;

    /** Jumlah bulan data demo (termasuk bulan berjalan). */
    protected const MONTHS = 12;

    /** Target stok akhir per produk urut berdasarkan id (sebagian sengaja menipis). */
    protected const TARGET_STOCKS = [3, 4, 45, 30, 60, 2, 40, 55];

    public function run(): void
    {
        if (app()->environment('testing')) {
            $this->runLight();

            return;
        }

        $this->runFull();
    }

    /**
     * Versi ringan untuk lingkungan testing (jumlah kecil agar cepat).
     */
    protected function runLight(): void
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

                    $maxDay = $monthBack === 0 ? min((int) now()->format('d'), $daysInMonth) : min($daysInMonth, 28);
                    $day = 1 + (($orderCounter * 7 + $monthBack * 5) % $maxDay);
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
                            'proof' => 'payment_proofs/seed/bukti.png',
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

    /**
     * Versi penuh: 1 tahun data dengan orderan sangat banyak
     * dan pendapatan bulanan berkisar 100-300 juta.
     */
    protected function runFull(): void
    {
        mt_srand(20260101);

        $buyerIds = User::role('buyer')->orderBy('id')->pluck('id')->all();

        if (empty($buyerIds)) {
            return;
        }

        $products = Product::orderBy('id')->get();

        if ($products->isEmpty()) {
            return;
        }

        $this->resetTransactionData();

        $productKeyed = $products->keyBy('id');
        $productIds = $productKeyed->keys()->all();
        $buyerCount = count($buyerIds);
        $productCount = count($productIds);
        $shippingCount = count(self::SHIPPING_METHODS);
        $zoneCount = count(self::ZONE_CITIES);
        $orderCounter = 0;

        for ($monthOffset = self::MONTHS - 1; $monthOffset >= 0; $monthOffset--) {
            $monthStart = now()->startOfMonth()->subMonthsNoOverflow($monthOffset);
            $daysInMonth = $monthStart->daysInMonth;
            $isCurrentMonth = $monthOffset === 0;

            $revenueTarget = mt_rand(120_000_000, 280_000_000);
            $orderTarget = (int) round($revenueTarget / self::AVG_ORDER_TOTAL / 0.84);

            for ($order = 0; $order < $orderTarget; $order++) {
                $orderCounter++;
                $orderIndex = $monthOffset * 10000 + $order;

                $status = $this->pickFullStatus($isCurrentMonth, $monthOffset, $orderIndex);
                $shipping = self::SHIPPING_METHODS[$orderIndex % $shippingCount];
                $zone = self::ZONE_CITIES[$orderIndex % $zoneCount];
                $buyerId = $buyerIds[$orderIndex % $buyerCount];

                $itemCount = 1 + ($orderIndex % 3);
                $items = [];
                for ($i = 0; $i < $itemCount; $i++) {
                    $productId = $productIds[($orderIndex + $i + $monthOffset) % $productCount];
                    $items[] = [
                        'product' => $productKeyed[$productId],
                        'qty' => 1 + (($orderIndex + $i) % 3),
                    ];
                }

                $shippingCost = $shipping['cost'];
                $totalAmount = $this->calculateTotal($items, $shippingCost);

                $day = 1 + (($orderIndex * 13 + $monthOffset * 5) % $daysInMonth);
                if ($monthOffset === 0) {
                    $maxDay = min($day, (int) now()->format('d'));
                    $day = min($day, $maxDay);
                }
                $hour = 8 + (($orderIndex * 3) % 11);
                $createdAt = $monthStart->copy()->setDay($day)->setTime($hour, ($orderIndex * 17) % 60, 0);
                $updatedAt = $createdAt->copy()->addDays($this->statusAdvanceDays($status));

                $isPaid = in_array($status, ProductTransaction::PAID_STATUSES, true);

                $transaction = new ProductTransaction([
                    'user_id' => $buyerId,
                    'total_amount' => $totalAmount,
                    'is_paid' => $isPaid,
                    'status' => $status,
                    'shipping_method' => $shipping['courier'],
                    'shipping_cost' => $shippingCost,
                    'payment_method' => self::PAYMENT_METHODS[$orderIndex % count(self::PAYMENT_METHODS)],
                    'tracking_number' => in_array($status, ['shipped', 'completed', 'returned']) ? $this->trackingNumber($orderIndex) : null,
                    'rejection_note' => $status === ProductTransaction::STATUS_REJECTED
                        ? 'Bukti transfer tidak sesuai.'
                        : null,
                    'proof' => 'payment_proofs/seed/bukti.png',
                    'address' => $zone['city'] . ', Jl. Merdeka No. ' . (10 + $orderIndex),
                    'city' => $zone['city'],
                    'post_code' => $zone['post_code'],
                    'phone_number' => $this->phoneNumber($orderIndex % $buyerCount, $orderIndex),
                    'notes' => $order % 3 === 0 ? 'Tolong dibungkus rapi.' : null,
                ]);

                $transaction->forceFill([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ])->save();

                $this->seedDetails($transaction, $items, $createdAt);
                $this->seedStockAndReturns($transaction, $items, $status, $updatedAt);
            }
        }

        $this->normalizeStock();
        $this->seedCarts($buyerIds, $productKeyed);
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

    protected function pickFullStatus(bool $isCurrentMonth, int $monthOffset, int $orderIndex): string
    {
        $seed = ($orderIndex * 13 + $monthOffset * 7) % 100;

        if ($monthOffset <= 1 && $seed < 14) {
            return ProductTransaction::STATUS_PENDING;
        }

        if ($seed < 4) {
            return ProductTransaction::STATUS_REJECTED;
        }

        if ($seed < 14) {
            return ProductTransaction::STATUS_CANCELLED;
        }

        if ($seed < 21) {
            return ProductTransaction::STATUS_RETURNED;
        }

        if ($seed < 37) {
            return ProductTransaction::STATUS_PROCESSING;
        }

        if ($seed < 57) {
            return ProductTransaction::STATUS_SHIPPED;
        }

        return ProductTransaction::STATUS_COMPLETED;
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

    protected function calculateSubtotal(array $items): int
    {
        return array_sum(array_map(fn ($item) => $item['product']->price * $item['qty'], $items));
    }

    protected function calculateTotal(array $items, int $shippingCost): int
    {
        $subTotal = $this->calculateSubtotal($items);

        return (int) round($subTotal * (1 + self::TAX_RATE + self::INSURANCE_RATE)) + $shippingCost;
    }

    /**
     * Bersihkan data transaksi lama agar hasil seed selalu bersih & tidak dobel.
     */
    protected function resetTransactionData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('transaction_details')->truncate();
        DB::table('product_returns')->truncate();
        DB::table('product_transactions')->truncate();
        DB::table('carts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Rapikan stok setelah seed: mutasi order dihapus dan diganti mutasi
     * "Stok awal" bernilai target supaya stok akhir bersih & sesuai TARGET_STOCKS.
     */
    protected function normalizeStock(): void
    {
        $products = Product::orderBy('id')->get();

        foreach ($products as $index => $product) {
            $target = self::TARGET_STOCKS[$index] ?? 30;
            $stokAwalRow = $product->stockMutations()
                ->where('description', 'Stok awal')
                ->orderBy('id')
                ->first();

            $product->stockMutations()
                ->whereNot('id', $stokAwalRow->id ?? 0)
                ->delete();

            if (! $stokAwalRow) {
                $product->stockMutations()->create([
                    'type' => 'in',
                    'description' => 'Stok awal',
                    'quantity' => $target,
                ]);

                continue;
            }

            $stokAwalRow->update(['quantity' => $target]);
        }
    }

    protected function seedDetails(ProductTransaction $transaction, array $items, Carbon $createdAt): void
    {
        foreach ($items as $item) {
            $detail = new TransactionDetail([
                'product_transaction_id' => $transaction->id,
                'product_id' => $item['product']->id,
                'price' => $item['product']->price,
                'qty' => $item['qty'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $detail->save();
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
                $item['product']->stockMutations()->create([
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'description' => 'Stock keluar untuk order #' . $transaction->id,
                ]);
            }
        }

        if ($status === ProductTransaction::STATUS_RETURNED) {
            foreach ($items as $item) {
                $item['product']->stockMutations()->create([
                    'type' => 'in',
                    'quantity' => $item['qty'],
                    'description' => 'Stok masuk dari retur pesanan #' . $transaction->id,
                ]);
            }

            $return = ProductReturn::create([
                'product_transaction_id' => $transaction->id,
                'reason' => 'produk_rusak',
                'description' => 'Barang diterima dalam kondisi tidak sesuai.',
                'status' => ProductReturn::STATUS_APPROVED,
                'admin_note' => 'Retur disetujui.',
            ]);

            $return->forceFill(['created_at' => $updatedAt, 'updated_at' => $updatedAt])->save();
        }
    }

    protected function trackingNumber(int $orderIndex): string
    {
        return 'WB' . str_pad((string) strtoupper(dechex(1000 + $orderIndex)), 6, '0', STR_PAD_LEFT);
    }

    protected function phoneNumber(int $buyerIndex, int $orderIndex): string
    {
        $prefix = ['0812', '0813', '0856', '0821', '0857', '0838', '0896', '0819', '0822'][($buyerIndex + $orderIndex) % 9];

        return $prefix . str_pad((string) str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT), 8, '0', STR_PAD_LEFT);
    }

    protected function seedCarts(iterable $buyersOrIds, iterable $products): void
    {
        foreach ($buyersOrIds as $userId) {
            if (is_object($userId)) {
                $userId = $userId->id;
            }

            if (Cart::where('user_id', $userId)->exists()) {
                continue;
            }

            foreach (collect($products)->take(4) as $product) {
                Cart::updateOrCreate(
                    ['user_id' => $userId, 'product_id' => $product->id],
                    ['quantity' => 1]
                );
            }
        }
    }
}