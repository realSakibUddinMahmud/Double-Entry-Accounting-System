<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccount;
use Auth;

use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class DeAccountTransaction extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'account_transactions';

    public function account()
    {
        return $this->belongsTo(DeAccount::class);
    }

    public function accountTransactionable()
    {
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->account_transactionable_type) {
                return $this->hasOne($item['class'], 'id', 'account_transactionable_id');
            }
        }

        return $this->morphTo();
    }
    public function getAccountTransactionableAliasAttribute()
    {
        foreach (DeAccount::$accountables as $item) {
            if ($item['class_id'] == $this->account_transactionable_type) {
                return $item['alias'];
            }
        }

        return 'Unknown';
    }

    public static function store($requestedData,$upDown)
    {
        if (is_null($requestedData))
            return false;

        $note = $requestedData['note']??null;
        $date = date('Y-m-d', strtotime($requestedData['date']??today()));
        $user_id = Auth::user()->id??null;
        $account_id = $requestedData['account_id'];
        $amount = $requestedData['amount'];
        $account_transactionable_type = $requestedData['account_transactionable_type']??NULL;
        $account_transactionable_id = $requestedData['account_transactionable_id']??NULL;
        

        $headType = DeAccount::headTypeCheck($account_id,$upDown);

        if (!in_array($headType, ['DEBIT', 'CREDIT'])) {
            echo $headType;
            return false;
        }

        $ac_transaction = new DeAccountTransaction();
        $ac_transaction->account_id = $account_id;
        $ac_transaction->amount = $amount;
        $ac_transaction->date = $date;
        $ac_transaction->type = $headType;
        if ($headType == 'DEBIT') {
            $ac_transaction->debit = $amount;
        }elseif($headType == 'CREDIT'){
            $ac_transaction->credit = $amount;
        }
        $ac_transaction->created_by = $user_id;
        $ac_transaction->note = $note;
        $ac_transaction->account_transactionable_type = $account_transactionable_type;
        $ac_transaction->account_transactionable_id = $account_transactionable_id;      
        $ac_transaction->save();

        return $ac_transaction;
        
            
    }

}
