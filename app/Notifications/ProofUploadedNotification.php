<?php

namespace App\Notifications;

use App\Models\ProductTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProofUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ProductTransaction $transaction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bukti Pembayaran Diunggah #' . $this->transaction->id)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Pembeli telah mengunggah bukti transfer untuk pesanan #' . $this->transaction->id . '.')
            ->line('Silakan verifikasi agar pesanan segera diproses.')
            ->action('Lihat Pesanan', route('product_transactions.preview', ['productTransaction' => $this->transaction->id]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Bukti pembayaran pesanan #' . $this->transaction->id . ' telah diunggah pembeli.',
            'url' => route('product_transactions.preview', ['productTransaction' => $this->transaction->id]),
            'order_id' => $this->transaction->id,
            'type' => 'proof_uploaded',
        ];
    }
}