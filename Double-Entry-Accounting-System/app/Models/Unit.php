<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Unit extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'symbol',
        'parent_id',
        'conversion_factor',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }
}