<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class StockAdjustment extends Model
{
    use UsesTenantConnection;
    protected $fillable = [
        'store_id',
        'date',
        'note',
        'user_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productStockAdjustments()
    {
        return $this->hasMany(ProductStockAdjustment::class);
    }
}
