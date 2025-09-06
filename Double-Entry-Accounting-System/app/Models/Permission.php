<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Permission extends SpatiePermission
{
    use UsesTenantConnection;
}