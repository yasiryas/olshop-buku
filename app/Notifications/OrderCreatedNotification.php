<?php

namespace App\Notifications;

use App\Models\ProductTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreatedNotification extends Notification
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
            ->subject('Pesanan Baru #' . $this->transaction->id)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada pesanan baru yang menunggu konfirmasi pembayaran.')
            ->line('Pembeli: ' . $this->transaction->recipient_name . ' (' . $this->transaction->phone_number . ')')
            ->line('Total: Rp ' . number_format($this->transaction->total_amount))
            ->action('Lihat Pesanan', route('product_transactions.show', $this->transaction->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pesanan baru #' . $this->transaction->id . ' dari ' . $this->transaction->recipient_name,
            'url' => route('product_transactions.show', $this->transaction->id),
        ];
    }
}