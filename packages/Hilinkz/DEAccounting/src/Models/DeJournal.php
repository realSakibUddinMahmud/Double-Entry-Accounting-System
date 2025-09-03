<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeFile;
use App\Scopes\CompanyScope;
use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeJournal extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'de_journals';

    public function creditTransaction()
    {
        return $this->belongsTo(DeAccountTransaction::class, 'credit_transaction_id');
    }
    public function debitTransaction()
    {
        return $this->belongsTo(DeAccountTransaction::class, 'debit_transaction_id');   
    }
    public function journalable()
    {
        // First check if the journalable_type exists in DeAccount::$accountables
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->journalable_type) {
                return $this->hasOne($item['class'], 'id', 'journalable_id');
            }
        }

        // If not found in accountables, use regular morphTo relationship
        return $this->morphTo();
    }
    public function getJournalableAliasAttribute()
    {
        // First check if the journalable_type exists in DeAccount::$accountables
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->journalable_type) {
                return $item['alias'];
            }
        }

        // If not found in accountables, try to get class name from journalable_type (for regular morphTo)
        if ($this->journalable_type && is_string($this->journalable_type)) {
            return class_basename($this->journalable_type);
        }

        return 'Unknown';
    }
    public function files()
    {
        return $this->morphMany(DeFile::class, 'fileable');
    }

    public static function store($sourceTransaction,$destinationTransaction,$taskId=NULL,$eventName=NULL,$requestedData=NULL)
    {
        if (is_null($sourceTransaction))
            return false;
        if (is_null($destinationTransaction))
            return false;

        $sourceTransactionId = $sourceTransaction->id;
        $destinationTransactionId = $destinationTransaction->id;
        
        $journal = new DeJournal();
        $journal->date = $destinationTransaction->date;
        $journal->created_by = $destinationTransaction->created_by;
        $journal->task_id = $taskId??NULL;
        $journal->transaction_type = $eventName??NULL;
        $journal->note = $destinationTransaction->note;
        $journal->amount = $destinationTransaction->amount;
        if($destinationTransaction->type == 'CREDIT'){
            $journal->credit_transaction_id = $destinationTransactionId;
            $journal->debit_transaction_id = $sourceTransactionId;
        }elseif ($destinationTransaction->type == 'DEBIT') {
            $journal->debit_transaction_id = $destinationTransactionId;
            $journal->credit_transaction_id = $sourceTransactionId;
        }
        $journal->journalable_id = $requestedData['journalable_id']??$sourceTransaction->account_transactionable_id;
        $journal->journalable_type = $requestedData['journalable_type']??$sourceTransaction->account_transactionable_type;
        $journal->save();

        return $journal;
                    
    }
    public function task()
    {
        return $this->belongsTo(DeTask::class, 'task_id');
    }

}
