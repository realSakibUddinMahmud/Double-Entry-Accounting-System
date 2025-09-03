<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Image extends Model
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'path',
        'imageable_type',
        'imageable_id',
    ];

    /**
     * Get the parent imageable model (product, etc).
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}