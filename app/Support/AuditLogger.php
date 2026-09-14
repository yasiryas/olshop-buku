<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function log(string $action, mixed $target = null, array $extra = []): void
    {
        $context = array_merge($extra, [
            'user_id' => auth()->id(),
            'email' => auth()->user()->email ?? null,
        ]);

        if ($target !== null) {
            $context['target'] = $target instanceof Model
                ? get_class($target) . '#' . $target->getKey()
                : (string) $target;
        }

        Log::info("[audit] {$action}", $context);
    }

    public static function warn(string $action, array $extra = []): void
    {
        Log::warning("[audit] {$action}", $extra);
    }
}