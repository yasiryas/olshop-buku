<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class AgenWebShipping
{
    public const COURIERS = ['jnt', 'lion', 'sap', 'spx', 'jtc'];

    public const API_BASE = 'https://api.agenwebsite.com/v1';

    public const DEFAULT_WEIGHT_PER_ITEM = 250;

    public static function configured(): bool
    {
        return StoreSettings::agenWebConfigured();
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
     * Tarif pengiriman real-time dari kota asal (toko) ke kota tujuan.
     * Dipanggil per kota tujuan (1 request = 1 kuota).
     * Kembalikan daftar tarif normalized, atau array kosong bila gagal / API.
     */
    public static function rates(int $destinationCityId, int $totalWeightGrams): array
    {
        if (!self::configured() || $destinationCityId <= 0 || $totalWeightGrams <= 0) {
            return [];
        }

        $originPostal = self::postalCodeFor(StoreSettings::agenWebOriginCityId());
        $destPostal = self::postalCodeFor((string) $destinationCityId);

        if ($originPostal === '' || $destPostal === '') {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => StoreSettings::agenWebApiKey(),
                    'Accept' => 'application/json',
                ])
                ->asJson()
                ->post(self::API_BASE . '/rates', [
                    'shipper' => ['postal_code' => $originPostal],
                    'destination' => ['postal_code' => $destPostal],
                    'weight' => $totalWeightGrams,
                    'sort' => 'cheapest',
                ]);

            if (!$response->successful()) {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }

        $rates = [];

        foreach (self::normalizedRates($response->json('data.rates', []) ?? []) as $row) {
            if ($row['discounted_cost'] <= 0) {
                continue;
            }

            $rates[] = [
                'code' => 'agenweb-' . count($rates),
                'courier' => $row['courier_name'],
                'service' => $row['service_name'],
                'cost' => $row['discounted_cost'],
                'eta' => $row['etd_text'] ?: '-',
                'rate_id' => "agenweb-{$row['service_code']}",
            ];
        }

        return $rates;
    }

    private static function normalizedRates(array $raw): array
    {
        return array_map(fn (array $rate) => [
            'courier_name' => $rate['courier_name'] ?? 'Kurir',
            'service_name' => $rate['service_name'] ?? 'Reguler',
            'service_code' => $rate['service_code'] ?? '',
            'discounted_cost' => (int) ($rate['discounted_cost'] ?? $rate['cost'] ?? 0),
            'etd_text' => $rate['etd_text'] ?? '',
        ], $raw);
    }

    private static function postalCodeFor(string $cityId): string
    {
        $city = collect(StoreSettings::agenWebCityList())->firstWhere('city_id', $cityId);

        return $city['postal_code'] ?? '';
    }
}