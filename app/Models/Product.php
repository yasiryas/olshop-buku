<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    //
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

    public function getStockAttribute()
    {
        $stockIn = $this->stockMutations()->where('type', 'in')->sum('quantity');
        $stockOut = $this->stockMutations()->where('type', 'out')->sum('quantity');
        return $stockIn - $stockOut;
    }
}
