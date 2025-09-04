<?php

namespace Hilinkz\DEAccounting\Http\Controllers;

use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DE;
use Illuminate\Routing\Controller;
use Request;
use PDF;

class FundTransferController extends Controller
{
    public static $eventName = 'FUND-TRANSFER';
    public function index()
    {
        $query = DeJournal::where('transaction_type', self::$eventName);

        if (request()->filled('start_date')) {
            $startDate = date('Y-m-d', strtotime(request('start_date')));
            $query->whereDate('date', '>=', $startDate);
        }

        if (request()->filled('end_date')) {
            $endDate = date('Y-m-d', strtotime(request('end_date')));
            $query->whereDate('date', '<=', $endDate);
        }

        if (request()->filled('accountable_type')) {
            $query->where('journalable_type', request('accountable_type'));
        }

        if (request()->filled('accountable_id')) {
            $query->where('journalable_id', request('accountable_id'));
        }   

        if (request()->filled('download') && request('download') === 'YES') {
            $journals = $query->latest('date')->latest()->get();

            $start_date = date('Y-m-d', strtotime(request('start_date') ?? today()));
            $end_date = date('Y-m-d', strtotime(request('end_date') ?? today()));

            $pdfFileName = 'FundTransfer-Report-' . $start_date . '-' . $end_date . '-' . uniqid().'-'.env('APP_NAME');
            $pdf = PDF::setPaper('a4', 'portrait')
                ->loadView('de-accounting::fund-transfers.download-pdf', [
                    'journals' => $journals,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]);
            return $pdf->download($pdfFileName . '.pdf');
        }
            
        $journals = $query->latest()->paginate()->appends(request()->query());
        

        return view('de-accounting::fund-transfers.index', compact('journals'));
    }



    public function create()
    {
        return view('de-accounting::fund-transfers.create');
    }

    public function delete($id)
    {
        $journal = DeJournal::findOrFail($id);

        if ($journal) {
            $result = DE::delete($journal);
            if ($result['status'] && $result['status'] == true) {
                return redirect()->back()->with('success', 'Fund transfer deleted successfully.');
            } 
        }

        return redirect()->back()->with('error', 'Failed to delete fund transfer.');
    }

}
