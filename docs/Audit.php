<?php

namespace OwenIt\Auditing\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Audit as AuditContract;

class Audit extends Model implements AuditContract
{
    use \OwenIt\Auditing\Audit, UsesTenantConnection {
        // Resolve the collision:
        UsesTenantConnection::getConnectionName insteadof \OwenIt\Auditing\Audit;
        \OwenIt\Auditing\Audit::getConnectionName as getAuditConnectionName;
    }

    protected $guarded = [];

    public static $auditingGloballyDisabled = false;

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function getSerializedDate(\DateTimeInterface $date): string
    {
        return $this->serializeDate($date);
    }
}
