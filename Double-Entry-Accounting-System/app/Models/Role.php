<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Role extends SpatieRole
{
    use UsesTenantConnection;
}