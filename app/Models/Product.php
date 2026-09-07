<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'photo',
        'price',
        'about',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function scopeWithStock(Builder $query): Builder
    {
        return $query
            ->withSum(['stockMutations as stock_in' => fn ($q) => $q->where('type', 'in')], 'quantity')
            ->withSum(['stockMutations as stock_out' => fn ($q) => $q->where('type', 'out')], 'quantity');
    }

    public function getStockAttribute(): int
    {
        if (array_key_exists('stock_in', $this->attributes) && array_key_exists('stock_out', $this->attributes)) {
            return (int) $this->attributes['stock_in'] - (int) $this->attributes['stock_out'];
        }

        $stockIn = $this->stockMutations()->where('type', 'in')->sum('quantity');
        $stockOut = $this->stockMutations()->where('type', 'out')->sum('quantity');

        return $stockIn - $stockOut;
    }
}
