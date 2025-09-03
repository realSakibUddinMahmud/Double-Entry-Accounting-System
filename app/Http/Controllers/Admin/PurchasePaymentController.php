<?php

namespace App\Http\Controllers\Admin;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Hilinkz\DEAccounting\Models\DE;
use App\Http\Controllers\Controller;
use Hilinkz\DEAccounting\Models\DeFile;
use Illuminate\Support\Facades\Validator;
use Hilinkz\DEAccounting\Models\DeAccount;

class PurchasePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static $eventName = 'PURCHASE-PAYMENT';
    
    public function index($purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        // No payments table, so set payments to null or an empty array
        $journals = $purchase->journals()
            ->where('transaction_type','PURCHASE-PAYMENT')
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('admin.purchase.payment.index', compact('purchase', 'journals'));
    }

    public function create($purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $supplier = $purchase->supplier;

        $payableAccount = $supplier->accounts()
            ->where('title', 'like', '%Payable%')
            ->where('root_type', 3)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $assetsAccounts = DeAccount::where('root_type', 1)
            ->whereNotNull('parent_id')
            ->where(function($q) {
                $q->where('title', 'not like', '%Inventory%')
                  ->orWhereNull('title');
            })
            ->whereIn('accountable_type', [1, 5]) // Assets or Equity accounts
            ->get();
        return view('admin.purchase.payment.create', compact('purchase','assetsAccounts','payableAccount'));
    }

    public function store(Request $request, $purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);

        $connection = $purchase->getConnectionName();

        $validator = Validator::make($request->all(), [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $purchase->due_amount,
            ],
            'pay_to' => ['required', 'string', 'in:supplier,transportation'],
            'payment_date' => ['required', 'date'],
            'paid_from_account' => [
                'required',
                Rule::exists($connection . '.accounts', 'id'),
            ],
            'note' => ['nullable', 'string', 'max:255'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $validated = $validator->validate();

        if($validated['pay_to'] == 'supplier') {
            $supplier = $purchase->supplier;
            $payableAccount = $supplier->accounts()
                ->where('title', 'like', '%Payable%')
                ->where('root_type', 3)
                ->where('account_type_id', 1)
                ->whereNotNull('parent_id')
                ->first();
        } elseif($validated['pay_to'] == 'transportation') {
            $store = $purchase->store;
            $payableAccount = $store->accounts()
                ->where('title', 'Transportation Payable')
                ->where('root_type', 3)
                ->where('account_type_id', 1)
                ->whereNotNull('parent_id')
                ->first();
        }else{
            return redirect()->back()->with('error', 'Invalid payment type selected.');
        }

        if (!$payableAccount) {
            return redirect()->back()->with('error', 'Payable account not found.');
        }

        $sourceAccount = DeAccount::find($validated['paid_from_account']);
        $destinationAccount = $payableAccount;

        $requestedData = array();
        $requestedData['date'] = $validated['payment_date'];
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $validated['note'];
        $requestedData['amount'] = $validated['amount'];
        $requestedData['journalable_type'] = Purchase::class;
        $requestedData['journalable_id'] = $purchase->id;

        $deType = 'DOWNDOWN';
        $taskId = null;
        $eventName = self::$eventName;
        $result = DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        $readableEventName = ucwords(strtolower(str_replace('-', ' ', $eventName)));

        if ($result['status'] && $result['status'] == true) {
            $purchase->paid_amount += $validated['amount'];
            $purchase->due_amount -= $validated['amount'];

            // Set payment_status based on paid_amount and total_amount
            if ($purchase->paid_amount == 0) {
                $purchase->payment_status = 'Pending';
            } elseif ($purchase->paid_amount > 0 && $purchase->paid_amount < $purchase->total_amount) {
                $purchase->payment_status = 'Partial';
            } elseif ($purchase->paid_amount >= $purchase->total_amount) {
                $purchase->payment_status = 'Paid';
            }

            $purchase->save();
            
            if (!empty($validated['attachments'])) {

                DeFile::upload($validated['attachments'], $result);

            }
            return redirect()->back()->with('success', $readableEventName . ' has been recorded successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        } 
    }
    
    public function destroy($purchaseId, $journalId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $journal = $purchase->journals()->findOrFail($journalId);

        // Optionally, delete related files if needed
        if ($journal->files && $journal->files->count()) {
            foreach ($journal->files as $file) {
                DeFile::destroy($file);
            }
        }

        // Adjust paid_amount and due_amount
        $purchase->paid_amount -= $journal->amount;
        $purchase->due_amount += $journal->amount;

        // Update payment_status
        if ($purchase->paid_amount == 0) {
            $purchase->payment_status = 'Pending';
        } elseif ($purchase->paid_amount > 0 && $purchase->paid_amount < $purchase->total_amount) {
            $purchase->payment_status = 'Partial';
        } elseif ($purchase->paid_amount >= $purchase->total_amount) {
            $purchase->payment_status = 'Paid';
        }
        $purchase->save();

        $journal->debitTransaction()->delete();
        $journal->creditTransaction()->delete();
        $journal->delete();

        return redirect()->back()->with('success', 'Payment entry deleted successfully.');
    }
}