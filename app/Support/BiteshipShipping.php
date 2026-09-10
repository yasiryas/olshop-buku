<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class BiteshipShipping
{
    public const API_URL = 'https://api.biteship.com/v1/rates/couriers';

    public const COURIERS = 'jne,jnt,sicepat,anteraja,grab-same-day,gojek-same-day';

    public const DEFAULT_WEIGHT_PER_ITEM = 250;

    public static function configured(): bool
    {
        return StoreSettings::biteshipConfigured();
    }

    public static function cartWeightGrams(iterable $cartItems): int
    {
        $weight = 0;

        foreach ($cartItems as $item) {
            $weight += (int) $item->quantity * self::DEFAULT_WEIGHT_PER_ITEM;
        }

        return $weight;
    }

    /**
     * Query tarif ongkir real-time. Kembalikan daftar tarif normalized,
     * atau array kosong bila belum dikonfigurasi / API gagal.
     */
    public static function rates(int $destinationPostalCode, int $totalWeightGrams): array
    {
        if (!self::configured() || $totalWeightGrams <= 0) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => StoreSettings::biteshipApiKey(),
                    'Content-Type' => 'application/json',
                ])
                ->post(self::API_URL, [
                    'origin_postal_code' => StoreSettings::biteshipOriginPostalCode(),
                    'destination_postal_code' => (string) $destinationPostalCode,
                    'couriers' => self::COURIERS,
                    'items' => [
                        [
                            'name' => 'Paket buku',
                            'value' => 10000,
                            'length' => 21,
                            'width' => 14,
                            'height' => 2,
                            'weight' => $totalWeightGrams,
                            'quantity' => 1,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json('pricing', []))
                ->map(fn (array $price, int $index) => [
                    'code' => 'biteship-' . ($index + 1),
                    'courier' => trim(($price['courier_company'] ?? '') . ' ' . ($price['courier_name'] ?? '')),
                    'service' => $price['courier_service_name'] ?? $price['courier_subservice_name'] ?? 'Reguler',
                    'cost' => (int) ($price['price'] ?? 0),
                    'eta' => $price['duration'] ?? '-',
                    'rate_id' => $price['rate_id'] ?? null,
                ])
                ->filter(fn (array $rate) => $rate['rate_id'] !== null)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}