<?php

namespace Hilinkz\DEAccounting\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\GasStation;
use Hilinkz\DEAccounting\Models\DeAccount;
use DB;
use Request;
use PDF;

class DeLedgerController extends Controller
{
    public function indexOLD()
    {
        $gs_id = request('gs_id') ?? null;
        $account_title = request('account_title') ?? null;
        $download = request('download') ?? null;
        $start_date = date('Y-m-d', strtotime(request('start_date') ?? today()));
        $end_date = date('Y-m-d', strtotime(request('end_date') ?? today()));

        $haveData = false;
        $message = null;
        $ledgers = null;
        $accountStatement = null;
        $account_root_type = null;
        $gs_name = null;

        $accounts = DeAccount::select('title')
            ->where('status', 'ACTIVE')
            ->where('parent_id', '!=', null)
            ->groupBy('title')
            ->get();

        $activeGasStations = GasStation::where('status', 'ACTIVE')->orderBy('name')->get();

        if (isset($account_title)) {
            if (is_numeric($gs_id)) {
                $account = DeAccount::withoutGlobalScopes()
                    ->where('accountable_type', 2)
                    ->where('accountable_id', $gs_id)
                    ->where('title', $account_title)
                    ->first();

                $gs_name = GasStation::where('id', $gs_id)->pluck('name')->first();
            } else {
                if ($gs_id == 'NATIVE') {
                    $account = DeAccount::where('accountable_type', '!=', 2)
                        ->where('title', $account_title)
                        ->first();

                    $gs_name = "Companies Native";
                } elseif ($gs_id == 'ALL') {
                    $haveData = true;
                    $all_stations_accounts = DeAccount::where('accountable_type', 2)
                        ->where('title', $account_title)
                        ->get();

                    foreach ($all_stations_accounts as $ac) {
                        $account_root_type[$ac->gas_station_id] = $ac->root_type ?? null;
                        $ledgers[$ac->gas_station_id] = DB::connection('tenant')->select("call proc_ledgers_info(?,?,?)", [$start_date, $end_date, $ac->id]);
                        DB::connection('tenant')->select("call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)", [$start_date, $ac->id]);

                        $accountStatement[$ac->gas_station_id] = DB::connection('tenant')->select('SELECT @prev_balance as prev_balance, @prev_date as prev_date,@today_total_debit as today_total_debit,@today_total_credit as today_total_credit,@today_closing_balance as today_closing_balance')[0];
                    }

                    if ($download == 'YES') {

                        $myMpdf = get_myMpdf('A4', 'P');
                        $myMpdf->falseBoldWeight = 0;

                        $myMpdf->writeHTML(view('de-accounting::all-gs-ledgers-data-pdf', compact('activeGasStations', 'accounts', 'haveData', 'ledgers', 'accountStatement', 'account_root_type', 'start_date', 'end_date')));
                        return $myMpdf->Output('RyoGas-Report-Ledgers-' . $start_date . '-' . $end_date . '-' . uniqid() . '.pdf', 'D');
                    } else {
                        return view('de-accounting::ledgers.index', compact('activeGasStations', 'accounts', 'haveData', 'ledgers', 'accountStatement', 'account_root_type'));
                    }
                } else {
                    $account_ids = array();
                }
            }

            if (isset($account)) {
                $haveData = true;
                $account_id = $account->id;
                $account_root_type = $account->root_type ?? null;
                $ledgers = DB::connection('tenant')->select("call proc_ledgers_info(?,?,?)", [$start_date, $end_date, $account_id]);

                DB::connection('tenant')->select("call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)", [$start_date, $account_id]);

                $accountStatement = DB::connection('tenant')->select('SELECT @prev_balance as prev_balance, @prev_date as prev_date,@today_total_debit as today_total_debit,@today_total_credit as today_total_credit,@today_closing_balance as today_closing_balance')[0];

                if ($download == 'YES') {

                    $myMpdf = get_myMpdf('A4', 'P');
                    $myMpdf->falseBoldWeight = 0;

                    $myMpdf->writeHTML(view('de-accounting::ledgers.ledgers-data-pdf', compact('activeGasStations', 'accounts', 'haveData', 'ledgers', 'accountStatement', 'account_root_type', 'gs_name', 'start_date', 'end_date')));
                    return $myMpdf->Output('RyoGas-Report-Ledgers-' . $start_date . '-' . $end_date . '-' . uniqid() . '.pdf', 'D');
                } else {
                    return view('de-accounting::ledgers.index', compact('activeGasStations', 'accounts', 'haveData', 'ledgers', 'accountStatement', 'account_root_type', 'gs_name'));
                }
            } else {
                $message = "Sorry, wrong account selection.";
            }
        }

        return view('de-accounting::ledgers.index', compact('activeGasStations', 'accounts', 'haveData', 'message'));
    }

    public function index()
    {
        $account_title = request('account_title') ?? null;
        $download = request('download') ?? null;
        $start_date = date('Y-m-d', strtotime(request('start_date') ?? today()));
        $end_date = date('Y-m-d', strtotime(request('end_date') ?? today()));

        $haveData = false;
        $message = null;
        $ledgers = null;
        $accountStatement = null;
        $account_root_type = null;

        $accounts = DeAccount::select('title')
            ->where('status', 'ACTIVE')
            ->where('parent_id', '!=', null)
            ->groupBy('title')
            ->get();

        if (isset($account_title)) {
            $account = DeAccount::where('title', $account_title)
                ->when(request('accountable_type'), function ($query) {
                    $query->where('accountable_type', request('accountable_type'));
                })
                ->when(request('accountable_id'), function ($query) {
                    $query->where('accountable_id', request('accountable_id'));
                })
                ->first();

            if (isset($account)) {
                $haveData = true;
                $account_id = $account->id;
                $account_root_type = $account->root_type ?? null;

                $ledgers = DB::connection('tenant')->select("call proc_ledgers_info(?,?,?)", [$start_date, $end_date, $account_id]); // need to be made

                // // Dummy placeholder data, based on the original procedure logic
                // $ledgers = [
                //     (object)[
                //         'slno' => 1,
                //         'date' => date('Y-m-d'),
                //         'tnx_id' => 1001,
                //         'account_id' => $account_id,
                //         'debit' => 500.00,
                //         'credit' => 0.00,
                //         'balance' => 0.00,
                //         'other_tnx_id' => 2001,
                //         'amount' => 500.00,
                //         'title' => 'Sample Account',
                //         'note' => 'Dummy transaction',
                //         'cbalance' => 500.00,
                //     ]
                // ];

                DB::connection('tenant')->select("call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)", [$start_date, $account_id]); // Procedure not created yet
                $accountStatement = DB::connection('tenant')->select('SELECT @prev_balance as prev_balance, @prev_date as prev_date,@today_total_debit as today_total_debit,@today_total_credit as today_total_credit,@today_closing_balance as today_closing_balance')[0]; // need to be made

                // // Dummy placeholder data, based on the original procedure logic
                // $accountStatement = (object)[
                //     'prev_balance' => 1000.00,
                //     'prev_date' => date('Y-m-d', strtotime('-1 day', strtotime($start_date))),
                //     'today_total_debit' => 500.00,
                //     'today_total_credit' => 200.00,
                //     'today_closing_balance' => 1000.00 + 500.00 - 200.00,
                // ];


                if ($download == 'YES') {
                    $pdfFileName = 'RyoGas-Report-Ledgers-' . $start_date . '-' . $end_date . '-' . uniqid() . '-' . env('APP_NAME');
                    $pdf = PDF::setPaper('a4', 'portrait')
                        ->loadView('de-accounting::ledgers.ledgers-data-pdf', [
                            'accounts' => $accounts,
                            'haveData' => $haveData,
                            'ledgers' => $ledgers,
                            'accountStatement' => $accountStatement,
                            'account_root_type' => $account_root_type,
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                        ]);
                    return $pdf->download($pdfFileName . '.pdf');
                }
                // else {
                //     return view('de-accounting::ledgers.index', compact('accounts','haveData','ledgers','accountStatement','account_root_type', 'start_date', 'end_date'));
                // }
            } else {
                $message = "Sorry, wrong account selection.";
            }
        }

        // return view('de-accounting::ledgers.index', compact('accounts', 'haveData', 'message'));
        return view('de-accounting::ledgers.index', compact('accounts', 'haveData', 'ledgers', 'accountStatement', 'account_root_type', 'start_date', 'end_date'));
    }
}
