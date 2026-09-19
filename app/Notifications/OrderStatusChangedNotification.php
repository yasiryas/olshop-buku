<?php

namespace App\Notifications;

use App\Models\ProductTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusChangedNotification extends Notification
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
            ->subject('Status Pesanan #' . $this->transaction->id . ': ' . $this->transaction->statusLabel())
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Status pesanan Anda telah diperbarui menjadi:')
            ->line('**' . $this->transaction->statusLabel() . '**')
            ->when(
                $this->transaction->rejection_note,
                fn (MailMessage $msg) => $msg->line('Alasan: ' . $this->transaction->rejection_note)
            )
            ->action('Lihat Pesanan', route('product_transactions.show', $this->transaction->id))
            ->line('Terima kasih sudah berbelanja di Wigati Buku.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pesanan #' . $this->transaction->id . ' kini berstatus: ' . $this->transaction->statusLabel(),
            'url' => route('product_transactions.show', $this->transaction->id),
            'order_id' => $this->transaction->id,
            'type' => 'order_status_changed',
        ];
    }
}