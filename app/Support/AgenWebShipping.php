<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
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
     *
     * $destinationPostal opsional: kode pos kecamatan milik pembeli untuk resolusi
     * lokasi yang akurat. Bila kosong, dicari dari nama kota tujuan.
     *
     * Hasil sukses di-cache 15 menit dan di-retry maks. 3× (API kerap 502 courier_upstream_error),
     * sehingga saat upstream sedang flaky pembeli tetap mendapat tarif terakhir yang valid.
     */
    public static function rates(int $destinationCityId, int $totalWeightGrams, string $destinationPostal = ''): array
    {
        if (!self::configured() || $totalWeightGrams <= 0 || ($destinationCityId <= 0 && $destinationPostal === '')) {
            return [];
        }

        $shipper = self::locationFor(self::originPostal(), self::originCityName());
        $destination = $destinationPostal !== ''
            ? self::locationFor($destinationPostal, '')
            : self::locationFor('', self::cityName((string) $destinationCityId));

        if ($shipper === null || $destination === null) {
            return [];
        }

        $weightBucket = max(250, (int) ceil($totalWeightGrams / 250) * 250);
        $cacheKey = 'agenweb_rates_' . md5(serialize([$shipper, $destination, $weightBucket]));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $rates = [];
        foreach ([0, 1, 2] as $attempt) {
            $rates = self::fetchRates($shipper, $destination, $totalWeightGrams);

            if ($rates !== []) {
                break;
            }

            if ($attempt < 2) {
                usleep(400000 * ($attempt + 1));
            }
        }

        if ($rates !== []) {
            Cache::put($cacheKey, $rates, now()->addMinutes(15));
        }

        return $rates;
    }

    private static function fetchRates(array $shipper, array $destination, int $weightGrams): array
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'x-api-key' => StoreSettings::agenWebApiKey(),
                    'Accept' => 'application/json',
                ])
                ->asJson()
                ->post(self::API_BASE . '/rates', [
                    'shipper' => $shipper,
                    'destination' => $destination,
                    'weight' => $weightGrams,
                    'couriers' => self::COURIERS,
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

    /**
     * Resolusi lokasi ke {province, city, district} untuk payload /rates.
     * Kode pos memberi hasil presisi; nama kota sebagai fallback (cache 30 hari).
     */
    private static function locationFor(string $postal, string $cityName): ?array
    {
        if ($postal !== '') {
            $row = Cache::remember(
                "agenweb_location_{$postal}",
                now()->addDays(30),
                fn () => self::searchFirst(['q' => $postal])
            );
        } else {
            $row = Cache::remember(
                "agenweb_location_city_{$cityName}",
                now()->addDays(30),
                fn () => self::searchCityFirst($cityName)
            );
        }

        if (!is_array($row) || $row === []) {
            return null;
        }

        return [
            'province' => $row['province'] ?? '',
            'city' => $row['city'] ?? '',
            'district' => $row['district'] ?? '',
        ];
    }

    /**
     * Saran kecamatan untuk autocomplete checkout (cache 30 hari).
     * Hasil di-dedupe per district+postal dan tetap membawa provinsi/kota/kode pos
     * agar form tidak perlu query lagi saat pembeli memilih.
     */
    public static function districtSuggestions(string $query): array
    {
        $query = trim($query);

        if ($query === '' || mb_strlen($query) < 2) {
            return [];
        }

        $rows = Cache::remember(
            'agenweb_location_suggest_' . md5($query),
            now()->addDays(30),
            fn () => self::searchLocations($query)
        );

        $suggestions = [];
        $seen = [];

        foreach ($rows as $row) {
            $district = trim((string) ($row['district'] ?? ''));
            $postal = trim((string) ($row['postal_code'] ?? ''));

            if ($district === '' || $postal === '') {
                continue;
            }

            $dedupeKey = $district . '|' . $postal;

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $suggestions[] = [
                'province' => trim((string) ($row['province'] ?? '')),
                'city' => trim((string) ($row['city'] ?? '')),
                'district' => $district,
                'postal_code' => $postal,
            ];

            if (count($suggestions) >= 15) {
                break;
            }
        }

        return $suggestions;
    }

    private static function searchLocations(string $query): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => StoreSettings::agenWebApiKey(),
                    'Accept' => 'application/json',
                ])
                ->get(self::API_BASE . '/locations/search', ['q' => $query, 'limit' => 40]);

            if (!$response->successful()) {
                return [];
            }

            return $response->json('data', []) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function searchFirst(array $query): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => StoreSettings::agenWebApiKey(),
                    'Accept' => 'application/json',
                ])
                ->get(self::API_BASE . '/locations/search', [...$query, 'limit' => 1]);

            if (!$response->successful()) {
                return [];
            }

            return $response->json('data.0', []) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Cari lokasi per kota; preferensi baris yang kotanya sama (hindari kecocokan samar).
     */
    private static function searchCityFirst(string $cityName): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => StoreSettings::agenWebApiKey(),
                    'Accept' => 'application/json',
                ])
                ->get(self::API_BASE . '/locations/search', ['q' => $cityName, 'limit' => 50]);

            if (!$response->successful()) {
                return [];
            }

            $rows = $response->json('data', []) ?? [];

            foreach ($rows as $row) {
                if (strcasecmp($row['city'] ?? '', $cityName) === 0) {
                    return $row;
                }
            }

            return $rows[0] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Kode pos asal toko (setting) atau hasil pencarian lokasi jika tidak diisi.
     */
    private static function originPostal(): string
    {
        $configuredOrigin = StoreSettings::agenWebOriginPostalCode();

        if ($configuredOrigin !== '') {
            return $configuredOrigin;
        }

        return self::originCityName() === '' ? '' : self::originPostalViaCity();
    }

    private static function originCityName(): string
    {
        return self::cityName(StoreSettings::agenWebOriginCityId());
    }

    private static function cityName(string $cityId): string
    {
        $city = collect(StoreSettings::agenWebCityList())->firstWhere('city_id', $cityId);

        return $city['city_name'] ?? '';
    }

    private static function originPostalViaCity(): string
    {
        $row = self::searchCityFirst(self::originCityName());

        return $row['postal_code'] ?? '';
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
}