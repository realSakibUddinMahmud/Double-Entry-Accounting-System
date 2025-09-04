<?php

namespace Hilinkz\DEAccounting\Http\Controllers;

use Illuminate\Routing\Controller;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use App\Models\GasStation;
use DB;
use Request;
use PDF;

class DeJournalController extends Controller
{
    public static $eventName = 'JOURNAL';

    public function index()
    {
        // $gs_id = request('gs_id') ?? null;
        $download = request('download') ?? null;
        $start_date = date('Y-m-d', strtotime(request('start_date') ?? today()));
        $end_date = date('Y-m-d', strtotime(request('end_date') ?? today()));

        $query = DeJournal::with(['creditTransaction.account', 'debitTransaction.account'])
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date');

        // if ($gs_id) {
        //     if ($gs_id == 'NATIVE') {
        //         $journals = $query->whereNull('gas_station_id')->get();
        //         $gas_stations = GasStation::where('status', 'ACTIVE')->get();
        //     } else {
        //         $journals = $query->where('gas_station_id', $gs_id)->get();
        //         $gas_stations = GasStation::where('status', 'ACTIVE')->where('id', $gs_id)->get();
        //     }
        // } else {
        //     $journals = $query->get();
        //     $gas_stations = GasStation::where('status', 'ACTIVE')->orderBy('name')->get();
        // }

        $journals = $query->get();

        if ($download == 'YES') {
            $pdfFileName = 'Journal-Report-' . $start_date . '-' . $end_date . '-' . uniqid() . '-' . env('APP_NAME');
            $pdf = PDF::setPaper('a4', 'portrait')
                ->loadView('de-accounting::journals.download-pdf', [
                    'journals' => $journals,
                    // 'gas_stations' => $gas_stations,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]);
            return $pdf->download($pdfFileName . '.pdf');
        }

        // return view('de-accounting::journals.index', compact('journals', 'gas_stations'));
        return view('de-accounting::journals.index', compact('journals'));
    }
}