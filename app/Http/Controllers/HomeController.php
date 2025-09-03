<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\ProductStockAdjustment;
use App\Models\Supplier;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            //check tenant is it loandlord or tenant
            $currentTenant = app('currentTenant');
            if (!$currentTenant || strpos($currentTenant->database, 'landlord') !== false) {
                return view('admin.home.landlord');
            }
            else {
                // Use try-catch to handle database connection issues
                try {
                    $totalPurchaseAmount = Purchase::sum('total_amount') ?? 0; // Calculate the total purchases amount
                    $totalSaleAmount = Sale::sum('total_amount') ?? 0; // Calculate the total sales amount
                    $totalReceivedAmount = Sale::sum('paid_amount') ?? 0; // Calculate the total paid sales amount
                    $totalDueAmount = Sale::sum('due_amount') ?? 0; // Calculate the total due sales amount
                    $totalCustomerCount = Customer::count() ?? 0; // Count the total number of customers
                    $totalSupplierCount = Supplier::count() ?? 0; // Count the total number of suppliers
                    $totalPurchaseCount = Purchase::count() ?? 0; // Count the total number of purchases
                    $totalSalesCount = Sale::count() ?? 0; // Count the total number of sales
                } catch (\Exception $e) {
                    // If database connection fails, set default values
                    $totalPurchaseAmount = 0;
                    $totalSaleAmount = 0;
                    $totalReceivedAmount = 0;
                    $totalDueAmount = 0;
                    $totalCustomerCount = 0;
                    $totalSupplierCount = 0;
                    $totalPurchaseCount = 0;
                    $totalSalesCount = 0;
                }
            

            $year = now()->year;

            // Get sales and purchase totals for each month
            $salesByMonth = Sale::selectRaw('MONTH(sale_date) as month, SUM(total_amount) as total')
                ->whereYear('sale_date', $year)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $purchasesByMonth = Purchase::selectRaw('MONTH(purchase_date) as month, SUM(total_amount) as total')
                ->whereYear('purchase_date', $year)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            // Prepare arrays for all 12 months
            $months = [];
            $salesData = [];
            $purchasesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[] = Carbon::create()->month($m)->format('M');
                $salesData[] = isset($salesByMonth[$m]) ? (float)$salesByMonth[$m] : 0;
                $purchasesData[] = isset($purchasesByMonth[$m]) ? (float)$purchasesByMonth[$m] : 0;
            }

            return view('admin.home.tenant', compact(
                'totalPurchaseAmount', 'totalSaleAmount', 'totalReceivedAmount', 'totalDueAmount',
                'totalCustomerCount', 'totalSupplierCount', 'totalPurchaseCount', 'totalSalesCount',
                'months', 'salesData', 'purchasesData'
            ));
        }
        } catch (\Exception $e) {
            // If there's any error, redirect to landlord dashboard
            return view('admin.home.landlord');
        }
    }

    public function summaryData(Request $request)
    {
        try {
            $range = $request->query('range', 'lifetime');
            $querySale = Sale::query();
            $queryPurchase = Purchase::query();

        // Filter by range
        switch ($range) {
            case 'today':
                $querySale->whereDate('sale_date', today());
                $queryPurchase->whereDate('purchase_date', today());
                break;
            case 'week':
                $querySale->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
                $queryPurchase->whereBetween('purchase_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $querySale->whereMonth('sale_date', now()->month)->whereYear('sale_date', now()->year);
                $queryPurchase->whereMonth('purchase_date', now()->month)->whereYear('purchase_date', now()->year);
                break;
            case 'year':
                $querySale->whereYear('sale_date', now()->year);
                $queryPurchase->whereYear('purchase_date', now()->year);
                break;
            case 'lifetime':
            default:
                // No filter
                break;
        }

        $totalSaleAmount = $querySale->sum('total_amount');
        $totalPurchaseAmount = $queryPurchase->sum('total_amount');
        $totalReceivedAmount = $querySale->sum('paid_amount');
        $totalDueAmount = $querySale->sum('due_amount');

        return response()->json([
            'totalSaleAmount' => $totalSaleAmount,
            'totalPurchaseAmount' => $totalPurchaseAmount,
            'totalReceivedAmount' => $totalReceivedAmount,
            'totalDueAmount' => $totalDueAmount,
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'totalSaleAmount' => 0,
                'totalPurchaseAmount' => 0,
                'totalReceivedAmount' => 0,
                'totalDueAmount' => 0,
            ]);
        }
    }
}
