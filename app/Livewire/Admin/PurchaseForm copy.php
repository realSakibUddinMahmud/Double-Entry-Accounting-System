<?php

namespace App\Livewire\Admin;

use App\Models\Tax;
use App\Models\Unit;
use App\Models\Store;
use App\Models\Product;
use Livewire\Component;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\PurchaseItem;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Hilinkz\DEAccounting\Models\DE;
use Illuminate\Support\Facades\Auth;
use Hilinkz\DEAccounting\Models\DeTask;
use Illuminate\Support\Facades\Validator;

class PurchaseForm extends Component
{
    // Form fields
    public $supplier_id, $store_id, $purchase_date, $tax_id, $shipping_cost = 0, $discount_amount = 0, $status = 1;
    public $product_id, $unit_id, $quantity, $per_unit_cost;
    public $product_items = [];
    public $total_amount = 0, $paid_amount = 0, $due_amount = 0, $payment_status = 'Pending';
    public $note = '';

    // Dropdown data
    public $suppliers = [], $stores = [], $products = [], $units = [], $taxes = [];

    // Search functionality
    public $product_search = '';
    public $product_suggestions = [];

    public $purchaseId;
    public $mode = 'create';

    public function mount($purchaseId = null, $mode = 'create')
    {
        $this->suppliers = Supplier::all();
        $this->stores = Store::all();
        $this->products = Product::all();
        $this->units = Unit::all();
        $this->taxes = Tax::all();
        $this->purchase_date = now()->format('Y-m-d');

        $this->purchaseId = $purchaseId;
        $this->mode = $mode;

        if ($this->mode === 'edit' && $this->purchaseId) {
            $purchase = Purchase::with('items')->findOrFail($this->purchaseId);
            $this->supplier_id = $purchase->supplier_id;
            $this->store_id = $purchase->store_id;
            $this->purchase_date = $purchase->purchase_date;
            $this->tax_id = $purchase->tax_id;
            $this->shipping_cost = $purchase->shipping_cost;
            $this->discount_amount = $purchase->discount_amount;
            $this->status = $purchase->status;
            $this->paid_amount = $purchase->paid_amount;
            $this->payment_status = $purchase->payment_status;
            $this->note = $purchase->note;
            $this->total_amount = $purchase->total_amount;
            $this->due_amount = $purchase->due_amount;

            $this->product_items = [];
            foreach ($purchase->items as $item) {
                // Get base_unit_id from product_store or fallback to unit_id
                $baseUnitId = $item->unit_id; // Default fallback
                if ($item->product && $purchase->store_id) {
                    $productStore = $item->product->productStores()
                        ->where('store_id', $purchase->store_id)
                        ->first();
                    if ($productStore) {
                        $baseUnitId = $productStore->base_unit_id ?? $item->unit_id;
                    }
                }

                $this->product_items[] = [
                    'product_id' => $item->product_id,
                    'product_name' => optional($item->product)->name,
                    'unit_id' => $item->unit_id,
                    'unit_name' => optional($item->unit)->name, // Add this if needed in blade
                    'base_unit_id' => $baseUnitId, // Add this line
                    'quantity' => $item->quantity,
                    'per_unit_cost' => $item->per_unit_cost,
                    'total' => $item->total,
                ];
            }
        }
    }

    public function updated($property)
    {
        $this->calculateTotals();
    }
    public function changeStore($value)
    {
        $this->store_id = $value;
    }

    public function inputProductSearch()
    {
        if (!$this->store_id) {
            $this->product_suggestions = [];
            return;
        }
        $search = trim($this->product_search);
        if ($search) {
            $this->product_suggestions = $this->products
                ->filter(function($product) use ($search) {
                    return str_contains(strtolower($product->name), strtolower($search))
                        || str_contains(strtolower($product->sku ?? ''), strtolower($search))
                        || str_contains(strtolower($product->barcode ?? ''), strtolower($search));
                })
                ->take(10)
                ->values();
        } else {
            $this->product_suggestions = [];
        }
    }

    public function addItem()
    {
        $connection = app(Purchase::class)->getConnectionName();

        $validator = Validator::make([
            'product_id'    => $this->product_id,
            'unit_id'       => $this->unit_id,
            'quantity'      => $this->quantity,
            'per_unit_cost' => $this->per_unit_cost,
        ], [
            'product_id'    => ['required', Rule::exists($connection . '.products', 'id')],
            'unit_id'       => ['required', Rule::exists($connection . '.units', 'id')],
            'quantity'      => ['required', 'numeric', 'min:1'],
            'per_unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->validate();

        $product = $this->products->find($this->product_id);
        $unit = $this->units->find($this->unit_id);

        $item = [
            'product_id' => $this->product_id,
            'product_name' => $product ? $product->name : '',
            'unit_id' => $this->unit_id,
            'unit_name' => $unit ? $unit->name : '',
            'quantity' => $this->quantity,
            'per_unit_cost' => $this->per_unit_cost,
            'total' => $this->quantity * $this->per_unit_cost,
        ];

        $this->product_items[] = $item;

        // Reset product fields
        $this->product_id = $this->unit_id = $this->quantity = $this->per_unit_cost = null;

        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->product_items[$index]);
        $this->product_items = array_values($this->product_items);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $subtotal = collect($this->product_items)->sum(function($item) {
            // Ensure quantity and per_unit_cost are valid numbers
            $qty = (is_numeric($item['quantity']) && $item['quantity'] !== null) ? (float)$item['quantity'] : 0;
            $cost = (is_numeric($item['per_unit_cost']) && $item['per_unit_cost'] !== null) ? (float)$item['per_unit_cost'] : 0;
            return $qty * $cost;
        });

        $shipping = is_numeric($this->shipping_cost) ? floatval($this->shipping_cost) : 0;
        $discount = is_numeric($this->discount_amount) ? floatval($this->discount_amount) : 0;

        $this->total_amount = $subtotal + $shipping - $discount;
        if ($this->total_amount < 0) {
            $this->total_amount = 0;
        }

        $paid = is_numeric($this->paid_amount) ? floatval($this->paid_amount) : 0;
        $this->due_amount = $this->total_amount - $paid;
        if ($this->due_amount < 0) {
            $this->due_amount = 0;
        }
    }

    public function generateUniqueUid()
    {
        do {
            $u_id = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (\App\Models\Purchase::where('u_id', $u_id)->exists());
        return $u_id;
    }

    public function save()
    {
        $connection = app(Purchase::class)->getConnectionName();

        $validator = Validator::make([
            'supplier_id'    => $this->supplier_id,
            'store_id'       => $this->store_id,
            'purchase_date'  => $this->purchase_date,
            'tax_id'         => $this->tax_id,
            'shipping_cost'  => $this->shipping_cost,
            'discount_amount' => $this->discount_amount,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'paid_amount'    => $this->paid_amount,
            'product_items'  => $this->product_items,
        ], [
            'supplier_id'    => ['required', Rule::exists($connection . '.suppliers', 'id')],
            'store_id'       => ['required', Rule::exists($connection . '.stores', 'id')],
            'purchase_date'  => ['required', 'date'],
            'tax_id'         => ['nullable', Rule::exists($connection . '.taxes', 'id')],
            'shipping_cost'  => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['required', 'boolean'],
            'payment_status' => ['required', Rule::in(['Pending', 'Partial', 'Paid'])],
            'paid_amount'    => ['required', 'numeric', 'min:0'],
            'product_items'  => ['required', 'array', 'min:1'],
            'product_items.*.quantity' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'product_items.*.per_unit_cost' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $validator->validate();

        // Calculate total cost (sum of all product costs without shipping/discount)
        $total_cost = collect($this->product_items)->sum(function($item) {
            $qty = (float)$item['quantity'];
            $cost = (float)$item['per_unit_cost'];
            return $qty * $cost;
        });

        DB::beginTransaction();
        try {
            if ($this->mode === 'edit' && $this->purchaseId) {
                $purchase = Purchase::findOrFail($this->purchaseId);
                $task = $purchase->tasks()->latest()->first();
                if ($task) {
                    foreach ($task->journals()->where('transaction_type', 'PURCHASE-DUE')->get() as $journal) {
                        if ($journal) {
                            $result = DE::delete($journal);
                            if ($result['status'] && $result['status'] != true) {
                                DB::rollBack();
                                return redirect()->back()->with('error', 'Something went wrong.');
                            }
                        }
                    }
                    foreach ($task->journals()->where('transaction_type', 'PURCHASE-SHIPPING-COST-DUE')->get() as $journal) {
                        if ($journal) {
                            $result = DE::delete($journal);
                            if ($result['status'] && $result['status'] != true) {
                                DB::rollBack();
                                return redirect()->back()->with('error', 'Something went wrong.');
                            }
                        }
                    }
                    foreach ($task->journals()->where('transaction_type', 'PURCHASE-DISCOUNT')->get() as $journal) {
                        if ($journal) {
                            $result = DE::delete($journal);
                            if ($result['status'] && $result['status'] != true) {
                                DB::rollBack();
                                return redirect()->back()->with('error', 'Something went wrong.');
                            }
                        }
                    }
                }
                $purchase->update([
                    'supplier_id' => $this->supplier_id,
                    'store_id' => $this->store_id,
                    'purchase_date' => $this->purchase_date,
                    'total_amount' => $this->total_amount,
                    'total_cost' => $total_cost, // Add this line
                    'paid_amount' => $this->paid_amount,
                    'due_amount' => $this->due_amount,
                    'shipping_cost' => $this->shipping_cost,
                    'discount_amount' => $this->discount_amount,
                    'tax_id' => $this->tax_id,
                    'total_tax' => 0,
                    'status' => $this->status,
                    'payment_status' => $this->payment_status,
                    'user_id' => Auth::id(),
                    'note' => $this->note,
                ]);
                // Remove old items
                $purchase->items()->delete();
            } else {
                $purchase = Purchase::create([
                    'u_id' => $this->generateUniqueUid(),
                    'supplier_id' => $this->supplier_id,
                    'store_id' => $this->store_id,
                    'purchase_date' => $this->purchase_date,
                    'total_amount' => $this->total_amount,
                    'total_cost' => $total_cost, // Add this line
                    'paid_amount' => $this->paid_amount,
                    'due_amount' => $this->due_amount,
                    'shipping_cost' => $this->shipping_cost,
                    'discount_amount' => $this->discount_amount,
                    'tax_id' => $this->tax_id,
                    'total_tax' => 0,
                    'status' => $this->status,
                    'payment_status' => $this->payment_status,
                    'user_id' => Auth::id(),
                    'note' => $this->note,
                ]);

                $task = DeTask::create([
                    'company_id'    => null, // Set your company_id
                    'name'          => 'Purchase',
                    'taskable_id'   => $purchase->id, // Set related model ID if needed
                    'taskable_type' => Purchase::class, // Set related model class if needed
                    'note'          => $purchase->note,
                ]);
                
            }

            foreach ($this->product_items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'per_unit_cost' => $item['per_unit_cost'],
                    'total' => $item['total'],
                ]);
            }
            if(!$task) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Something went wrong.');
            }
            if ($this->shipping_cost > 0) { 
                $this->purchaseShippingCostAccounting($purchase->id, $task->id);  
            }
            if ($this->discount_amount > 0) {
                $this->purchaseDiscountAccounting($purchase->id, $task->id);
            }
            $this->purchaseDueAccounting($purchase->id, $task->id);

            DB::commit();
            session()->flash('success', $this->mode === 'edit' ? 'Purchase updated successfully!' : 'Purchase created successfully!');
            return redirect()->route('purchases.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save purchase: ' . $e->getMessage());
            return;
        }
    }

    public function purchaseDueAccounting($purchaseId,$taskId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $store = Store::findOrFail($purchase->store_id);
        $supplier_id = $purchase->supplier_id;
        $supplier = Supplier::findOrFail($supplier_id);

        $inventoryAccount = $store->accounts()
            ->where('title', 'Inventory')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $payableAccount = $supplier->accounts()
            ->where('title','like','%Payable%')
            ->where('root_type', 3)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        if (!$inventoryAccount || !$payableAccount) {
            DB::rollBack();
            session()->flash('error', 'Inventory or Payable account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $inventoryAccount;
        $destinationAccount = $payableAccount;

        $requestedData = array();
        $requestedData['date'] = $purchase->purchase_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $purchase->note ?? null;
        $requestedData['amount'] = $purchase->total_cost-$purchase->discount_amount;
        $requestedData['journalable_type'] = Purchase::class;
        $requestedData['journalable_id'] = $purchase->id;

        $deType = 'UPUP';
        $eventName = 'PURCHASE-DUE';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }
    public function purchaseShippingCostAccounting($purchaseId,$taskId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $store = Store::findOrFail($purchase->store_id);

        $carriageAccount = $store->accounts()
            ->where('title', 'Carriage Inwards')
            ->where('root_type',2)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $transportationPayableAccount = $store->accounts()
            ->where('title', 'Transportation Payable')
            ->where('root_type', 3)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        if (!$carriageAccount || !$transportationPayableAccount) {
            DB::rollBack();
            session()->flash('error', 'Carriage or Transportation Payable account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $carriageAccount;
        $destinationAccount = $transportationPayableAccount;

        $requestedData = array();
        $requestedData['date'] = $purchase->purchase_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $purchase->note ?? null;
        $requestedData['amount'] = $purchase->shipping_cost ?? 0;
        $requestedData['journalable_type'] = Purchase::class;
        $requestedData['journalable_id'] = $purchase->id;

        $deType = 'UPUP';
        $eventName = 'PURCHASE-SHIPPING-COST-DUE';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }
    public function purchaseDiscountAccounting($purchaseId, $taskId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $store = Store::findOrFail($purchase->store_id);

        $discountAccount = $store->accounts()
            ->where('title', 'Discount Received')
            ->where('root_type', 4)
            ->where('account_type_id', 8)
            ->whereNotNull('parent_id')
            ->first();

        $inventoryAccount = $store->accounts()
            ->where('title', 'Inventory')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        if (!$discountAccount || !$inventoryAccount) {
            DB::rollBack();
            session()->flash('error', 'Discount or Inventory account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $discountAccount;
        $destinationAccount = $inventoryAccount;

        $requestedData = array();
        $requestedData['date'] = $purchase->purchase_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $purchase->note ?? null;
        $requestedData['amount'] = $purchase->discount_amount ?? 0;
        $requestedData['journalable_type'] = Purchase::class;
        $requestedData['journalable_id'] = $purchase->id;

        $deType = 'UPUP';
        $eventName = 'PURCHASE-DISCOUNT';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }

    public function selectProduct($productId)
    {
        $product = $this->products->find($productId);
        if (!$product) return;

        // Get purchase cost from product_store for the selected store
        $purchaseCost = 0;
        $unitId = $this->units->first()->id ?? null;

        if ($this->store_id && $product) {
            $productStore = $product->productStores()
                ->where('store_id', $this->store_id)
                ->first();
            if ($productStore) {
                $purchaseCost = $productStore->purchase_cost ?? 0;
                $purchaseUnitId = $productStore->purchase_unit_id ?? $unitId;
                $baseUnitId = $productStore->base_unit_id ?? $unitId;
            }
        }

        // Check if already in cart
        foreach ($this->product_items as $i => $item) {
            if ($item['product_id'] == $productId) {
                $this->product_items[$i]['quantity']++;
                $this->calculateTotals();
                $this->product_search = '';
                $this->product_suggestions = [];
                return;
            }
        }

        // Add new product to cart
        $this->product_items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_id' => $purchaseUnitId,
            'base_unit_id' => $baseUnitId, // Store for filtering units in the view
            'quantity' => 1,
            'per_unit_cost' => $purchaseCost,
            'total' => $purchaseCost,
        ];

        $this->product_search = '';
        $this->product_suggestions = [];
        $this->calculateTotals();
    }

    public function changeProductQty($value, $index)
    {
        $qty = max(1, (float)$value);
        $this->product_items[$index]['quantity'] = $qty;
        $this->product_items[$index]['total'] = $qty * $this->product_items[$index]['per_unit_cost'];
        $this->calculateTotals();
    }
    public function changeProductCost($value, $index)
    {
        $cost = max(1, (float)$value);
        $this->product_items[$index]['per_unit_cost'] = $cost;
        $this->product_items[$index]['total'] = $cost * $this->product_items[$index]['quantity'];
        $this->calculateTotals();
    }

    public function changePaidAmount()
    {
        $this->calculateTotals();
    }

    public function changeShippingCost()
    {
        $this->calculateTotals();
    }

    public function changeDiscountAmount()
    {
        $this->calculateTotals();
    }

    public function render()
    {
        return view('livewire.admin.purchase-form');
    }
}
