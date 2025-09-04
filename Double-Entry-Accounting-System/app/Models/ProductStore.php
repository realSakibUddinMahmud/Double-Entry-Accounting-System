<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class ProductStore extends Model implements AuditableContract
{
    use UsesTenantConnection, Auditable;

    protected $table = 'product_store';

    protected $fillable = [
        'store_id',
        'product_id',
        'base_unit_id',
        'purchase_unit_id',
        'sales_unit_id',
        'purchase_cost',
        'cogs',
        'sales_price',
        'status',
        'tax_id',
        'tax_method',
    ];

    /**
     * Audit configuration
     */
    protected $auditInclude = [
        'store_id',
        'product_id',
        'base_unit_id',
        'purchase_unit_id',
        'sales_unit_id',
        'purchase_cost',
        'cogs',
        'sales_price',
        'status',
        'tax_id',
        'tax_method',
    ];

    protected $auditTimestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    public function base_unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'base_unit_id');
    }

    public function purchase_unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'purchase_unit_id');
    }

    public function sales_unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'sales_unit_id');
    }
}