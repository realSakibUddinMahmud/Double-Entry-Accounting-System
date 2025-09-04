<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeBank;
use Hilinkz\DEAccounting\Models\DeAccount;

use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeBankAccount extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'bank_accounts';

    public function bank()
    {
        return $this->belongsTo(DeBank::class);
    }
    public function account()
    {
        return $this->belongsTo(DeAccount::class);
    }
}
