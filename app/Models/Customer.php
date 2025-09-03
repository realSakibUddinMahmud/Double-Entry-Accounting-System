<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Hilinkz\DEAccounting\Models\DeAccount;
use Illuminate\Validation\ValidationException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Customer extends Model implements AuditableContract
{
    use HasFactory, UsesTenantConnection, Auditable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalDueAttribute()
    {
        return $this->sales()->sum('due_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->sales()->sum('paid_amount');
    }

    public function getTotalAmountAttribute()
    {
        return $this->sales()->sum('total_amount');
    }

    /**
     * Booted model event for Customer.
     * Creates default accounts on customer creation.
     */
    protected static function booted()
    {
        static::creating(function ($customer) {
            $user = auth()->user();
            DeAccount::fixTree();

            // Get class_id for accountable_type
            $classId = null;
            foreach (DeAccount::$accountables as $item) {
                if ($item['class'] === self::class) {
                    $classId = $item['class_id'];
                    break;
                }
            }

            $assetsAccount = DeAccount::where('title', 'Assets')
                ->where('accountable_id', $user->tenant_id)
                ->where('accountable_type', 1) // 1 = Company
                ->where('root_type', '1') // 1 = Assets
                ->whereNull('parent_id')
                ->where('status', 'ACTIVE')
                ->first();

            // If Assets account not found, terminate customer creation with validation error
            if (!$assetsAccount) {
                throw ValidationException::withMessages([
                    'assets_account' => 'Assets account not found. Cannot create customer. Please contact administrator.',
                ]);
            }
        });

        static::created(function ($customer) {
            $user = auth()->user();

            // Get class_id for accountable_type
            $classId = null;
            foreach (DeAccount::$accountables as $item) {
                if ($item['class'] === self::class) {
                    $classId = $item['class_id'];
                    break;
                }
            }

            $assetsAccount = DeAccount::where('title', 'Assets')
                ->where('accountable_id', $user->tenant_id)
                ->where('accountable_type', 1) // 1 = Company
                ->where('root_type', '1') // 1 = Assets
                ->whereNull('parent_id')
                ->where('status', 'ACTIVE')
                ->first();

            // This check is redundant due to the creating event, but kept for safety
            if ($assetsAccount) {
                DeAccount::create([
                    'company_id' => $user->tenant_id,
                    'account_no' => '1003'. $customer->id, // Default account number for customer
                    'title' => $customer->name . ' Receivable',
                    'account_type_id' => 1, // 1 = Default
                    'accountable_type' => $classId,
                    'accountable_id' => $customer->id,
                    'created_by' => auth()->id(),
                    'root_type' => '1', // 1 = Assets
                    'status' => 'ACTIVE',
                    'parent_id' => $assetsAccount->id,
                ]);
            }
        });
    }

    public function accounts()
    {
        // 'accountable_type' is stored as class_id (e.g., 5 for Store)
        $classId = null;
        foreach (DeAccount::$accountables as $item) {
            if ($item['class'] === self::class) {
                $classId = $item['class_id'];
                break;
            }
        }
        if (!$classId) {
            return collect();
        }

        return DeAccount::where('accountable_id', $this->id)
            ->where('accountable_type', $classId);
    }
}