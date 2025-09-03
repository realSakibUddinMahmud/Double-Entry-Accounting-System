<?php

namespace App\Models;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\ProductStore;
use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Store extends Model implements AuditableContract
{
    use HasFactory, UsesTenantConnection, Auditable;

    protected $fillable = [
        'name',
        'address',
        'status',
        'contact_no',
    ];

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
    
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function products()
    {
        // If you want to get all products related to this store via the product_store pivot table:
        return $this->belongsToMany(
            Product::class,
            'product_store', // pivot table name
            'store_id',      // foreign key on pivot table for this model
            'product_id'     // foreign key on pivot table for related model
        )
        ->withPivot([
            'id',
            'base_unit_id',
            'purchase_unit_id',
            'sales_unit_id',
            'purchase_cost',
            'sales_price',
            'status',
            'tax_id',
            'tax_method',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * Booted model event for Store.
     * Creates default accounts on store creation.
     */
    protected static function booted()
    {
        static::created(function ($store) {
            DeAccount::fixTree();

            // Get class_id for accountable_type
            $classId = null;
            foreach (DeAccount::$accountables as $item) {
                if ($item['class'] === self::class) {
                    $classId = $item['class_id'];
                    break;
                }
            }

            // 1. Create root accounts first and store their IDs
            $rootAccounts = [
                'Assets'      => ['root_type' => '1', 'account_no' => '1'. $store->id.'00'],
                'Liabilities' => ['root_type' => '3', 'account_no' => '3'. $store->id.'00'],
                'Income'      => ['root_type' => '4', 'account_no' => '4'. $store->id.'00'],
                'Expense'     => ['root_type' => '2', 'account_no' => '2'. $store->id.'00'],
                'Capital'     => ['root_type' => '5', 'account_no' => '5'. $store->id.'00'],
            ];

            $rootIds = [];
            foreach ($rootAccounts as $title => $info) {
                $account = DeAccount::create([
                    'company_id' => $store->company_id ?? null,
                    'account_no' => $info['account_no'],
                    'title' => $title,
                    'account_type_id' => 1, // default account type
                    'accountable_type' => $classId,
                    'accountable_id' => $store->id,
                    'created_by' => auth()->id(),
                    'root_type' => $info['root_type'],
                    'status' => 'ACTIVE',
                    'parent_id' => null,
                ]);
                $rootIds[$title] = $account->id;
            }

            // 2. Create sub-accounts under the correct root/parent
            $subAccounts = [
                [
                    'title' => 'Cash',
                    'account_no' => '1'.$store->id.'01',
                    'root_type' => '1', // Assets
                    'parent_id' => $rootIds['Assets'],
                    'account_type_id' => 2, // receivable account type
                ],
                [
                    'title' => 'Inventory',
                    'account_no' => '1'.$store->id.'02',
                    'root_type' => '1', // Assets
                    'parent_id' => $rootIds['Assets'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Cost of Goods Sold',
                    'account_no' => '2'.$store->id.'01',
                    'root_type' => '2', // Expense
                    'parent_id' => $rootIds['Expense'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Carriage Inwards',
                    'account_no' => '2' . $store->id . '02',
                    'root_type' => '2', // Expense
                    'parent_id' => $rootIds['Expense'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Sales Revenue',
                    'account_no' => '4'.$store->id.'01',
                    'root_type' => '4', // Income
                    'parent_id' => $rootIds['Income'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Transportation Payable',
                    'account_no' => '3' . $store->id . '01',
                    'root_type' => '3', // Liabilities
                    'parent_id' => $rootIds['Liabilities'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Discount Received',
                    'account_no' => '4' . $store->id . '01',
                    'root_type' => '4', // Income
                    'parent_id' => $rootIds['Income'],
                    'account_type_id' => 8,
                ],


            ];

            foreach ($subAccounts as $account) {
                DeAccount::create([
                    'company_id' => $store->company_id ?? null,
                    'account_no' => $account['account_no'],
                    'title' => $account['title'],
                    'account_type_id' => $account['account_type_id'],
                    'accountable_type' => $classId,
                    'accountable_id' => $store->id,
                    'created_by' => auth()->id(),
                    'root_type' => $account['root_type'],
                    'status' => 'ACTIVE',
                    'parent_id' => $account['parent_id'],
                ]);
            }
        });
    }
}