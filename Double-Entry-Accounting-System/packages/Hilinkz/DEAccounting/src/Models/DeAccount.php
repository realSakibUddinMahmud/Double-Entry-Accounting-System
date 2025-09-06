<?php

namespace Hilinkz\DEAccounting\Models;

use OwenIt\Auditing\Auditable;
use Kalnoy\Nestedset\NodeTrait;
use Hilinkz\DEAccounting\Models\DeBank;
use Illuminate\Database\Eloquent\Model;

use Hilinkz\DEAccounting\Models\DeAccountType;
use Hilinkz\DEAccounting\Models\DeBankAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeAccount extends Model implements AuditableContract
{
    use NodeTrait;
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'accounts';

    // Add fillable fields as per your schema
    protected $fillable = [
        'company_id',
        'account_no',
        'title',
        'account_type_id',
        'accountable_type',
        'accountable_id',
        'created_by',
        'status',
        '_lft',
        '_rgt',
        'parent_id',
        'root_type',
        'financial_statement_placement',
    ];

    public static $accountables = [
        [
            'alias' => 'User',
            'class' => \App\Models\User::class,
            'class_id' => 4,
        ],
        [
            'alias' => 'Customer',
            'class' => \App\Models\Customer::class,
            'class_id' => 2,
        ],
        [
            'alias' => 'Company',
            'class' => \App\Models\Company::class,
            'class_id' => 1,
        ],
        [
            'alias' => 'Supplier',
            'class' => \App\Models\Supplier::class,
            'class_id' => 3,
        ],
        [
            'alias' => 'Store',
            'class' => \App\Models\Store::class,
            'class_id' => 5,
        ],
    ];

    public static $rootTypes = [
            [
                'id' => 1,
                'name' => 'Assets',
            ],
            [
                'id' => 2,
                'name' => 'Expenses',
            ],
            [
                'id' => 3,
                'name' => 'Liabilities',
            ],
            [
                'id' => 4,
                'name' => 'Income',
            ],
            [
                'id' => 5,
                'name' => 'Capital',
            ],
        ];

    // Add any relationships if needed
    public function accountType()
    {
        return $this->belongsTo(DeAccountType::class, 'account_type_id');
    }

    public function accountable()
    {
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->accountable_type) {
                return $this->hasOne($item['class'], 'id', 'accountable_id');
            }
        }

        return $this->morphTo();
    }
    public function getAccountableAliasAttribute()
    {
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->accountable_type) {
                return $item['alias'];
            }
        }

        return 'Unknown';
    }
    public function getAccountableAttribute()
    {
        foreach (self::$accountables as $item) {
            if ($item['class_id'] == $this->accountable_type) {
                return $item['class']::find($this->accountable_id);
            }
        }
        return null;
    }


    public function bank()
    {
        return $this->belongsTo(DeBank::class);
    }
    public function bankAccount()
    {
        // Map accounts.id (local key) with bank_accounts.account_id (foreign key)
        return $this->hasOne(DeBankAccount::class, 'account_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function transactions()
    {
        return $this->hasMany(DeAccountTransaction::class, 'account_id');
    }

    public static function headTypeCheck($account_id = NULL, $upDown = NULL)
    {
        $account = DeAccount::withoutGlobalScopes()->where('id', $account_id)->first();

        if (!$account) {
            return 'Account headType check, Id Not Found: ' . $account_id;
        }

        $root_type = $account->root_type;

        $headType = NULL;

        if ($upDown === 'INCREASE') {
            switch ($root_type) {
                case 1: // Assets
                case 2: // Expenses
                    $headType = "DEBIT";
                    break;
                case 3: // Liabilities
                case 4: // Income
                case 5: // Capital
                    $headType = "CREDIT";
                    break;
                default:
                    $headType = 'AC Not Found in INCREASE account_id ' . $account_id;
            }
        } elseif ($upDown === 'DECREASE') {
            switch ($root_type) {
                case 1: // Assets
                case 2: // Expenses
                    $headType = "CREDIT";
                    break;
                case 3: // Liabilities
                case 4: // Income
                case 5: // Capital
                    $headType = "DEBIT";
                    break;
                default:
                    $headType = 'AC Not Found in DECREASE for acc_id ' . $account_id;
            }
        } else {
            $headType = 'AC Not Found In Else';
        }

        return $headType;
    }
}
