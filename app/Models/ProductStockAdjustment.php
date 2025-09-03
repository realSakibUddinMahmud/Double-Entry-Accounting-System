<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ProductStockAdjustment extends Model
{
    use UsesTenantConnection;
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'action',
        'quantity',
        'user_id',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
