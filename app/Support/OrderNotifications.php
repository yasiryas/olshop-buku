<?php

namespace App\Support;

use App\Models\ProductTransaction;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ProofUploadedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderNotifications
{
    private static function notify($notifiable, Notification $notification, string $contextLog): void
    {
        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            Log::warning("Gagal kirim {$contextLog} (user {$notifiable->id}): {$e->getMessage()}");
        }
    }

    public static function orderCreated(ProductTransaction $transaction): void
    {
        foreach (User::role(['owner', 'admin'])->get() as $admin) {
            self::notify($admin, new OrderCreatedNotification($transaction), 'notifikasi pesanan baru');
        }
    }

    public static function proofUploaded(ProductTransaction $transaction): void
    {
        foreach (User::role(['owner', 'admin'])->get() as $admin) {
            self::notify($admin, new ProofUploadedNotification($transaction), 'notifikasi bukti pembayaran');
        }
    }

    public static function orderStatusChanged(ProductTransaction $transaction): void
    {
        if ($transaction->user) {
            self::notify($transaction->user, new OrderStatusChangedNotification($transaction), 'notifikasi status pesanan');
        }
    }
}