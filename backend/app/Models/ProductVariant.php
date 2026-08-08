<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'option_summary',
        'price',
        'stock_qty',
        'weight_grams',
    ];

    protected function casts(): array
    {
        return [
            'option_summary' => 'array',
            'price' => 'decimal:2',
            'stock_qty' => 'integer',
            'weight_grams' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
