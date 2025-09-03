<?php

namespace Hilinkz\DEAccounting\Models;

use OwenIt\Auditing\Auditable;
use Kalnoy\Nestedset\NodeTrait;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountType;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeTask extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'tasks';
    protected $fillable = [
        'company_id',
        'name',
        'taskable_id',
        'taskable_type',
        'note',
    ];
    public function taskable()
    {
        return $this->morphTo();
    }
    public function journals()
    {
        return $this->hasMany(DeJournal::class, 'task_id');
    }
}
