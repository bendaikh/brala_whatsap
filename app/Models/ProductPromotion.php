<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model
{
    protected $fillable = [
        'product_id',
        'product_variation_id',
        'label',
        'min_quantity',
        'max_quantity',
        'price',
        'compare_at_price',
        'promotion_type',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function getQuantityRangeAttribute()
    {
        if ($this->max_quantity) {
            return $this->min_quantity . ' - ' . $this->max_quantity;
        }
        return $this->min_quantity;
    }

    public function getDiscountPercentageAttribute()
    {
        $compareAt = $this->compare_at_price;
        if ($compareAt && $compareAt > $this->price) {
            return round((($compareAt - $this->price) / $compareAt) * 100);
        }

        $basePrice = $this->variation ? $this->variation->price : ($this->product->compare_at_price ?: $this->product->price);

        if ($basePrice && $basePrice > $this->price) {
            return round((($basePrice - $this->price) / $basePrice) * 100);
        }
        return 0;
    }
}
