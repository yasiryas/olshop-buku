<?php

namespace App\Support;

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
}