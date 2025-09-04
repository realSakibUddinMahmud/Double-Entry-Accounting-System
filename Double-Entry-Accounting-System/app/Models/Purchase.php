<?php

namespace App\Models;

use Hilinkz\DEAccounting\Models\DeTask;
use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeJournal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Purchase extends Model implements AuditableContract
{
    use HasFactory, UsesTenantConnection, Auditable;

    protected $fillable = [
        'supplier_id',
        'store_id',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'total_cost',
        'shipping_cost',
        'discount_amount',
        'tax_id',
        'total_tax',
        'status',
        'payment_status',
        'user_id',
        'u_id',
        'note',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function journals()
    {
        return $this->morphMany(DeJournal::class, 'journalable');
    }
    public function tasks()
    {
        return $this->morphMany(DeTask::class, 'taskable');
    }
}