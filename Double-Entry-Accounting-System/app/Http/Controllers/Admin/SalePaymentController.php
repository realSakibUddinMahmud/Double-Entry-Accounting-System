<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Hilinkz\DEAccounting\Models\DE;
use App\Http\Controllers\Controller;
use Hilinkz\DEAccounting\Models\DeFile;
use Illuminate\Support\Facades\Validator;
use Hilinkz\DEAccounting\Models\DeAccount;

class SalePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static $eventName = 'SALE-PAYMENT';

    public function index($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $journals = $sale->journals()->where('transaction_type','SALE-PAYMENT')
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('admin.sale.payment.index', compact('sale', 'journals'));
    }

    public function create($saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $store = $sale->store;
        $customer = $sale->customer;

        $customerReceivableAccount = $customer->accounts()
            ->where('title', 'like', '%Receivable%')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $assetsAccounts = $store->accounts()
            ->where(function ($q) {
                $q->where('account_type_id', 2)
                    ->orWhere('title', 'Cash');
            })
            ->whereNotNull('parent_id')
            ->where('root_type', 1)
            ->get();

        return view('admin.sale.payment.create', compact('sale', 'assetsAccounts', 'customerReceivableAccount'));
    }

    public function store(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $connection = $sale->getConnectionName();

        $validator = Validator::make($request->all(), [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $sale->due_amount,
            ],
            'payment_date' => ['required', 'date'],
            'received_in_account' => [
                'required',
                Rule::exists($connection . '.accounts', 'id'),
            ],
            'note' => ['nullable', 'string', 'max:255'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $validated = $validator->validate();

        $customer = $sale->customer;

        $customerReceivableAccount = $customer->accounts()
            ->where('title', 'like', '%Receivable%')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $destinationAccount = DeAccount::find($validated['received_in_account']);
        $sourceAccount = $customerReceivableAccount;

        $requestedData = array();
        $requestedData['date'] = $validated['payment_date'];
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $validated['note'];
        $requestedData['amount'] = $validated['amount'];
        $requestedData['journalable_type'] = Sale::class;
        $requestedData['journalable_id'] = $sale->id;

        $deType = 'DOWNUP';
        $taskId = $sale->tasks()->first()?->id;
        $eventName = self::$eventName;
        $result = DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        $readableEventName = ucwords(strtolower(str_replace('-', ' ', $eventName)));

        if ($result['status'] && $result['status'] == true) {
            $sale->paid_amount += $validated['amount'];
            $sale->due_amount -= $validated['amount'];

            // Set payment_status based on paid_amount and total_amount
            if ($sale->paid_amount == 0) {
                $sale->payment_status = 'Pending';
            } elseif ($sale->paid_amount > 0 && $sale->paid_amount < $sale->total_amount) {
                $sale->payment_status = 'Partial';
            } elseif ($sale->paid_amount >= $sale->total_amount) {
                $sale->payment_status = 'Paid';
            }

            $sale->save();

            if (!empty($validated['attachments'])) {
                DeFile::upload($validated['attachments'], $result);
            }
            return redirect()->back()->with('success', $readableEventName . ' has been recorded successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy($saleId, $journalId)
    {
        $sale = Sale::findOrFail($saleId);
        $journal = $sale->journals()->findOrFail($journalId);

        // Optionally, delete related files if needed
        if ($journal->files && $journal->files->count()) {
            foreach ($journal->files as $file) {
                DeFile::destroy($file);
            }
        }

        // Adjust paid_amount and due_amount
        $sale->paid_amount -= $journal->amount;
        $sale->due_amount += $journal->amount;

        // Update payment_status
        if ($sale->paid_amount == 0) {
            $sale->payment_status = 'Pending';
        } elseif ($sale->paid_amount > 0 && $sale->paid_amount < $sale->total_amount) {
            $sale->payment_status = 'Partial';
        } elseif ($sale->paid_amount >= $sale->total_amount) {
            $sale->payment_status = 'Paid';
        }
        $sale->save();

        $journal->debitTransaction()->delete();
        $journal->creditTransaction()->delete();
        $journal->delete();

        return redirect()->back()->with('success', 'Payment entry deleted successfully.');
    }
}