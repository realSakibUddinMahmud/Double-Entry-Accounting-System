<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccountType;

use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeBank extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'banks';

}
