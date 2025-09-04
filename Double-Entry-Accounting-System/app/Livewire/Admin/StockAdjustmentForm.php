<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StockAdjustment;
use App\Models\ProductStockAdjustment;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StockAdjustmentForm extends Component
{
    public $stock_adjustment_id;
    public $store_id;
    public $date;
    public $note;
    public $product_search = '';
    public $product_suggestions = [];
    public $product_items = [];

    public $allStores = [];
    public $allProducts = [];

    public function mount($stock_adjustment_id = null)
    {
        $this->allStores = Store::all();
        $this->allProducts = Product::all();

        if ($stock_adjustment_id) {
            $this->stock_adjustment_id = $stock_adjustment_id;
            $stockAdjustment = StockAdjustment::with('productStockAdjustments.product.productStores.base_unit')->findOrFail($stock_adjustment_id);
            $this->store_id = $stockAdjustment->store_id;
            $this->date = $stockAdjustment->date;
            $this->note = $stockAdjustment->note;
            $this->product_items = [];
            foreach ($stockAdjustment->productStockAdjustments as $item) {
                // Get the productStore for the selected store
                $productStore = $item->product
                    ? $item->product->productStores->where('store_id', $stockAdjustment->store_id)->first()
                    : null;
                $baseUnitName = $productStore && $productStore->base_unit ? $productStore->base_unit->name : '';

                $this->product_items[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : '',
                    'base_unit_name' => $baseUnitName,
                    'action' => $item->action,
                    'quantity' => $item->quantity,
                ];
            }
        } else {
            $this->date = date('Y-m-d');
        }
    }
    public function changeStore($value)
    {
        $this->store_id = $value;
    }

    public function inputProductSearch($value)
    {
        $this->product_search = $value;
        if (!$this->store_id || strlen($value) < 1) {
            $this->product_suggestions = [];
            return;
        }
        $this->product_suggestions = Product::where('name', 'like', "%{$value}%")
            ->orWhere('sku', 'like', "%{$value}%")
            ->orWhere('barcode', 'like', "%{$value}%")
            ->limit(10)
            ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::with(['productStores.base_unit'])->find($productId);
        if (!$product) return;

        // Prevent duplicate
        foreach ($this->product_items as $item) {
            if ($item['product_id'] == $productId) {
                session()->flash('error', 'Product already added.');
                $this->product_search = '';
                $this->product_suggestions = [];
                return;
            }
        }

        // Get the productStore for the selected store
        $productStore = $product->productStores->where('store_id', $this->store_id)->first();
        $baseUnitName = $productStore && $productStore->base_unit ? $productStore->base_unit->name : '';

        $this->product_items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'base_unit_name' => $baseUnitName,
            'action' => '+',
            'quantity' => 1,
        ];

        $this->product_search = '';
        $this->product_suggestions = [];
    }

    public function removeItem($index)
    {
        unset($this->product_items[$index]);
        $this->product_items = array_values($this->product_items);
    }

    public function save()
    {
        $connection = app(StockAdjustment::class)->getConnectionName();

        $validator = Validator::make([
            'store_id'      => $this->store_id,
            'date'          => $this->date,
            'note'          => $this->note,
            'product_items' => $this->product_items,
        ], [
            'store_id'      => ['required', Rule::exists($connection . '.stores', 'id')],
            'date'          => ['required', 'date'],
            'note'          => ['nullable', 'string'],
            'product_items' => ['required', 'array', 'min:1'],
            'product_items.*.product_id' => ['required', Rule::exists($connection . '.products', 'id')],
            'product_items.*.action'     => ['required', Rule::in(['+', '-'])],
            'product_items.*.quantity'   => [
                'required',
                'numeric',
                'min:0.01',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
        ]);

        $validator->validate();

        if ($this->stock_adjustment_id) {
            $stockAdjustment = StockAdjustment::findOrFail($this->stock_adjustment_id);
            $stockAdjustment->update([
                'store_id' => $this->store_id,
                'date' => $this->date,
                'note' => $this->note,
                'user_id' => Auth::id(),
            ]);
            $stockAdjustment->productStockAdjustments()->delete();
        } else {
            $stockAdjustment = StockAdjustment::create([
                'store_id' => $this->store_id,
                'date' => $this->date,
                'note' => $this->note,
                'user_id' => Auth::id(),
            ]);
        }

        foreach ($this->product_items as $item) {
            ProductStockAdjustment::create([
                'stock_adjustment_id' => $stockAdjustment->id,
                'product_id' => $item['product_id'],
                'action' => $item['action'],
                'quantity' => $item['quantity'],
                'user_id' => Auth::id(),
            ]);
        }

        session()->flash('success', 'Stock adjustment saved successfully.');
        return redirect()->route('stock-adjustments.index');
    }

    public function render()
    {
        return view('livewire.admin.stock-adjustment-form');
    }
}