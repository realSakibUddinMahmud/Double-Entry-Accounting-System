<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Hilinkz\DEAccounting\Models\DeAccount;
use Illuminate\Validation\ValidationException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Supplier extends Model implements AuditableContract
{
    use HasFactory, UsesTenantConnection, Auditable;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getTotalDueAttribute()
    {
        return $this->purchases()->sum('due_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->purchases()->sum('paid_amount');
    }

    public function getTotalAmountAttribute()
    {
        return $this->purchases()->sum('total_amount');
    }

    /**
     * Booted model event for Supplier.
     * Creates default payable account on supplier creation.
     */
    protected static function booted()
    {
        static::creating(function ($supplier) {
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

            // Find the root Liabilities account for this company/tenant
            $liabilitiesAccount = DeAccount::where('title', 'Liabilities')
                ->where('accountable_id', $user->tenant_id)
                ->where('accountable_type', 1) // 1 = Company
                ->where('root_type', '3') // 3 = Liabilities
                ->whereNull('parent_id')
                ->where('status', 'ACTIVE')
                ->first();

            // If Liabilities account not found, terminate supplier creation with validation error
            if (!$liabilitiesAccount) {
                throw ValidationException::withMessages([
                    'liabilities_account' => 'Liabilities account not found. Cannot create supplier. Please contact administrator.',
                ]);
            }
        });

        static::created(function ($supplier) {
            $user = auth()->user();

            // Get class_id for accountable_type
            $classId = null;
            foreach (DeAccount::$accountables as $item) {
                if ($item['class'] === self::class) {
                    $classId = $item['class_id'];
                    break;
                }
            }

            $liabilitiesAccount = DeAccount::where('title', 'Liabilities')
                ->where('accountable_id', $user->tenant_id)
                ->where('accountable_type', 1) // 1 = Company
                ->where('root_type', '3') // 3 = Liabilities
                ->whereNull('parent_id')
                ->where('status', 'ACTIVE')
                ->first();

            if ($liabilitiesAccount) {
                DeAccount::create([
                    'company_id' => $user->tenant_id,
                    'account_no' => '3001'. $supplier->id,
                    'title' => $supplier->name . ' Payable',
                    'account_type_id' => 1, // 1 = Default
                    'accountable_type' => $classId,
                    'accountable_id' => $supplier->id,
                    'created_by' => auth()->id(),
                    'root_type' => '3', // 3 = Liabilities
                    'status' => 'ACTIVE',
                    'parent_id' => $liabilitiesAccount->id,
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