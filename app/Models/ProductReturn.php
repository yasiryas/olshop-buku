<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_REQUESTED => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    public const STATUS_BADGE_COLORS = [
        self::STATUS_REQUESTED => 'bg-orange-500',
        self::STATUS_APPROVED => 'bg-green-500',
        self::STATUS_REJECTED => 'bg-red-500',
    ];

    protected $fillable = [
        'product_transaction_id',
        'reason',
        'description',
        'status',
        'admin_note',
    ];

    public function transaction()
    {
        return $this->belongsTo(ProductTransaction::class, 'product_transaction_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadgeColor(): string
    {
        return self::STATUS_BADGE_COLORS[$this->status] ?? 'bg-gray-500';
    }
}