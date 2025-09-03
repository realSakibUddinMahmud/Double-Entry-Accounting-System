<?php

namespace App\Http\Controllers\Admin;

use PDF;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductStockAdjustment;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeJournal;

class ReportController extends Controller
{
    function __construct()
    {
        $this->middleware('auth');
    }
    // Shared query logic for sales report
    protected function getSalesReportData(Request $request)
    {
        $today = now()->toDateString();
        $startDate = $request->input('start_date') ?: $today;
        $endDate = $request->input('end_date') ?: $today;
        $storeId = $request->input('store_id');

        $salesQuery = Sale::query();

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $salesQuery->whereBetween('sale_date', [$start, $end]);
        } elseif ($startDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $salesQuery->where('sale_date', '>=', $start);
        } elseif ($endDate) {
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $salesQuery->where('sale_date', '<=', $end);
        }

        if ($storeId) {
            $salesQuery->where('store_id', $storeId);
        }

        $sales = $salesQuery->with(['items.product', 'store'])->get();

        // Group products by store
        $storeProductSales = [];
        foreach ($sales as $sale) {
            $storeKey = $sale->store ? $sale->store->name : 'Unknown Store';
            foreach ($sale->items as $item) {
                $product = $item->product;
                if (!$product) continue;
                $pid = $item->product_id;

                // Get ProductStore for this store and product
                $productStore = null;
                if ($sale->store_id) {
                    $productStore = ProductStore::where('product_id', $pid)
                        ->where('store_id', $sale->store_id)
                        ->with(['base_unit', 'sales_unit'])
                        ->first();
                }

                $baseUnitName = $productStore && $productStore->base_unit ? $productStore->base_unit->name : '';

                // Convert item quantity to base unit quantity if conversion exists
                $qtyInBaseUnit = $item->quantity * ($item ? $item->unit->conversion_factor : 1);

                $key = $pid;
                if (!isset($storeProductSales[$storeKey][$key])) {
                    $storeProductSales[$storeKey][$key] = [
                        'sku' => $product->sku ?? '',
                        'product_name' => $product->name ?? '',
                        'unit' => $baseUnitName,
                        'sold_qty' => 0,
                        'sold_amount' => 0,
                    ];
                }
                $storeProductSales[$storeKey][$key]['sold_qty'] += $qtyInBaseUnit;
                $storeProductSales[$storeKey][$key]['sold_amount'] += $item->total;
            }
        }

        // Convert to collection for easier handling in Blade
        $reportRows = collect($storeProductSales);

        $stores = Store::select('id', 'name')->get();

        return compact('reportRows', 'stores');
    }

    // Shared query logic for purchase report
    protected function getPurchaseReportData(Request $request)
    {
        $today = now()->toDateString();
        $startDate = $request->input('start_date') ?: $today;
        $endDate = $request->input('end_date') ?: $today;
        $storeId = $request->input('store_id');

        $purchaseQuery = Purchase::query();

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $purchaseQuery->whereBetween('purchase_date', [$start, $end]);
        } elseif ($startDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $purchaseQuery->where('purchase_date', '>=', $start);
        } elseif ($endDate) {
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $purchaseQuery->where('purchase_date', '<=', $end);
        }

        if ($storeId) {
            $purchaseQuery->where('store_id', $storeId);
        }

        $purchases = $purchaseQuery->with(['items.product', 'store'])->get();

        // Group products by store
        $storeProductPurchases = [];
        foreach ($purchases as $purchase) {
            $storeKey = $purchase->store ? $purchase->store->name : 'Unknown Store';
            foreach ($purchase->items as $item) {
                $product = $item->product;
                if (!$product) continue;
                $pid = $item->product_id;

                // Get ProductStore for this store and product
                $productStore = null;
                if ($purchase->store_id) {
                    $productStore = ProductStore::where('product_id', $pid)
                        ->where('store_id', $purchase->store_id)
                        ->with(['base_unit', 'purchase_unit'])
                        ->first();
                }

                $baseUnitName = $productStore && $productStore->base_unit ? $productStore->base_unit->name : '';

                // Convert item quantity to base unit quantity if conversion exists
                $qtyInBaseUnit = $item->quantity * ($item ? $item->unit->conversion_factor : 1);

                $key = $pid;
                if (!isset($storeProductPurchases[$storeKey][$key])) {
                    $storeProductPurchases[$storeKey][$key] = [
                        'sku' => $product->sku ?? '',
                        'product_name' => $product->name ?? '',
                        'unit' => $baseUnitName,
                        'purchased_qty' => 0,
                        'purchased_amount' => 0,
                    ];
                }
                $storeProductPurchases[$storeKey][$key]['purchased_qty'] += $qtyInBaseUnit;
                $storeProductPurchases[$storeKey][$key]['purchased_amount'] += $item->total;
            }
        }

        // Convert to collection for easier handling in Blade
        $reportRows = collect($storeProductPurchases);

        $stores = Store::select('id', 'name')->get();

        return compact('reportRows', 'stores');
    }

    protected function getStockReportData(Request $request)
    {
        $today = now()->toDateString();
        $startDate = $request->input('start_date') ?: $today;
        $endDate = $request->input('end_date') ?: $today;
        $storeId = $request->input('store_id');

        $stores = Store::select('id', 'name')->get();
        $productStoresQuery = ProductStore::with(['product', 'store', 'base_unit']);

        if ($storeId) {
            $productStoresQuery->where('store_id', $storeId);
        }

        $productStores = $productStoresQuery->get();
        $reportRows = [];

        foreach ($productStores as $ps) {
            $storeName = $ps->store ? $ps->store->name : 'Unknown Store';
            $pid = $ps->product_id;
            $baseUnitName = $ps->base_unit ? $ps->base_unit->name : '';

            // --- Initial Stock Calculation ---
            $initialStock = 0;
            if ($startDate) {
                $initPurchaseQuery = Purchase::where('store_id', $ps->store_id)
                    ->where('purchase_date', '<', \Carbon\Carbon::parse($startDate)->startOfDay())
                    ->whereHas('items', function ($q) use ($pid) {
                        $q->where('product_id', $pid);
                    });
                foreach ($initPurchaseQuery->with('items')->get() as $purchase) {
                    foreach ($purchase->items as $item) {
                        if ($item->product_id == $pid) {
                            $initialStock += $item->quantity * ($item->unit->conversion_factor ?? 1);
                        }
                    }
                }

                $initSalesQuery = Sale::where('store_id', $ps->store_id)
                    ->where('sale_date', '<', \Carbon\Carbon::parse($startDate)->startOfDay())
                    ->whereHas('items', function ($q) use ($pid) {
                        $q->where('product_id', $pid);
                    });
                foreach ($initSalesQuery->with('items')->get() as $sale) {
                    foreach ($sale->items as $item) {
                        if ($item->product_id == $pid) {
                            $initialStock -= $item->quantity * ($item->unit->conversion_factor ?? 1);
                        }
                    }
                }

                $initAdjustmentQuery = ProductStockAdjustment::where('product_id', $pid)
                    ->whereHas('stockAdjustment', function ($q) use ($ps, $startDate) {
                        $q->where('store_id', $ps->store_id)
                            ->where('date', '<', \Carbon\Carbon::parse($startDate)->startOfDay());
                    });
                foreach ($initAdjustmentQuery->get() as $adj) {
                    $initialStock += ($adj->action === '+') ? $adj->quantity : -$adj->quantity;
                }
            }

            // Purchase Qty
            $purchaseQuery = Purchase::where('store_id', $ps->store_id)
                ->whereHas('items', function ($q) use ($pid) {
                    $q->where('product_id', $pid);
                });
            if ($startDate) {
                $purchaseQuery->where('purchase_date', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
            }
            if ($endDate) {
                $purchaseQuery->where('purchase_date', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
            }
            $purchaseQty = 0;
            foreach ($purchaseQuery->with('items')->get() as $purchase) {
                foreach ($purchase->items as $item) {
                    if ($item->product_id == $pid) {
                        $purchaseQty += $item->quantity * ($item->unit->conversion_factor ?? 1);
                    }
                }
            }

            // Sales Qty
            $salesQuery = Sale::where('store_id', $ps->store_id)
                ->whereHas('items', function ($q) use ($pid) {
                    $q->where('product_id', $pid);
                });
            if ($startDate) {
                $salesQuery->where('sale_date', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
            }
            if ($endDate) {
                $salesQuery->where('sale_date', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
            }
            $salesQty = 0;
            foreach ($salesQuery->with('items')->get() as $sale) {
                foreach ($sale->items as $item) {
                    if ($item->product_id == $pid) {
                        $salesQty += $item->quantity * ($item->unit->conversion_factor ?? 1);
                    }
                }
            }

            // Adjustment Qty (show as array of strings like +3, -2, etc. and sum for total)
            $adjustmentStrings = [];
            $adjustmentTotal = 0;
            $adjustmentQuery = ProductStockAdjustment::where('product_id', $pid)
                ->whereHas('stockAdjustment', function ($q) use ($ps, $startDate, $endDate) {
                    $q->where('store_id', $ps->store_id);
                    if ($startDate) {
                        $q->where('date', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
                    }
                    if ($endDate) {
                        $q->where('date', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
                    }
                });

            foreach ($adjustmentQuery->get() as $adj) {
                $sign = $adj->action === '+' ? '+' : '-';
                $adjustmentStrings[] = $sign . abs($adj->quantity);
                $adjustmentTotal += ($adj->action === '+') ? $adj->quantity : -$adj->quantity;
            }

            // Calculate InStock Qty in controller
            $instockQty = $initialStock + $purchaseQty - $salesQty + $adjustmentTotal;

            $key = $storeName;
            if (!isset($reportRows[$key])) {
                $reportRows[$key] = [];
            }
            $reportRows[$key][] = [
                'sku' => $ps->product ? $ps->product->sku : '',
                'product_name' => $ps->product ? $ps->product->name : '',
                'unit' => $baseUnitName,
                'initial_stock' => $initialStock,
                'purchase_qty' => $purchaseQty,
                'sales_qty' => $salesQty,
                'adjustment_qty' => implode(', ', $adjustmentStrings),
                'adjustment_total' => $adjustmentTotal,
                'instock_qty' => $instockQty,
            ];
        }

        $reportRows = collect($reportRows);

        return compact('reportRows', 'stores');
    }

    public function sales(Request $request)
    {
        $data = $this->getSalesReportData($request);
        return view('admin.reports.sales', $data);
    }

    public function salesExport(Request $request)
    {
        $data = $this->getSalesReportData($request);
        $data['start_date'] = $request->input('start_date', now()->toDateString());
        $data['end_date'] = $request->input('end_date', now()->toDateString());
        $timestamp = now()->format('Ymd_His');
        $fileName = "sales_report_{$timestamp}.pdf";
        $pdf = PDF::loadView('admin.reports.sales-export-pdf', $data);
        return $pdf->download($fileName);
    }

    public function purchase(Request $request)
    {
        $data = $this->getPurchaseReportData($request);
        return view('admin.reports.purchase', $data);
    }

    public function purchaseExport(Request $request)
    {
        $data = $this->getPurchaseReportData($request);
        $data['start_date'] = $request->input('start_date', now()->toDateString());
        $data['end_date'] = $request->input('end_date', now()->toDateString());
        $timestamp = now()->format('Ymd_His');
        $fileName = "purchase_report_{$timestamp}.pdf";
        $pdf = PDF::loadView('admin.reports.purchase-export-pdf', $data);
        return $pdf->download($fileName);
    }

    public function stock(Request $request)
    {
        $data = $this->getStockReportData($request);
        return view('admin.reports.stock', $data);
    }

    public function stockExport(Request $request)
    {
        $data = $this->getStockReportData($request);
        $timestamp = now()->format('Ymd_His');
        $fileName = "stock_report_{$timestamp}.pdf";
        $pdf = PDF::loadView('admin.reports.stock-export-pdf', $data);
        return $pdf->download($fileName);
    }

    // public function incomeStatement(Request $request)
    // {
    //     $download = $request->download ?? null;
    //     $company_id = Auth::user()->company_id;

    //     $selectedId = $request->query('selected_id');

    //     $start_date = date('Y-m-d', strtotime(request('start_date') ?? today()));
    //     $end_date = date('Y-m-d', strtotime(request('end_date') ?? today()));

    //     $salesRecords = SalesRecord::whereBetween('sales_date', [$start_date, $end_date])
    //         ->get();

    //     $totalSalesTakaLt = Journal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
    //         ->join('accounts as debit_accounts', 'debit.account_id', '=', 'debit_accounts.id')
    //         ->join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
    //         ->join('accounts as credit_accounts', 'credit.account_id', '=', 'credit_accounts.id')
    //         ->where('credit_accounts.root_type', 4) // Income accounts
    //         ->where('credit_accounts.accountable_type', 1)
    //         ->where('credit_accounts.accountable_id', $company_id)
    //         ->where('credit_accounts.title', '=', 'Sales')
    //         ->whereNotNull('credit_accounts.parent_id')
    //         ->whereBetween('de_journals.date', [$start_date, $end_date])
    //         ->where('credit.type', 'CREDIT')
    //         ->select(DB::raw('SUM(de_journals.amount) as total_amount'))
    //         ->pluck('total_amount') // pluck the sum of amounts
    //         ->first() ?? 0;

    //     $salesReturn = SalesTransaction::whereBetween('sales_date', [$start_date, $end_date])
    //         ->where('company_id', $company_id)
    //         ->where('payment_type', 'like', '%SALES%')
    //         ->oldest('sales_date')
    //         ->sum('sales_taka') ?? 0;

    //     // Fetch other expenses (excluding COGS)
    //     $expenses = Account::where('root_type', 2) // Expense accounts
    //         // ->where('account_type_id', 7)
    //         ->where('accountable_type', 1)
    //         ->where('accountable_id', $company_id)
    //         ->whereNotNull('parent_id')
    //         ->get();

    //     $expenseData = Journal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
    //         ->join('accounts as debit_accounts', 'debit.account_id', '=', 'debit_accounts.id')
    //         ->join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
    //         ->join('accounts as credit_accounts', 'credit.account_id', '=', 'credit_accounts.id')
    //         ->where('debit_accounts.root_type', 2) // Expense accounts
    //         ->where('debit_accounts.accountable_type', 1)
    //         ->where('debit_accounts.accountable_id', $company_id)
    //         ->where('debit_accounts.account_type_id', 7)
    //         ->whereNotNull('debit_accounts.parent_id')
    //         ->whereBetween('de_journals.date', [$start_date, $end_date])
    //         ->where('debit.type', 'DEBIT') // Ensure expenses are recorded as debits
    //         ->select('debit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
    //         ->groupBy('debit_accounts.title')
    //         ->get();

    //     // Fetch incomes (Income accounts)
    //     $incomes = Account::where('root_type', 4) // Income accounts
    //         // ->where('account_type_id', 2)
    //         ->where('accountable_type', 1)
    //         ->where('accountable_id', $company_id)
    //         ->whereNotNull('parent_id')
    //         ->get();

    //     $incomeData = Journal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
    //         ->join('accounts as debit_accounts', 'debit.account_id', '=', 'debit_accounts.id')
    //         ->join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
    //         ->join('accounts as credit_accounts', 'credit.account_id', '=', 'credit_accounts.id')
    //         ->where('credit_accounts.root_type', 4) // Income accounts
    //         ->where('credit_accounts.accountable_type', 1)
    //         ->where('credit_accounts.accountable_id', $company_id)
    //         // ->where('credit_accounts.title', '!=', 'Sales')
    //         ->whereNotNull('credit_accounts.parent_id')
    //         ->whereBetween('de_journals.date', [$start_date, $end_date])
    //         ->where('credit.type', 'CREDIT')
    //         ->select('credit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
    //         ->groupBy('credit_accounts.title')
    //         ->get();

    //     // if ($download == 'YES') {
    //     //     $pdfFileName = 'RyoGas-Report-Income-Statement-' . $start_date . '-' . $end_date . '-' . uniqid();

    //     //     $pdf = PDF::setPaper('a4', 'portrait')->loadView('admin.statements.income-statement-pdf', ['result' => $result, 'selectedId' => $selectedId, 'company_id' => $company_id, 'gasStations' => $gasStations, 'salesReturn' => $salesReturn, 'expenseData' => $expenseData, 'incomeData' => $incomeData, 'start_date' => $start_date, 'end_date' => $end_date]);

    //     //     // Critical font configuration
    //     //     $pdf->getDomPDF()->set_option('fontDir', public_path('fonts'));
    //     //     $pdf->getDomPDF()->set_option('fontCache', storage_path('fonts'));
    //     //     $pdf->getDomPDF()->set_option('defaultFont', 'Nikosh'); // Match font-family exactly
    //     //     $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
    //     //     $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    //     //     $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

    //     //     return $pdf->stream($pdfFileName . '.pdf');
    //     // }

    //     if ($download == 'YES') {

    //         $myMpdf = get_myMpdf('A4', 'P');
    //         $myMpdf->falseBoldWeight = 0;

    //         $myMpdf->writeHTML(view('admin.statements.income-statement-pdf', compact('result', 'selectedId', 'company_id', 'gasStations', 'salesReturn', 'expenseData', 'incomeData', 'start_date', 'end_date')));
    //         return $myMpdf->Output('RyoGas-Report-Income-Statement-' . $start_date . '-' . $end_date . '-' . uniqid() . '.pdf', 'I');
    //     }

    //     // return view('admin.statements.income-statement', compact('revenues', 'cogs', 'expenses', 'total_revenue', 'total_cogs', 'gross_profit', 'total_expenses', 'net_income', 'start_date', 'end_date'));
    //     return view('admin.statements.income-statement', compact('result', 'selectedId', 'company_id', 'gasStations', 'salesReturn', 'expenseData', 'incomeData', 'start_date', 'end_date'));
    // }

    protected function getIncomeStatementData(Request $request)
    {
        $company_id = Auth::user()->tenant_id ?? 0;
        $selectedId = $request->query('selected_id');

        $today = now()->toDateString();
        $startDate = $request->input('start_date') ?: $today;
        $endDate = $request->input('end_date') ?: $today;

        if($selectedId == 'company') {
            $salesIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->whereNotNull('parent_id')
                ->where('account_type_id','!=',8) //exclude other income accounts
                ->pluck('id')
                ->toArray();
            $otherIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->whereNotNull('parent_id')
                ->where('account_type_id', 8) //only other income accounts
                ->pluck('id')
                ->toArray();
            $expenseAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->where('account_type_id', 7)
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
            $cogsAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->where('title', 'Cost of Goods Sold')
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
        } elseif($selectedId == 'all-store') {
            $storeIds = Store::pluck('id')->toArray();
            $salesIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->whereNotNull('parent_id')
                ->where('account_type_id', '!=', 8) //exclude other income accounts
                ->pluck('id')
                ->toArray();
            $otherIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->whereNotNull('parent_id')
                ->where('account_type_id', 8) //only other income accounts
                ->pluck('id')
                ->toArray();
            $expenseAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->where('account_type_id', 7)
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
            $cogsAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->where('title', 'Cost of Goods Sold')
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
        } else {
            $storeId = $selectedId;
            $salesIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->whereNotNull('parent_id')
                ->where('account_type_id', '!=', 8) //exclude other income accounts
                ->pluck('id')
                ->toArray();
            $otherIncomeAccountIds = DeAccount::where('root_type', 4)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->whereNotNull('parent_id')
                ->where('account_type_id', 8) //only other income accounts
                ->pluck('id')
                ->toArray();
            $expenseAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->where('account_type_id', 7)
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
            $cogsAccountIds = DeAccount::where('root_type', 2)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->where('title', 'Cost of Goods Sold')
                ->whereNotNull('parent_id')
                ->pluck('id')
                ->toArray();
        }
        // Fetch sales data
        // Get total amount for each credit account in the date range
        // Fetch total sales for only the selected income accounts
        $salesIncomeAcData = DeJournal::join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
            ->join('accounts as credit_accounts', 'credit.account_id', '=', 'credit_accounts.id')
            ->whereBetween('de_journals.date', [$startDate, $endDate])
            ->whereIn('credit.account_id', $salesIncomeAccountIds)
            ->select('credit.account_id', 'credit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
            ->groupBy('credit.account_id', 'credit_accounts.title')
            ->get();
        $otherIncomeAcData = DeJournal::join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
            ->join('accounts as credit_accounts', 'credit.account_id', '=', 'credit_accounts.id')
            ->whereBetween('de_journals.date', [$startDate, $endDate])
            ->whereIn('credit.account_id', $otherIncomeAccountIds)
            ->select('credit.account_id', 'credit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
            ->groupBy('credit.account_id', 'credit_accounts.title')
            ->get();

        // Similarly get expense account data
        $expenseAcData = DeJournal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
            ->join('accounts as debit_accounts', 'debit.account_id', '=', 'debit_accounts.id')
            ->whereBetween('de_journals.date', [$startDate, $endDate])
            ->whereIn('debit.account_id', $expenseAccountIds)
            ->select('debit.account_id', 'debit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
            ->groupBy('debit.account_id', 'debit_accounts.title')
            ->get();
        // Fetch COGS data
        $cogsAcData = DeJournal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
            ->join('accounts as debit_accounts', 'debit.account_id', '=', 'debit_accounts.id')
            ->whereBetween('de_journals.date', [$startDate, $endDate])
            ->whereIn('debit.account_id', $cogsAccountIds)
            ->select('debit.account_id', 'debit_accounts.title', DB::raw('SUM(de_journals.amount) as total_amount'))
            ->groupBy('debit.account_id', 'debit_accounts.title')
            ->get();

        return compact('salesIncomeAcData','otherIncomeAcData', 'cogsAcData', 'expenseAcData', 'startDate', 'endDate');
    }

    public function incomeStatement(Request $request)
    {
        $data = $this->getIncomeStatementData($request);
        $stores = Store::select('id', 'name')->get();
        return view('admin.reports.income-statement', $data, compact('stores'));
    }

    public function incomeStatementExport(Request $request)
    {
        $data = $this->getIncomeStatementData($request);
        $selectedId = $request->query('selected_id');
        $company_id = Auth::user()->tenant_id ?? 0;
        if($selectedId == 'company') {
            $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
            $data['selected_name'] = $company ? $company->name : 'Company';
            $data['address'] = $company ? $company->address : '';
            $data['phone'] = $company ? $company->phone : '';
        } elseif($selectedId == 'all-store') {
            $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
            $data['selected_name'] = $company ? $company->name : 'Company';
            $data['address'] = $company ? $company->address : '';
            $data['phone'] = $company ? $company->phone : '';
        } else {
            $store = Store::find($selectedId);
            $data['selected_name'] = $store ? $store->name : 'Unknown Store';
            $data['address'] = $store ? $store->address : '';
            $data['phone'] = $store ? $store->phone : '';
        }

        $data['start_date'] = $request->input('start_date', now()->toDateString());
        $data['end_date'] = $request->input('end_date', now()->toDateString());
        $timestamp = now()->format('Ymd_His');
        $fileName = "income_statement_{$timestamp}.pdf";
        $pdf = PDF::loadView('admin.reports.income-statement-export-pdf', $data);
        return $pdf->download($fileName);
    }

    public function accountTransactions(Request $request, $accountId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Get account information
        $account = DeAccount::find($accountId);
        if (!$account) {
            abort(404, 'Account not found');
        }

        // Check if this is a cumulative balance request (Balance Sheet/Trial Balance)
        $isCumulativeBalance = ($startDate === $endDate);
        
        if ($isCumulativeBalance) {
            // For Balance Sheet and Trial Balance: Show cumulative balance up to the end date
            $cumulativeEndDate = $endDate;
            
            // For cumulative balance, calculate the opening balance from the very beginning
            // Get the first transaction date for this account to determine the true beginning
            $firstTransaction = DeJournal::where(function($query) use ($accountId) {
                    $query->whereHas('debitTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    })
                    ->orWhereHas('creditTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    });
                })
                ->orderBy('date', 'asc')
                ->first();
            
            if ($firstTransaction) {
                // Calculate opening balance for the day before the first transaction
                $dayBeforeFirstTransaction = \Carbon\Carbon::parse($firstTransaction->date)->subDay()->toDateString();
                
                DB::connection('tenant')->select(
                    'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
                    [$dayBeforeFirstTransaction, $accountId]
                );

                $openingBalanceResult = DB::connection('tenant')->select(
                    'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
                )[0];

                $openingBalance = (float)($openingBalanceResult->today_closing_balance ?? 0);
            } else {
                // No transactions found, opening balance is 0
                $openingBalance = 0;
            }

            // Get ALL journal entries for this account up to the end date (cumulative)
            $journals = DeJournal::with(['debitTransaction.account', 'creditTransaction.account'])
                ->where(function($query) use ($accountId) {
                    $query->whereHas('debitTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    })
                    ->orWhereHas('creditTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    });
                })
                ->where('date', '<=', $cumulativeEndDate)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->paginate(50);

            $totalDebit = 0;
            $totalCredit = 0;

            // Calculate totals for all records up to the end date
            $allJournals = DeJournal::where(function($query) use ($accountId) {
                    $query->whereHas('debitTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    })
                    ->orWhereHas('creditTransaction', function($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    });
                })
                ->where('date', '<=', $cumulativeEndDate)
                ->with(['debitTransaction', 'creditTransaction'])
                ->get();

            foreach ($allJournals as $journal) {
                if ($journal->debitTransaction && $journal->debitTransaction->account_id == $accountId) {
                    $totalDebit += $journal->amount;
                }
                if ($journal->creditTransaction && $journal->creditTransaction->account_id == $accountId) {
                    $totalCredit += $journal->amount;
                }
            }

            // For cumulative balance, set start date to beginning of time for display
            $displayStartDate = 'Beginning';
            $displayEndDate = $cumulativeEndDate;

        } else {
            // For Income Statement: Show transactions within the date range
        // Calculate opening balance using stored procedure
        // Get the day before start date to calculate opening balance
        $dayBeforeStartDate = \Carbon\Carbon::parse($startDate)->subDay()->toDateString();
        
        DB::connection('tenant')->select(
            'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
            [$dayBeforeStartDate, $accountId]
        );

        $openingBalanceResult = DB::connection('tenant')->select(
            'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
        )[0];

        $openingBalance = (float)($openingBalanceResult->today_closing_balance ?? 0);

        // Get journal entries for this account within the date range
        $journals = DeJournal::with(['debitTransaction.account', 'creditTransaction.account'])
            ->where(function($query) use ($accountId) {
                $query->whereHas('debitTransaction', function($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })
                ->orWhereHas('creditTransaction', function($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(50);

        $totalDebit = 0;
        $totalCredit = 0;

        // Calculate totals for all records (not just current page)
        $allJournals = DeJournal::where(function($query) use ($accountId) {
                $query->whereHas('debitTransaction', function($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })
                ->orWhereHas('creditTransaction', function($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['debitTransaction', 'creditTransaction'])
            ->get();

        foreach ($allJournals as $journal) {
            if ($journal->debitTransaction && $journal->debitTransaction->account_id == $accountId) {
                $totalDebit += $journal->amount;
            }
            if ($journal->creditTransaction && $journal->creditTransaction->account_id == $accountId) {
                $totalCredit += $journal->amount;
            }
        }

            $displayStartDate = $startDate;
            $displayEndDate = $endDate;
        }

        return view('admin.reports.account-transactions', compact(
            'account', 
            'journals', 
            'totalDebit', 
            'totalCredit', 
            'displayStartDate', 
            'displayEndDate', 
            'accountId', 
            'openingBalance',
            'isCumulativeBalance'
        ));
    }

    protected function getBalanceSheetDataOld(Request $request)
    {
        $company_id = Auth::user()->tenant_id ?? 0;
        $selectedId = $request->query('selected_id');

        $today = now()->toDateString();
        $date = $request->input('date') ?: $today;

        if ($selectedId == 'company') {
            $aseetsAccounts = DeAccount::where('root_type', 1)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->whereNotNull('parent_id')
                ->get();

            $liabalitiesAccounts = DeAccount::where('root_type', 3)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->whereNotNull('parent_id')
                ->get();

            $capitalAccounts = DeAccount::where('root_type', 5)
                ->where('accountable_type', 1)
                ->where('accountable_id', $company_id)
                ->whereNotNull('parent_id')
                ->get();

        } elseif ($selectedId == 'all-store') {
            $storeIds = Store::pluck('id')->toArray();
            $aseetsAccounts = DeAccount::where('root_type', 1)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->whereNotNull('parent_id')
                ->get();

            $liabalitiesAccounts = DeAccount::where('root_type', 3)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->whereNotNull('parent_id')
                ->get();

            $capitalAccounts = DeAccount::where('root_type', 5)
                ->where('accountable_type', 5)
                ->whereIn('accountable_id', $storeIds)
                ->whereNotNull('parent_id')
                ->get();

        } else {
            $storeId = $selectedId;
            $aseetsAccounts = DeAccount::where('root_type', 1)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->whereNotNull('parent_id')
                ->get();

            $liabalitiesAccounts = DeAccount::where('root_type', 3)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->whereNotNull('parent_id')
                ->get();

            $capitalAccounts = DeAccount::where('root_type', 5)
                ->where('accountable_type', 5)
                ->where('accountable_id', $storeId)
                ->whereNotNull('parent_id')
                ->get();
        }

        $assetData = [];
        // Group asset accounts by title and sum their balances
        $assetBalances = [];
        foreach ($aseetsAccounts as $assetAccount) {
            DB::connection('tenant')->select(
            'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
            [$date, $assetAccount->id]
            );

            $accStatement = DB::connection('tenant')->select(
            'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
            )[0];

            $title = $assetAccount->title;
            $balance = (float)($accStatement->today_closing_balance ?? 0);

            if (!isset($assetBalances[$title])) {
            $assetBalances[$title] = 0;
            }
            $assetBalances[$title] += $balance;
        }

        $assetData = [];
        foreach ($assetBalances as $title => $balance) {
            $assetData[] = [
            'title' => $title,
            'balance' => $balance,
            ];
        }
        // Liabilities: group by title and sum balances
        $liabilityBalances = [];
        foreach ($liabalitiesAccounts as $liabilityAccount) {
            DB::connection('tenant')->select(
            'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
            [$date, $liabilityAccount->id]
            );
            $accStatement = DB::connection('tenant')->select(
            'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
            )[0];
            $title = $liabilityAccount->title;
            $balance = (float)($accStatement->today_closing_balance ?? 0);
            if (!isset($liabilityBalances[$title])) {
            $liabilityBalances[$title] = 0;
            }
            $liabilityBalances[$title] += $balance;
        }
        $liabilityData = [];
        foreach ($liabilityBalances as $title => $balance) {
            $liabilityData[] = [
            'title' => $title,
            'balance' => $balance,
            ];
        }

        // Capital: group by title and sum balances
        $capitalBalances = [];
        foreach ($capitalAccounts as $capitalAccount) {
            DB::connection('tenant')->select(
            'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
            [$date, $capitalAccount->id]
            );
            $accStatement = DB::connection('tenant')->select(
            'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
            )[0];
            $title = $capitalAccount->title;
            $balance = (float)($accStatement->today_closing_balance ?? 0);
            if (!isset($capitalBalances[$title])) {
            $capitalBalances[$title] = 0;
            }
            $capitalBalances[$title] += $balance;
        }
        $capitalData = [];
        foreach ($capitalBalances as $title => $balance) {
            $capitalData[] = [
            'title' => $title,
            'balance' => $balance,
            ];
        }
        $totalAssets = array_sum(array_column($assetData, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilityData, 'balance'));
        $totalCapital = array_sum(array_column($capitalData, 'balance'));
        $totalProfit = $this->getProfit($request);

        return compact('selectedId', 'company_id', 'assetData', 'liabilityData','capitalData','totalAssets', 'totalLiabilities', 'totalCapital', 'date', 'totalProfit');
    }
    protected function getBalanceSheetData(Request $request)
    {
        $company_id = Auth::user()->tenant_id ?? 0;
        $selectedId = $request->query('selected_id');

        $today = now()->toDateString();
        $date = $request->input('date') ?: $today;

        // Get all accounts in one query with eager loading
        $assetsAccounts = DeAccount::where('root_type', 1)
            ->whereNotNull('parent_id')
            ->get();

        $liabalitiesAccounts = DeAccount::where('root_type', 3)
            ->whereNotNull('parent_id')
            ->get();

        $capitalAccounts = DeAccount::where('root_type', 5)
            ->whereNotNull('parent_id')
            ->get();

        // Process assets efficiently using batch processing
        $assetData = [];
        $assetDataDetailed = [];
        $assetAccountBalances = [];
        $assetGroupedBalances = [];
        $assetAccountIds = [];
        
        // Get all asset account balances in batch
        $assetAccountBalances = $this->getAccountBalancesBatch($assetsAccounts, $date);
        
        // Group by title and build data structures
        foreach ($assetsAccounts as $assetAccount) {
            $title = $assetAccount->title;
            $balance = $assetAccountBalances[$assetAccount->id] ?? 0;

            if (!isset($assetGroupedBalances[$title])) {
                $assetGroupedBalances[$title] = 0;
                $assetAccountIds[$title] = $assetAccount->id;
            }
            $assetGroupedBalances[$title] += $balance;
        }

        // Build asset data arrays
        foreach ($assetGroupedBalances as $title => $balance) {
            $assetData[] = [
                'title' => $title,
                'balance' => $balance,
                'account_id' => $assetAccountIds[$title] ?? null,
                'type' => 'group'
            ];
            
            // Build detailed data from the batch results
            $detailedAccounts = $assetsAccounts->where('title', $title);
            foreach ($detailedAccounts as $account) {
                $accountBalance = $assetAccountBalances[$account->id] ?? 0;
                $assetDataDetailed[$title][] = [
                    'title' => $account->title,
                    'account_number' => $account->account_number ?? '',
                    'balance' => $accountBalance,
                    'account_id' => $account->id,
                    'type' => 'detail',
                    'parent_title' => $title,
                    'accountable_type' => $account->accountable_type,
                    'accountable_id' => $account->accountable_id,
                    'accountable_alias' => $account->accountable_alias ?? 'Unknown',
                    'accountable_name' => $account->accountable ? $account->accountable->name : 'Unknown'
                ];
            }
        }
        
        // Liabilities: group by title and sum balances efficiently
        $liabilityGroupedBalances = [];
        $liabilityAccountIds = [];
        
        // Get all liability account balances in batch
        $liabilityAccountBalances = $this->getAccountBalancesBatch($liabalitiesAccounts, $date);
        
        foreach ($liabalitiesAccounts as $liabilityAccount) {
            $title = $liabilityAccount->title;
            $balance = $liabilityAccountBalances[$liabilityAccount->id] ?? 0;
            
            if (!isset($liabilityGroupedBalances[$title])) {
                $liabilityGroupedBalances[$title] = 0;
                $liabilityAccountIds[$title] = $liabilityAccount->id;
            }
            $liabilityGroupedBalances[$title] += $balance;
        }
        $liabilityData = [];
        $liabilityDataDetailed = [];
        foreach ($liabilityGroupedBalances as $title => $balance) {
            // Grouped data for summary view
            $liabilityData[] = [
                'title' => $title,
                'balance' => $balance,
                'account_id' => $liabilityAccountIds[$title] ?? null,
                'type' => 'group'
            ];
            
            // Detailed data for expansion
            $detailedAccounts = $liabalitiesAccounts->where('title', $title);
            foreach ($detailedAccounts as $account) {
                $accountBalance = $liabilityAccountBalances[$account->id] ?? 0;
                
                $liabilityDataDetailed[$title][] = [
                    'title' => $account->title,
                    'account_number' => $account->account_number ?? '',
                    'balance' => $accountBalance,
                    'account_id' => $account->id,
                    'type' => 'detail',
                    'parent_title' => $title,
                    'accountable_type' => $account->accountable_type,
                    'accountable_id' => $account->accountable_id,
                    'accountable_alias' => $account->accountable_alias ?? 'Unknown',
                    'accountable_name' => $account->accountable ? $account->accountable->name : 'Unknown'
                ];
            }
        }

        // Capital: group by title and sum balances efficiently
        $capitalGroupedBalances = [];
        $capitalAccountIds = [];
        
        // Get all capital account balances in batch
        $capitalAccountBalances = $this->getAccountBalancesBatch($capitalAccounts, $date);
        
        foreach ($capitalAccounts as $capitalAccount) {
            $title = $capitalAccount->title;
            $balance = $capitalAccountBalances[$capitalAccount->id] ?? 0;
            
            if (!isset($capitalGroupedBalances[$title])) {
                $capitalGroupedBalances[$title] = 0;
                $capitalAccountIds[$title] = $capitalAccount->id;
            }
            $capitalGroupedBalances[$title] += $balance;
        }
        $capitalData = [];
        $capitalDataDetailed = [];
        foreach ($capitalGroupedBalances as $title => $balance) {
            // Grouped data for summary view
            $capitalData[] = [
                'title' => $title,
                'balance' => $balance,
                'account_id' => $capitalAccountIds[$title] ?? null,
                'type' => 'group'
            ];
            
            // Detailed data for expansion
            $detailedAccounts = $capitalAccounts->where('title', $title);
            foreach ($detailedAccounts as $account) {
                $accountBalance = $capitalAccountBalances[$account->id] ?? 0;
                
                $capitalDataDetailed[$title][] = [
                    'title' => $account->title,
                    'account_number' => $account->account_number ?? '',
                    'balance' => $accountBalance,
                    'account_id' => $account->id,
                    'type' => 'detail',
                    'parent_title' => $title,
                    'accountable_type' => $account->accountable_type,
                    'accountable_id' => $account->accountable_id,
                    'accountable_alias' => $account->accountable_alias ?? 'Unknown',
                    'accountable_name' => $account->accountable ? $account->accountable->name : 'Unknown'
                ];
            }
        }
        $totalAssets = array_sum(array_column($assetData, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilityData, 'balance'));
        $totalCapital = array_sum(array_column($capitalData, 'balance'));
        $totalProfit = $this->getProfit($request);

        return compact(
            'selectedId', 
            'company_id', 
            'assetData', 
            'assetDataDetailed',
            'liabilityData', 
            'liabilityDataDetailed',
            'capitalData', 
            'capitalDataDetailed',
            'totalAssets', 
            'totalLiabilities', 
            'totalCapital', 
            'date', 
            'totalProfit'
        );
    }

    /**
     * Get account balances in batch to avoid multiple stored procedure calls
     */
    protected function getAccountBalancesBatch($accounts, $date)
    {
        $balances = [];
        
        foreach ($accounts as $account) {
            DB::connection('tenant')->select(
                'call proc_account_prev_balance_fixed_date(?,?,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance)',
                [$date, $account->id]
            );

            $accStatement = DB::connection('tenant')->select(
                'SELECT @prev_balance as prev_balance, @prev_date as prev_date, @today_total_debit as today_total_debit, @today_total_credit as today_total_credit, @today_closing_balance as today_closing_balance'
            )[0];

            $balances[$account->id] = (float)($accStatement->today_closing_balance ?? 0);
        }
        
        return $balances;
    }

    public function balanceSheet(Request $request)
    {
        // Check if this is a PDF export request
        if ($request->input('format') === 'pdf') {
            return $this->balanceSheetExport($request);
        }
        
        // Normal view request
        $data = $this->getBalanceSheetData($request);
        $stores = Store::select('id', 'name')->get();
        return view('admin.reports.balance-sheet', $data, compact('stores'));
    }

    public function balanceSheetExportOld(Request $request)
    {
        $data = $this->getBalanceSheetData($request);
        $data['date'] = $request->input('date', now()->toDateString());
        $selectedId = $request->query('selected_id');
        $company_id = Auth::user()->tenant_id ?? 0;
        if ($selectedId == 'company') {
            $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
            $data['selected_name'] = $company ? $company->name : 'Company';
            $data['address'] = $company ? $company->address : '';
            $data['phone'] = $company ? $company->phone : '';
        } elseif ($selectedId == 'all-store') {
            $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
            $data['selected_name'] = $company ? $company->name : 'Company';
            $data['address'] = $company ? $company->address : '';
            $data['phone'] = $company ? $company->phone : '';
        } else {
            $store = Store::find($selectedId);
            $data['selected_name'] = $store ? $store->name : 'Unknown Store';
            $data['address'] = $store ? $store->address : '';
            $data['phone'] = $store ? $store->phone : '';
        }
        $timestamp = now()->format('Ymd_His');
        $fileName = "balance_sheet_{$timestamp}.pdf";
        $pdf = PDF::loadView('admin.reports.balance-sheet-export-pdf', $data);
        return $pdf->download($fileName);
    }
    public function balanceSheetExport(Request $request)
    {
        $data = $this->getBalanceSheetData($request);
        $data['date'] = $request->input('date', now()->toDateString());
        $data['report_type'] = $request->input('report_type', 'summary');
        
        // Ensure detailed data arrays are included for PDF export
        if (!isset($data['assetDataDetailed'])) {
            $data['assetDataDetailed'] = [];
        }
        if (!isset($data['liabilityDataDetailed'])) {
            $data['liabilityDataDetailed'] = [];
        }
        if (!isset($data['capitalDataDetailed'])) {
            $data['capitalDataDetailed'] = [];
        }
        
        $selectedId = $request->query('selected_id');
        $company_id = Auth::user()->tenant_id ?? 0;
        $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
        $data['selected_name'] = $company ? $company->name : 'Company';
        $data['address'] = $company ? $company->address : '';
        $data['phone'] = $company ? $company->phone : '';
        $timestamp = now()->format('Ymd_His');
        
        // Choose appropriate blade file based on report type
        if ($data['report_type'] === 'detail') {
            $fileName = "balance_sheet_detail_{$timestamp}.pdf";
            $pdf = PDF::loadView('admin.reports.balance-sheet-detail-pdf', $data);
        } else {
            $fileName = "balance_sheet_summary_{$timestamp}.pdf";
            $pdf = PDF::loadView('admin.reports.balance-sheet-summary-pdf', $data);
        }
        
        return $pdf->download($fileName);
    }

    protected function getTrialBalanceData(Request $request)
    {
        $company_id = Auth::user()->tenant_id ?? 0;

        $today = now()->toDateString();
        $date = $request->input('date') ?: $today;

        $assetsAccountIds = DeAccount::where('root_type', 1)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();
        $liabilitiesAccountIds = DeAccount::where('root_type', 3)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();
        $expenseAccountIds = DeAccount::where('root_type', 2)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();
        $incomeAccountIds = DeAccount::where('root_type', 4)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();
        $capitalAccountIds = DeAccount::where('root_type', 5)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();



        $assetsAcData = DeAccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->whereIn('account_transactions.account_id', $assetsAccountIds)
            ->where('account_transactions.date', '<=', $date)
            ->select(
                'accounts.title',
                'accounts.id as account_id',
                'accounts.accountable_type',
                'accounts.accountable_id',
                DB::raw('SUM(account_transactions.debit) as total_debit'),
                DB::raw('SUM(account_transactions.credit) as total_credit'),
                DB::raw('SUM(account_transactions.debit) - SUM(account_transactions.credit) as balance')
            )
            ->groupBy('accounts.title', 'accounts.id', 'accounts.accountable_type', 'accounts.accountable_id')
            ->get();

        // Convert to DeAccount models to access accountable methods
        $assetsAcData = $assetsAcData->map(function($row) {
            $account = DeAccount::find($row->account_id);
            if ($account) {
                $row->accountable_alias = $account->getAccountableAliasAttribute();
                $row->accountable_name = $account->getAccountableAttribute() ? $account->getAccountableAttribute()->name : 'Unknown';
            } else {
                $row->accountable_alias = 'Unknown';
                $row->accountable_name = 'Unknown';
            }
            return $row;
        });
        
        $liabilitiesAcData = DeAccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->whereIn('account_transactions.account_id', $liabilitiesAccountIds)
            ->where('account_transactions.date', '<=', $date)
            ->select(
                'accounts.title',
                'accounts.id as account_id',
                'accounts.accountable_type',
                'accounts.accountable_id',
                DB::raw('SUM(account_transactions.debit) as total_debit'),
                DB::raw('SUM(account_transactions.credit) as total_credit'),
                DB::raw('SUM(account_transactions.credit) - SUM(account_transactions.debit) as balance')
            )
            ->groupBy('accounts.title', 'accounts.id', 'accounts.accountable_type', 'accounts.accountable_id')
            ->get();

        // Convert to DeAccount models to access accountable methods
        $liabilitiesAcData = $liabilitiesAcData->map(function($row) {
            $account = DeAccount::find($row->account_id);
            if ($account) {
                $row->accountable_alias = $account->getAccountableAliasAttribute();
                $row->accountable_name = $account->getAccountableAttribute() ? $account->getAccountableAttribute()->name : 'Unknown';
            } else {
                $row->accountable_alias = 'Unknown';
                $row->accountable_name = 'Unknown';
            }
            return $row;
        });

        $expenseAcData = DeAccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->whereIn('account_transactions.account_id', $expenseAccountIds)
            ->where('account_transactions.date', '<=', $date)
            ->select(
                'accounts.title',
                'accounts.id as account_id',
                'accounts.accountable_type',
                'accounts.accountable_id',
                DB::raw('SUM(account_transactions.debit) as total_debit'),
                DB::raw('SUM(account_transactions.credit) as total_credit'),
                DB::raw('SUM(account_transactions.debit) - SUM(account_transactions.credit) as balance')
            )
            ->groupBy('accounts.title', 'accounts.id', 'accounts.accountable_type', 'accounts.accountable_id')
            ->get();

        // Convert to DeAccount models to access accountable methods
        $expenseAcData = $expenseAcData->map(function($row) {
            $account = DeAccount::find($row->account_id);
            if ($account) {
                $row->accountable_alias = $account->getAccountableAliasAttribute();
                $row->accountable_name = $account->getAccountableAttribute() ? $account->getAccountableAttribute()->name : 'Unknown';
            } else {
                $row->accountable_alias = 'Unknown';
                $row->accountable_name = 'Unknown';
            }
            return $row;
        });
        
        $incomeAcData = DeAccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->whereIn('account_transactions.account_id', $incomeAccountIds)
            ->where('account_transactions.date', '<=', $date)
            ->select(
                'accounts.title',
                'accounts.id as account_id',
                'accounts.accountable_type',
                'accounts.accountable_id',
                DB::raw('SUM(account_transactions.debit) as total_debit'),
                DB::raw('SUM(account_transactions.credit) as total_credit'),
                DB::raw('SUM(account_transactions.credit) - SUM(account_transactions.debit) as balance')
            )
            ->groupBy('accounts.title', 'accounts.id', 'accounts.accountable_type', 'accounts.accountable_id')
            ->get();

        // Convert to DeAccount models to access accountable methods
        $incomeAcData = $incomeAcData->map(function($row) {
            $account = DeAccount::find($row->account_id);
            if ($account) {
                $row->accountable_alias = $account->getAccountableAliasAttribute();
                $row->accountable_name = $account->getAccountableAttribute() ? $account->getAccountableAttribute()->name : 'Unknown';
            } else {
                $row->accountable_alias = 'Unknown';
                $row->accountable_name = 'Unknown';
            }
            return $row;
        });

        $capitalAcData = DeAccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->whereIn('account_transactions.account_id', $capitalAccountIds)
            ->where('account_transactions.date', '<=', $date)
            ->select(
                'accounts.title',
                'accounts.id as account_id',
                'accounts.accountable_type',
                'accounts.accountable_id',
                DB::raw('SUM(account_transactions.debit) as total_debit'),
                DB::raw('SUM(account_transactions.credit) as total_credit'),
                DB::raw('SUM(account_transactions.credit) - SUM(account_transactions.debit) as balance')
            )
            ->groupBy('accounts.title', 'accounts.id', 'accounts.accountable_type', 'accounts.accountable_id')
            ->get();

        // Convert to DeAccount models to access accountable methods
        $capitalAcData = $capitalAcData->map(function($row) {
            $account = DeAccount::find($row->account_id);
            if ($account) {
                $row->accountable_alias = $account->getAccountableAliasAttribute();
                $row->accountable_name = $account->getAccountableAttribute() ? $account->getAccountableAttribute()->name : 'Unknown';
            } else {
                $row->accountable_alias = 'Unknown';
                $row->accountable_name = 'Unknown';
            }
            return $row;
        });

        
    

        // Group accounts by title for summary view
        $trialBalanceData = [
            'Assets' => [],
            'Liabilities' => [],
            'Capital' => [],
            'Income' => [],
            'Expense' => [],
        ];

        $trialBalanceDataDetailed = [
            'Assets' => [],
            'Liabilities' => [],
            'Capital' => [],
            'Income' => [],
            'Expense' => [],
        ];

        // Process Assets - group by title
        $assetsGrouped = [];
        foreach ($assetsAcData as $row) {
            $title = $row->title;
            $balance = (float) $row->balance;
            
            if (!isset($assetsGrouped[$title])) {
                $assetsGrouped[$title] = [
                    'title' => $title,
                    'amount' => 0,
                    'type' => 'debit', // Default type, will be updated after total calculation
                    'account_id' => $row->account_id,
                    'count' => 0
                ];
            }
            $assetsGrouped[$title]['amount'] += $balance;
            $assetsGrouped[$title]['count']++;
            
            // Add to detailed data
            $trialBalanceDataDetailed['Assets'][] = [
            'title' => $row->title,
                'amount' => abs($balance), // Use absolute value for display
                'type' => $balance >= 0 ? 'debit' : 'credit',
                'account_id' => $row->account_id,
                'accountable_type' => $row->accountable_type ?? null,
                'accountable_id' => $row->accountable_id ?? null,
                'accountable_alias' => $row->accountable_alias ?? 'Unknown',
                'accountable_name' => $row->accountable_name ?? 'Unknown'
            ];
        }
        
        // Update group types based on total balance
        foreach ($assetsGrouped as $title => $group) {
            $assetsGrouped[$title]['type'] = $group['amount'] >= 0 ? 'debit' : 'credit';
        }
        $trialBalanceData['Assets'] = array_values($assetsGrouped);

        // Process Liabilities - group by title
        $liabilitiesGrouped = [];
        foreach ($liabilitiesAcData as $row) {
            $title = $row->title;
            $balance = (float) $row->balance;
            
            if (!isset($liabilitiesGrouped[$title])) {
                $liabilitiesGrouped[$title] = [
                    'title' => $title,
                    'amount' => 0,
                    'type' => 'credit', // Default type, will be updated after total calculation
                    'account_id' => $row->account_id,
                    'count' => 0
                ];
            }
            $liabilitiesGrouped[$title]['amount'] += $balance;
            $liabilitiesGrouped[$title]['count']++;
            
            // Add to detailed data
            $trialBalanceDataDetailed['Liabilities'][] = [
            'title' => $row->title,
                'type' => $balance >= 0 ? 'credit' : 'debit',
                'amount' => abs($balance), // Use absolute value for display
                'account_id' => $row->account_id,
                'accountable_type' => $row->accountable_type ?? null,
                'accountable_id' => $row->accountable_id ?? null,
                'accountable_alias' => $row->accountable_alias ?? 'Unknown',
                'accountable_name' => $row->accountable_name ?? 'Unknown'
            ];
        }
        
        // Update group types based on total balance
        foreach ($liabilitiesGrouped as $title => $group) {
            $liabilitiesGrouped[$title]['type'] = $group['amount'] >= 0 ? 'credit' : 'debit';
        }
        $trialBalanceData['Liabilities'] = array_values($liabilitiesGrouped);

        // Process Capital - group by title
        $capitalGrouped = [];
        foreach ($capitalAcData as $row) {
            $title = $row->title;
            $balance = (float) $row->balance;
            
            if (!isset($capitalGrouped[$title])) {
                $capitalGrouped[$title] = [
                    'title' => $title,
                    'amount' => 0,
                    'type' => 'credit', // Default type, will be updated after total calculation
                    'account_id' => $row->account_id,
                    'count' => 0
                ];
            }
            $capitalGrouped[$title]['amount'] += $balance;
            $capitalGrouped[$title]['count']++;
            
            // Add to detailed data
            $trialBalanceDataDetailed['Capital'][] = [
            'title' => $row->title,
                'type' => $balance >= 0 ? 'credit' : 'debit',
                'amount' => abs($balance), // Use absolute value for display
                'account_id' => $row->account_id,
                'accountable_type' => $row->accountable_type ?? null,
                'accountable_id' => $row->accountable_id ?? null,
                'accountable_alias' => $row->accountable_alias ?? 'Unknown',
                'accountable_name' => $row->accountable_name ?? 'Unknown'
            ];
        }
        
        // Update group types based on total balance
        foreach ($capitalGrouped as $title => $group) {
            $capitalGrouped[$title]['type'] = $group['amount'] >= 0 ? 'credit' : 'debit';
        }
        $trialBalanceData['Capital'] = array_values($capitalGrouped);

        // Process Income - group by title
        $incomeGrouped = [];
        foreach ($incomeAcData as $row) {
            $title = $row->title;
            $balance = (float) $row->balance;
            
            if (!isset($incomeGrouped[$title])) {
                $incomeGrouped[$title] = [
                    'title' => $title,
                    'amount' => 0,
                    'type' => 'credit', // Default type, will be updated after total calculation
                    'account_id' => $row->account_id,
                    'count' => 0
                ];
            }
            $incomeGrouped[$title]['amount'] += $balance;
            $incomeGrouped[$title]['count']++;
            
            // Add to detailed data
            $trialBalanceDataDetailed['Income'][] = [
            'title' => $row->title,
                'type' => $balance >= 0 ? 'credit' : 'debit',
                'amount' => abs($balance), // Use absolute value for display
                'account_id' => $row->account_id,
                'accountable_type' => $row->accountable_type ?? null,
                'accountable_id' => $row->accountable_id ?? null,
                'accountable_alias' => $row->accountable_alias ?? 'Unknown',
                'accountable_name' => $row->accountable_name ?? 'Unknown'
            ];
        }
        
        // Update group types based on total balance
        foreach ($incomeGrouped as $title => $group) {
            $incomeGrouped[$title]['type'] = $group['amount'] >= 0 ? 'credit' : 'debit';
        }
        $trialBalanceData['Income'] = array_values($incomeGrouped);

        // Process Expense - group by title
        $expenseGrouped = [];
        foreach ($expenseAcData as $row) {
            $title = $row->title;
            $balance = (float) $row->balance;
            
            if (!isset($expenseGrouped[$title])) {
                $expenseGrouped[$title] = [
                    'title' => $title,
                    'amount' => 0,
                    'type' => 'debit', // Default type, will be updated after total calculation
                    'account_id' => $row->account_id,
                    'count' => 0
                ];
            }
            $expenseGrouped[$title]['amount'] += $balance;
            $expenseGrouped[$title]['count']++;
            
            // Add to detailed data
            $trialBalanceDataDetailed['Expense'][] = [
            'title' => $row->title,
                'amount' => abs($balance), // Use absolute value for display
                'type' => $balance >= 0 ? 'debit' : 'credit',
                'account_id' => $row->account_id,
                'accountable_type' => $row->accountable_type ?? null,
                'accountable_id' => $row->accountable_id ?? null,
                'accountable_alias' => $row->accountable_alias ?? 'Unknown',
                'accountable_name' => $row->accountable_name ?? 'Unknown'
            ];
        }
        
        // Update group types based on total balance
        foreach ($expenseGrouped as $title => $group) {
            $expenseGrouped[$title]['type'] = $group['amount'] >= 0 ? 'debit' : 'credit';
        }
        $trialBalanceData['Expense'] = array_values($expenseGrouped);

        // Calculate totals
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($trialBalanceData as $category => $accounts) {
            foreach ($accounts as $account) {
            if (isset($account['type']) && $account['type'] === 'debit') {
                $totalDebit += $account['amount'];
            } elseif (isset($account['type']) && $account['type'] === 'credit') {
                $totalCredit += $account['amount'];
            }
            }
        }

        return compact('trialBalanceData', 'trialBalanceDataDetailed', 'totalDebit', 'totalCredit');
    }

    public function trialBalance(Request $request)
    {
        // Check if this is a PDF export request
        if ($request->input('format') === 'pdf') {
            return $this->trialBalanceExport($request);
        }
        
        // Normal view request
        $data = $this->getTrialBalanceData($request);
        $stores = Store::select('id', 'name')->get();
        return view('admin.reports.trial-balance', $data, compact('stores'));
    }

    public function trialBalanceExport(Request $request)
    {
        $data = $this->getTrialBalanceData($request);
        $data['date'] = $request->input('date', now()->toDateString());
        $data['report_type'] = $request->input('report_type', 'summary');
        
        // Ensure detailed data arrays are included for PDF export
        if (!isset($data['trialBalanceDataDetailed'])) {
            $data['trialBalanceDataDetailed'] = [];
        }
        
        $company_id = Auth::user()->tenant_id ?? 0;
        $company = Company::select('name', 'office_address as address', 'contact_no as phone')->find($company_id);
        $data['selected_name'] = $company ? $company->name : 'Company';
        $data['address'] = $company ? $company->address : '';
        $data['phone'] = $company ? $company->phone : '';
        $timestamp = now()->format('Ymd_His');
        
        // Choose appropriate blade file based on report type
        if ($data['report_type'] === 'detail') {
            $fileName = "trial_balance_detail_{$timestamp}.pdf";
            $pdf = PDF::loadView('admin.reports.trial-balance-detail-pdf', $data);
        } else {
            $fileName = "trial_balance_summary_{$timestamp}.pdf";
            $pdf = PDF::loadView('admin.reports.trial-balance-summary-pdf', $data);
        }
        
        return $pdf->download($fileName);
    }

    protected function getProfit(Request $request)
    {
        $company_id = Auth::user()->tenant_id ?? 0;

        $today = now()->toDateString();
        $date = $request->input('date') ?: $today;

        $salesIncomeAccountIds = DeAccount::where('root_type', 4)
            ->whereNotNull('parent_id')
            ->where('account_type_id', '!=', 8) //exclude other income accounts
            ->pluck('id')
            ->toArray();
        $otherIncomeAccountIds = DeAccount::where('root_type', 4)
            ->whereNotNull('parent_id')
            ->where('account_type_id', 8) //only other income accounts
            ->pluck('id')
            ->toArray();
        $expenseAccountIds = DeAccount::where('root_type', 2)
            ->where('account_type_id', 7)
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();
        $cogsAccountIds = DeAccount::where('root_type', 2)
            ->where('title', 'Cost of Goods Sold')
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->toArray();

        // Fetch sales data
        // Get total amount for each credit account in the date range
        // Fetch total sales for only the selected income accounts
        $salesIncomeAcData = DeJournal::join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
            ->where('de_journals.date', '<=', $date)
            ->whereIn('credit.account_id', $salesIncomeAccountIds)
            ->sum('de_journals.amount');
        $otherIncomeAcData = DeJournal::join('account_transactions as credit', 'de_journals.credit_transaction_id', '=', 'credit.id')
            ->where('de_journals.date', '<=', $date)
            ->whereIn('credit.account_id', $otherIncomeAccountIds)
            ->sum('de_journals.amount');

        // Similarly get expense account data
        $expenseAcData = DeJournal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
            ->where('de_journals.date', '<=', $date)
            ->whereIn('debit.account_id', $expenseAccountIds)
            ->sum('de_journals.amount');
        // Fetch COGS data
        $cogsAcData = DeJournal::join('account_transactions as debit', 'de_journals.debit_transaction_id', '=', 'debit.id')
            ->where('de_journals.date', '<=', $date)
            ->whereIn('debit.account_id', $cogsAccountIds)
            ->sum('de_journals.amount');

        // Calculate profit
        $totalIncome = $salesIncomeAcData + $otherIncomeAcData;
        $totalExpense = $expenseAcData + $cogsAcData;
        $profit = $totalIncome - $totalExpense;

        return $profit;

    }
}
