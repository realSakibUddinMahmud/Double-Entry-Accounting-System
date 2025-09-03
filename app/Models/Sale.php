<?php

namespace App\Models;

use Hilinkz\DEAccounting\Models\DeTask;
use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeJournal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Sale extends Model implements AuditableContract
{
    use HasFactory, UsesTenantConnection, Auditable;

    protected $fillable = [
        'u_id',
        'store_id',
        'customer_id',
        'sale_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'total_price',
        'shipping_cost',
        'discount_amount',
        'tax_id',
        'total_tax',
        'status',
        'payment_status',
        'user_id',
        'note',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(SaleItem::class);
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