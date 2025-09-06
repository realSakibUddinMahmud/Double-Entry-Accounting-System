<?php

namespace App\Models\View;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CogsAvgView extends Model
{
    use UsesTenantConnection;

    protected $table = 'store_product_current_cogs_avg';

    // This view does not have timestamps
    public $timestamps = false;

    // If you want to allow mass assignment for all columns
    protected $guarded = [];
}