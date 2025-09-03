<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccount;

use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeAccountType extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'account_types';

    // Add any relationships if needed
    public function accounts()
    {
        return $this->hasMany(DeAccount::class);
    }
}
