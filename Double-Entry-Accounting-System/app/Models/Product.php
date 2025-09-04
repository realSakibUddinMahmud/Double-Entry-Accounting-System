<?php

namespace App\Models;

use App\Models\View\CogsAvgView;
use Illuminate\Database\Eloquent\Model;
use App\Models\View\ProductStoreStockView;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Product extends Model implements AuditableContract
{
    use UsesTenantConnection, Auditable;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'description',
        'sku',
        'barcode',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productStores()
    {
        return $this->hasMany(ProductStore::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // Add relation for custom field values
    public function customFieldValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'model_id')
            ->where('model_type', self::class);
    }

    // Relation to PurchaseItem
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Relation to Purchases through PurchaseItem
    public function purchases()
    {
        return $this->belongsToMany(Purchase::class, 'purchase_items');
    }

    /**
     * Get all stock view rows for this product (across all stores).
     */
    public function productStoreStockViews()
    {
        return $this->hasMany(ProductStoreStockView::class, 'product_id', 'id');
    }

    /**
     * Get the stock view row for this product in a specific store.
     */
    public function productStoreStockView($storeId)
    {
        return $this->hasOne(ProductStoreStockView::class, 'product_id', 'id')
            ->where('store_id', $storeId);
    }
    public function cogsAvgView($storeId)
    {
        return $this->hasOne(CogsAvgView::class, 'product_id', 'id')
            ->where('store_id', $storeId);
    }
}
