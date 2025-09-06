<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomField extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'model_type',
        'name',
        'label',
        'type',
        'options',
    ];

    // Remove the casts for options
    // Add an accessor for options as array
    public function getOptionsArrayAttribute()
    {
        return $this->options ? array_map('trim', explode(',', $this->options)) : [];
    }

    public function customFieldValues()
    {
        return $this->hasMany(\App\Models\CustomFieldValue::class);
    }
}