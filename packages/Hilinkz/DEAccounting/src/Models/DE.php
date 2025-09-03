<?php  
namespace Hilinkz\DEAccounting\Models;

use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountStatement;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeFile;
use Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class DE
{
    public static function store($sourceAc,$destinationAc,$requestedData,$deType,$taskId=null,$eventName=null)
    {
        if ($sourceAc == null) {
            return response()->json([
                'status' => false,
                'message' => 'Source account not found.'
            ]);
        }

        if ($destinationAc == null) {
            return response()->json([
                'status' => false,
                'message' => 'Destination account not found.'
            ]);
        }

        $sourceAccountData = array();
        $sourceAccountData['date'] = $requestedData['date'];
        $sourceAccountData['note'] = $requestedData['note'];
        $sourceAccountData['account_id'] = $sourceAc->id;
        $sourceAccountData['amount'] = $requestedData['amount'];
        $sourceAccountData['account_transactionable_type'] = $requestedData['source_transactionable_type']??$requestedData['source_accountable_type'];
        $sourceAccountData['account_transactionable_id'] = $requestedData['source_transactionable_id']??$requestedData['source_accountable_id'];

        $destinationAccountData = array();
        $destinationAccountData['date'] = $requestedData['date'];
        $destinationAccountData['note'] = $requestedData['note'];
        $destinationAccountData['account_id'] = $destinationAc->id;
        $destinationAccountData['amount'] = $requestedData['amount'];
        $destinationAccountData['account_transactionable_type'] = $requestedData['destination_transactionable_type']??$requestedData['destination_accountable_type'];
        $destinationAccountData['account_transactionable_id'] = $requestedData['destination_transactionable_id']??$requestedData['destination_accountable_id'];


        $result = DB::transaction(function () use ($sourceAccountData, $destinationAccountData, $taskId, $eventName, $deType, $requestedData) {
            $sourceTransaction = null;
            $destinationTransaction = null;

            if ($deType === 'UPUP') {
                $sourceTransaction = DeAccountTransaction::store($sourceAccountData, 'INCREASE');
                if (!$sourceTransaction) throw new Exception('Failed to store source transaction (UPUP INCREASE)');

                $destinationTransaction = DeAccountTransaction::store($destinationAccountData, 'INCREASE');
                if (!$destinationTransaction) throw new Exception('Failed to store destination transaction (UPUP INCREASE)');
                
            } elseif ($deType === 'UPDOWN') {
                $sourceTransaction = DeAccountTransaction::store($sourceAccountData, 'INCREASE');
                if (!$sourceTransaction) throw new Exception('Failed to store source transaction (UPDOWN INCREASE)');

                $destinationTransaction = DeAccountTransaction::store($destinationAccountData, 'DECREASE');
                if (!$destinationTransaction) throw new Exception('Failed to store destination transaction (UPDOWN DECREASE)');

            } elseif ($deType === 'DOWNUP') {
                $sourceTransaction = DeAccountTransaction::store($sourceAccountData, 'DECREASE');
                if (!$sourceTransaction) throw new Exception('Failed to store source transaction (DOWNUP DECREASE)');

                $destinationTransaction = DeAccountTransaction::store($destinationAccountData, 'INCREASE');
                if (!$destinationTransaction) throw new Exception('Failed to store destination transaction (DOWNUP INCREASE)');

            } elseif ($deType === 'DOWNDOWN') {
                $sourceTransaction = DeAccountTransaction::store($sourceAccountData, 'DECREASE');
                if (!$sourceTransaction) throw new Exception('Failed to store source transaction (DOWNDOWN DECREASE)');

                $destinationTransaction = DeAccountTransaction::store($destinationAccountData, 'DECREASE');
                if (!$destinationTransaction) throw new Exception('Failed to store destination transaction (DOWNDOWN DECREASE)');
            } else {
                throw new Exception('Invalid deType: ' . $deType);
            }

            return DeJournal::store($sourceTransaction, $destinationTransaction, $taskId, $eventName, $requestedData);
        });

        return [
            'status' => true,
            'data' => $result
        ];


    }
    public static function delete($journal = null)
    {
        try {
            DB::transaction(function () use ($journal) {
                $creditTransaction = $journal->creditTransaction;
                $debitTransaction = $journal->debitTransaction;

                // Delete files if available
                if ($journal->files && $journal->files->count() > 0) {
                    foreach ($journal->files as $file) {
                        DeFile::destroy($file);
                    }
                }

                // Delete journal and related transactions
                $journal->delete();
                $creditTransaction?->delete();
                $debitTransaction?->delete();
                
            });

            return [
                'status' => true,
                'message' => 'Journal and related transactions deleted successfully.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Failed to delete journal: ' . $e->getMessage()
            ];
        }
    }


    
}
  
?> 
