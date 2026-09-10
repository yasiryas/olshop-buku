<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTransaction extends Model
{
    //
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_SHIPPED => 'Dikirim',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public const STATUS_BADGE_COLORS = [
        self::STATUS_PENDING => 'bg-orange-500',
        self::STATUS_PROCESSING => 'bg-blue-500',
        self::STATUS_SHIPPED => 'bg-indigo-500',
        self::STATUS_COMPLETED => 'bg-green-500',
        self::STATUS_REJECTED => 'bg-red-500',
        self::STATUS_CANCELLED => 'bg-gray-500',
    ];

    protected $fillable = [
        'user_id',
        'total_amount',
        'is_paid',
        'status',
        'shipping_method',
        'shipping_cost',
        'payment_method',
        'tracking_number',
        'rejection_note',
        'address',
        'city',
        'post_code',
        'phone_number',
        'notes',
        'proof',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'product_transaction_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadgeColor(): string
    {
        return self::STATUS_BADGE_COLORS[$this->status] ?? 'bg-gray-500';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_COMPLETED]);
    }
}
