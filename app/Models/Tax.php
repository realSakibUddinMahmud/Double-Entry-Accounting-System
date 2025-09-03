<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Tax extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'rate',
        'status',
    ];
}
