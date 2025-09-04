<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomFieldValue extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'model_type',
        'model_id',
        'custom_field_id',
        'value',
    ];

    // Relationship to the CustomField definition
    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    // Polymorphic relation to the owning model (e.g., Product)
    public function model()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}