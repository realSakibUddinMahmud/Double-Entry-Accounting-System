<?php

namespace App\Models;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class PurchaseItem extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'tax_id',
        'tax_amount',
        'discount_amount',
        'quantity',
        'per_unit_cost',
        'per_unit_cogs',
        'total',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productStore()
    {
        return $this->belongsTo(ProductStore::class, 'product_id', 'product_id')
            ->where('store_id', $this->purchase->store_id ?? null);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}