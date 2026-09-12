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

    public static function agenWebApiKey(): string
    {
        return (string) self::get('agenweb_api_key', '');
    }

    public static function agenWebOriginCityId(): string
    {
        return (string) self::get('agenweb_origin_city_id', '');
    }

    public static function agenWebCityList(): array
    {
        $list = self::get('agenweb_city_list', []);

        return is_array($list) ? $list : [];
    }

    public static function agenWebConfigured(): bool
    {
        return self::agenWebApiKey() !== ''
            && self::agenWebOriginCityId() !== ''
            && !empty(self::agenWebCityList());
    }

    public static function lowStockThreshold(): int
    {
        return (int) self::get('low_stock_threshold', 5);
    }

    public static function shippingZones(): array
    {
        $zones = self::get('shipping_zones', []);

        return collect($zones)
            ->map(fn (array $zone) => [
                'city' => $zone['city'] ?? '',
                'costs' => (array) ($zone['costs'] ?? []),
            ])
            ->filter(fn (array $zone) => $zone['city'] !== '')
            ->values()
            ->all();
    }

    public static function shippingZoneCities(): array
    {
        return array_column(self::shippingZones(), 'city');
    }

    /**
     * Tarif per kurir untuk kota tertentu; fallback ke biaya flat (`cost`) bila kota tidak punya tarif khusus.
     */
    public static function shippingRatesFor(string $city): array
    {
        $zone = collect(self::shippingZones())->firstWhere('city', $city);
        $zoneCosts = $zone['costs'] ?? [];

        return collect(self::shippingRates())
            ->map(fn (array $rate) => [
                ...$rate,
                'cost' => array_key_exists($rate['code'], $zoneCosts) ? (int) $zoneCosts[$rate['code']] : $rate['cost'],
            ])
            ->all();
    }

    public static function invalidateCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget(self::CACHE_KEY);
    }
}