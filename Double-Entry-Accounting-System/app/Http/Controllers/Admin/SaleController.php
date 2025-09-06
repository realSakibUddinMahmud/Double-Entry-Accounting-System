<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'store'])->latest();
        
        // Search by invoice ID (u_id only)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('u_id', 'LIKE', "%{$search}%");
        }
        
        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        
        $sales = $query->paginate(20)->withQueryString();
        
        // Get customers and stores for filters
        $customers = \App\Models\Customer::orderBy('name')->get();
        $stores = \App\Models\Store::orderBy('name')->get();
        
        return view('admin.sale.index', compact('sales', 'customers', 'stores'));
    }

    public function create()
    {
        return view('admin.sale.create');
    }

    public function store(Request $request)
    {
        // Validation and storing logic here
    }

    public function show(Sale $sale)
    {
        return view('admin.sale.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        return view('admin.sale.edit', compact('sale'));
    }

    public function update(Request $request, Sale $sale)
    {
        // Validation and update logic here
    }

    public function destroy(Sale $sale)
    {
        $task = $sale->tasks()->latest()->first();
        if ($task) {
            $salePaymentJournalCount = $task->journals()
                ->where('transaction_type', 'SALE-PAYMENT')
                ->count();
            if ($salePaymentJournalCount > 1) {
                return redirect()->route('sales.index')->with('error', 'Cannot delete sale with multiple payments.');
            }
        }

        $sale->journals()->each(function ($journal) {
            $journal->debitTransaction()->delete();
            $journal->creditTransaction()->delete();
            $journal->delete();
        });
        $sale->items()->delete(); // Delete related sale items
        $sale->tasks()->each(function ($task) {
            $task->delete();
        });
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    }
}