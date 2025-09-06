<?php

namespace Hilinkz\DEAccounting\Http\Controllers;

use Illuminate\Routing\Controller;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use DB;
use Request;

class AccountController extends Controller
{
    public function index()
    {
        $query = DeAccount::latest();

        if (request()->filled('accountable_type')) {
            $query->where('accountable_type', request('accountable_type'));
        }

        if (request()->filled('accountable_id')) {
            $query->where('accountable_id', request('accountable_id'));
        }

        if (request()->filled('account_type_id')) {
            $query->where('account_type_id', request('account_type_id'));
        }

        if (request()->filled('title_ac_no')) {
            $query->where(function ($q) {
                $value = request('title_ac_no');
                $q->where('title', 'like', "%$value%")
                  ->orWhere('account_no', 'like', "%$value%");
            });
        }

        $accounts = $query->paginate()->appends(request()->query());
        // $accounts = $query->get();

        return view('de-accounting::accounts.index', compact('accounts'));
    }


    public function create()
    {
        return view('de-accounting::accounts.create');
    }

    public function edit($id)
    {
        return view('de-accounting::accounts.update', ['account_id' => $id]);
    }

    public function latestBalance($id)
    {        
        $input_date = date('Y-m-d', strtotime(now()));

        DB::connection('tenant')->select(
            'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
            [$input_date, $id],
        );

        $accStatement = DB::connection('tenant')->select(
            'SELECT @prev_balance as prev_balance, @prev_date as prev_date,@today_total_debit as today_total_debit,@today_total_credit as today_total_credit,@today_closing_balance as today_closing_balance',
        )[0];

        return response()->json([
            'account_id' => $id,
            'balance' => number_format($accStatement->today_closing_balance ?? 0, 2)
        ]);
    }

    public function delete($id)
    {
        $account = DeAccount::findOrFail($id);

        if ($account) {
            // Check if acc trans or journal
            $hasTransactions = DeAccountTransaction::where('account_id', $account->id)->exists();
            $hasJournalEntries = DeJournal::where('credit_transaction_id', $account->id)
                ->orWhere('debit_transaction_id', $account->id)
                ->exists();

            if ($hasTransactions || $hasJournalEntries) {
                //deactivate instead of delete
                $account->status = 'DEACTIVE';
                $account->save();

                return redirect()->route('de-account.index')->with('success', 'Account deactivated (has transactions)');
            } else {
                // Delete related bank account
                if ($account->bankAccount) {
                    $account->bankAccount->delete();
                }

                // Delete the account
                $account->delete();

                return redirect()->route('de-account.index')->with('success', 'Account deleted successfully');
            }
        }
        return redirect()->back()->with('error', 'Failed to delete the account.');
    }

    public function resetBalance(Request $request)
    {
        // return $request->all();
        abort(403);
        // $account = Account::findOrFail($request->account_id);

        // $input_date = date('Y-m-d', strtotime($request->date));

        // $accountStatement = AccountStatement::where('account_id', $account->id)->where('type', 'Daily')->whereDate('date', $input_date)->first();

        // if (!$accountStatement) {
        //     $accountStatement = new AccountStatement();
        //     $accountStatement->company_id = $account->company_id;
        //     $accountStatement->account_id = $account->id;
        //     $accountStatement->gas_station_id = $account->gas_station_id;
        //     $accountStatement->date = $input_date;
        //     $accountStatement->type = 'Daily';
        //     $accountStatement->total_debit = 0;
        //     $accountStatement->total_credit = 0;
        // }

        // $accountStatement->closing_balance = $request->balance;
        // $accountStatement->created_by = auth()->user()->id;
        // $accountStatement->note = 'RESETED';

        // $accountStatement->save();
        // // return $accountStatement;

        return back()->with('success', 'Account balance reset successfully');
    }
}
