<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class WaNotifier
{
    public static function phoneToWa(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }

    public static function url(string $phone, string $message): string
    {
        return 'https://wa.me/' . self::phoneToWa($phone) . '?text=' . rawurlencode($message);
    }

    /**
     * Kirim notifikasi. Mode `api` + URL terisi => kirim ke gateway; gagal/`manual` => fallback deep-link.
     * Mengembalikan tautan wa.me (string kosong bila berhasil terkirim via API).
     */
    public static function send(string $phone, string $message): string
    {
        if (StoreSettings::waMode() === 'api' && StoreSettings::waApiUrl() !== '') {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . StoreSettings::waApiToken(),
                        'Accept' => 'application/json',
                    ])
                    ->asForm()
                    ->post(StoreSettings::waApiUrl(), [
                        'phone' => self::phoneToWa($phone),
                        'message' => $message,
                    ]);

                if ($response->successful()) {
                    return '';
                }
            } catch (\Throwable $e) {
                // gagal menuju gateway, fallback ke deep-link
            }
        }

        return self::url($phone, $message);
    }
}