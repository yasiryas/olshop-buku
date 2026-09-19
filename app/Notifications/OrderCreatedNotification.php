<?php

namespace App\Notifications;

use App\Models\ProductTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ProductTransaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pesanan Baru #'.$this->transaction->id)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Ada pesanan baru yang menunggu konfirmasi pembayaran.')
            ->line('Pembeli: '.$this->transaction->recipient_name.' ('.$this->transaction->phone_number.')')
            ->line('Total: '.rupiah($this->transaction->total_amount))
            ->action('Lihat Pesanan', route('product_transactions.show', $this->transaction->id));
    }

    public function toArray(object $notifiable): array
    {
        $url = route('product_transactions.show', $this->transaction->id);
        if ($notifiable->hasAnyRole(['owner', 'admin'])) {
            $url = route('product_transactions.preview', ['productTransaction' => $this->transaction->id]);
        }
        return [
            'message' => 'Pesanan baru #'.$this->transaction->id.' dari '.$this->transaction->recipient_name,
            'url' => $url,
            'order_id' => $this->transaction->id,
            'type' => 'order_created',
        ];
    }
}
