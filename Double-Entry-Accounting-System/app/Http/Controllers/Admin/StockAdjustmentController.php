<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stockAdjustments = StockAdjustment::with('store')->latest()->paginate(20);
        return view('admin.stock-adjustment.index', compact('stockAdjustments'));
    }

    public function create()
    {
        return view('admin.stock-adjustment.create');
    }

    public function show(StockAdjustment $stock_adjustment)
    {
        $stock_adjustment->load(['store', 'productStockAdjustments.product']);
        return view('admin.stock-adjustment.show', compact('stock_adjustment'));
    }

    public function edit(StockAdjustment $stock_adjustment)
    {
        $stores = Store::all();
        $products = Product::all();
        $stock_adjustment->load('productStockAdjustments');
        return view('admin.stock-adjustment.edit', compact('stock_adjustment', 'stores', 'products'));
    }

    public function destroy(StockAdjustment $stock_adjustment)
    {
        $stock_adjustment->productStockAdjustments()->delete();
        $stock_adjustment->delete();

        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment deleted successfully.');
    }
}