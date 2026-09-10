<?php

namespace App\Support;

use App\Models\Setting;

class StoreSettings
{
    private const CACHE_KEY = 'store_settings';

    public static function all(): array
    {
        return \Illuminate\Support\Facades\Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function shippingRates(): array
    {
        $rates = self::get('shipping_methods', []);

        return collect($rates)->map(fn (array $rate, int $index) => [
            'code' => $rate['code'] ?? 'courier-' . ($index + 1),
            'courier' => $rate['courier'] ?? 'Kurir',
            'cost' => (int) ($rate['cost'] ?? 0),
            'eta' => $rate['eta'] ?? '-',
        ])->all();
    }

    public static function paymentMethods(): array
    {
        $methods = self::get('payment_methods', []);

        return collect($methods)->filter(fn (array $method) => $method['active'] ?? true)->map(fn ($method) => [
            'code' => $method['code'] ?? 'method-' . uniqid(),
            'name' => $method['name'] ?? 'Metode Pembayaran',
            'acc_number' => $method['acc_number'] ?? '',
            'acc_name' => $method['acc_name'] ?? '',
        ])->values()->all();
    }

    public static function waContact(): string
    {
        return self::get('wa_contact', '6285713878266');
    }

    public static function invalidateCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget(self::CACHE_KEY);
    }
}