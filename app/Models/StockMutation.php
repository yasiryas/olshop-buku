<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
