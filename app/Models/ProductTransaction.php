<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTransaction extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'is_paid',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'product_transaction_id');
    }
}
