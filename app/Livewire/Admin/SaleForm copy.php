<?php

namespace App\Livewire\Admin;

use App\Models\Tax;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\Store;
use App\Models\Product;
use Livewire\Component;
use App\Models\Customer;
use App\Models\SaleItem;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Hilinkz\DEAccounting\Models\DE;
use Illuminate\Support\Facades\Auth;
use Hilinkz\DEAccounting\Models\DeTask;
use Illuminate\Support\Facades\Validator;
use Hilinkz\DEAccounting\Models\DeAccount;

class SaleForm extends Component
{
    // Form fields
    public $customer_id, $store_id, $sale_date, $tax_id, $shipping_cost = 0, $discount_amount = 0, $status = 1;
    public $product_id, $unit_id, $quantity, $per_unit_price, $payment_account_id;
    public $product_items = [];
    public $total_amount = 0, $paid_amount = 0, $due_amount = 0, $payment_status = 'Pending';
    public $note = '';

    public $payment_accounts = [];

    // Dropdown data
    public $customers = [], $stores = [], $products = [], $units = [], $taxes = [];

    // Search functionality
    public $product_search = '';
    public $product_suggestions = [];

    public $saleId;
    public $mode = 'create';

    public $disable_payment_fields = false;

    public function mount($saleId = null, $mode = 'create')
    {
        $this->customers = Customer::all();
        $this->stores = Store::all();
        $this->products = Product::all();
        $this->units = Unit::all();
        $this->taxes = Tax::all();
        $this->sale_date = now()->format('Y-m-d');

        $this->saleId = $saleId;
        $this->mode = $mode;

        if ($this->mode === 'edit' && $this->saleId) {
            $sale = \App\Models\Sale::with(['tasks.journals'])->findOrFail($this->saleId);

            $this->customer_id = $sale->customer_id;
            $this->store_id = $sale->store_id;
            $this->sale_date = $sale->sale_date;
            $this->tax_id = $sale->tax_id;
            $this->shipping_cost = $sale->shipping_cost;
            $this->discount_amount = $sale->discount_amount;
            $this->status = $sale->status;
            $this->paid_amount = $sale->paid_amount;
            $this->payment_status = $sale->payment_status;
            $this->note = $sale->note;
            $this->total_amount = $sale->total_amount;
            $this->due_amount = $sale->due_amount;

            $this->product_items = [];
            foreach ($sale->items as $item) {
                $this->product_items[] = [
                    'product_id' => $item->product_id,
                    'product_name' => optional($item->product)->name,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->quantity,
                    'per_unit_price' => $item->per_unit_price,
                    'total' => $item->total,
                ];
            }

            // Auto-load payment accounts if status is Paid or Partial
            if (in_array($this->payment_status, ['Paid', 'Partial'])) {
                $store = $sale->store;
                if ($store) {
                    $this->payment_accounts = $store->accounts()
                        ->where(function($q) {
                            $q->where('account_type_id', 2)
                              ->orWhere('title', 'Cash');
                        })
                        ->whereNotNull('parent_id')
                        ->where('root_type', 1)
                        ->get();
                } else {
                    $this->payment_accounts = [];
                }
            }

            // Find the latest payment account used for this sale
            $lastPaymentAccountId = null;
            foreach ($sale->tasks as $task) {
                $journal = $task->journals()
                    ->where('transaction_type', 'SALE-PAYMENT')
                    ->latest()
                    ->with('debittransaction')
                    ->first();

                if ($journal && $journal->debittransaction) {
                    $lastPaymentAccountId = $journal->debittransaction->account_id;
                    break;
                }
            }
            $this->payment_account_id = $lastPaymentAccountId;

            // Check for multiple SALE-PAYMENT journals
            $salePaymentJournalCount = 0;
            foreach ($sale->tasks as $task) {
                $salePaymentJournalCount += $task->journals()
                    ->where('transaction_type', 'SALE-PAYMENT')
                    ->count();
            }
            if ($salePaymentJournalCount > 1) {
                $this->disable_payment_fields = true;
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
        $connection = app(Sale::class)->getConnectionName();

        $validator = Validator::make([
            'product_id'    => $this->product_id,
            'unit_id'       => $this->unit_id,
            'quantity'      => $this->quantity,
            'per_unit_price' => $this->per_unit_price,
        ], [
            'product_id'    => ['required', Rule::exists($connection . '.products', 'id')],
            'unit_id'       => ['required', Rule::exists($connection . '.units', 'id')],
            'quantity'      => ['required', 'numeric', 'min:1'],
            'per_unit_price' => ['required', 'numeric', 'min:0'],
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
            'per_unit_price' => $this->per_unit_price,
            'total' => $this->quantity * $this->per_unit_price,
        ];

        $this->product_items[] = $item;

        // Reset product fields
        $this->product_id = $this->unit_id = $this->quantity = $this->per_unit_price = null;

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
            // Ensure quantity and per_unit_price are valid numbers
            $qty = (is_numeric($item['quantity']) && $item['quantity'] !== null) ? (float)$item['quantity'] : 0;
            $price = (is_numeric($item['per_unit_price']) && $item['per_unit_price'] !== null) ? (float)$item['per_unit_price'] : 0;
            return $qty * $price;
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
        } while (\App\Models\Sale::where('u_id', $u_id)->exists());
        return $u_id;
    }

    public function save()
    {
        $connection = app(Sale::class)->getConnectionName();

        $validator = Validator::make([
            'customer_id'    => $this->customer_id,
            'store_id'       => $this->store_id,
            'sale_date'      => $this->sale_date,
            'tax_id'         => $this->tax_id,
            'shipping_cost'  => $this->shipping_cost,
            'discount_amount' => $this->discount_amount,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'paid_amount'    => $this->paid_amount,
            'product_items'  => $this->product_items,
        ], [
            'customer_id'    => ['required', Rule::exists($connection . '.customers', 'id')],
            'store_id'       => ['required', Rule::exists($connection . '.stores', 'id')],
            'sale_date'      => ['required', 'date'],
            'tax_id'         => ['nullable', Rule::exists($connection . '.taxes', 'id')],
            'shipping_cost'  => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status'         => ['required'],
            'payment_status' => ['required', Rule::in(['Pending', 'Partial', 'Paid'])],
            'paid_amount'    => ['required', 'numeric', 'min:0'],
            'product_items'  => ['required', 'array', 'min:1'],
        ]);

        $validator->validate();

        // Calculate total price (sum of all product prices without shipping/discount)
        $total_price = collect($this->product_items)->sum(function($item) {
            $qty = (float)$item['quantity'];
            $price = (float)$item['per_unit_price'];
            return $qty * $price;
        });

        DB::beginTransaction();
        try {
            if ($this->mode === 'edit' && $this->saleId) {
                $sale = Sale::findOrFail($this->saleId);
                $task = $sale->tasks()->latest()->first();
                if ($task) {
                    $salePaymentJournalCount = $task->journals()
                        ->where('transaction_type', 'SALE-PAYMENT')
                        ->count();
                    if ($salePaymentJournalCount > 1) {
                        // Only delete and re-insert COGS journals
                        foreach ($task->journals()->where('transaction_type', 'SALE-COGS')->get() as $journal) {
                            if ($journal) {
                                $result = DE::delete($journal);
                                if ($result['status'] && $result['status'] != true) {
                                    DB::rollBack();
                                    return redirect()->back()->with('error', 'Something went wrong.');
                                }
                            }
                        }
                    } else {
                        // Delete all journals (COGS and SALE-PAYMENT)
                        foreach ($task->journals as $journal) {
                            if ($journal) {
                                $result = DE::delete($journal);
                                if ($result['status'] && $result['status'] != true) {
                                    DB::rollBack();
                                    return redirect()->back()->with('error', 'Something went wrong.');
                                }
                            }
                        }
                    }
                }
                $sale->update([
                    'customer_id' => $this->customer_id,
                    'store_id' => $this->store_id,
                    'sale_date' => $this->sale_date,
                    'total_amount' => $this->total_amount,
                    'total_price' => $total_price, // Add this line
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
                $sale->items()->delete();
            } else {
                $sale = Sale::create([
                    'u_id' => $this->generateUniqueUid(),
                    'customer_id' => $this->customer_id,
                    'store_id' => $this->store_id,
                    'sale_date' => $this->sale_date,
                    'total_amount' => $this->total_amount,
                    'total_price' => $total_price, // Add this line
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

                //create DeTask
                $task = DeTask::create([
                    'company_id'    => null, // Set your company_id
                    'name'          => 'Sales',
                    'taskable_id'   => $sale->id, // Set related model ID if needed
                    'taskable_type' => Sale::class, // Set related model class if needed
                    'note'          => $sale->note,
                ]);
            }

            foreach ($this->product_items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'per_unit_price' => $item['per_unit_price'],
                    'total' => $item['total'],
                ]);
            }

            if ($task) {
                $salePaymentJournalCount = $task->journals()
                    ->where('transaction_type', 'SALE-PAYMENT')
                    ->count();
                if ($this->handleCOGSAccounting($sale, $task->id) === false) {
                    return;
                }
                if($salePaymentJournalCount < 1) {
                    // that means this is the first payment accounting not during edit when we have more payments
                    if ($this->payment_status === 'Paid') {
                        if ($this->handlePaymentAccounting($sale, $task->id) === false) {
                            return;
                        }
                    }
                    elseif ($this->payment_status === 'Partial') {
                        if ($this->handlePaymentAccounting($sale, $task->id) === false) {
                            return;
                        }
                        if ($this->handleDueAccounting($sale, $task->id) === false) {
                            return;
                        }
                    }
                    else{
                        if ($this->handleDueAccounting($sale, $task->id) === false) {
                            return;
                        }
                    }
                }
                
            }

            DB::commit();
            session()->flash('success', $this->mode === 'edit' ? 'Sale updated successfully!' : 'Sale created successfully!');
            return redirect()->route('sales.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save sale: ' . $e->getMessage());
            return;
        }
    }

    public function selectProduct($productId)
    {
        $product = $this->products->find($productId);
        if (!$product) return;

        // Get sale price and units from product_store for the selected store
        $salePrice = 0;
        $unitId = $this->units->first()->id ?? null;
        $saleUnitId = $product->sale_unit_id ?? $unitId;
        $baseUnitId = $unitId;

        if ($this->store_id && $product) {
            $productStore = $product->productStores()
                ->where('store_id', $this->store_id)
                ->first();
            if ($productStore) {
                $salePrice = $productStore->sales_price ?? 0;
                $saleUnitId = $productStore->sale_unit_id ?? $saleUnitId;
                $baseUnitId = $productStore->base_unit_id ?? $baseUnitId;
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
            'unit_id' => $saleUnitId,
            'base_unit_id' => $baseUnitId, // Store for filtering units in the view
            'quantity' => 1,
            'per_unit_price' => $salePrice,
            'total' => $salePrice,
        ];

        $this->product_search = '';
        $this->product_suggestions = [];
        $this->calculateTotals();
    }

    public function changeProductQty($value, $index)
    {
        $qty = round(max(0, (float)$value), 2); // Allow 2 decimal points, minimum 0
        $this->product_items[$index]['quantity'] = $qty;
        $this->product_items[$index]['total'] = round($qty * $this->product_items[$index]['per_unit_price'], 2);
        $this->calculateTotals();
    }

    public function changeProductPrice($value, $index)
    {
        $price = round(max(0, (float)$value), 2); // Allow 2 decimal points, minimum 0
        $this->product_items[$index]['per_unit_price'] = $price;
        $this->product_items[$index]['total'] = round($this->product_items[$index]['quantity'] * $price, 2);
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
    public function changePaymentStatus($value)
    {
        $this->payment_status = $value;
        if ($value === 'Paid') {
            $this->paid_amount = $this->total_amount;
            $this->due_amount = 0;
            $store = Store::find($this->store_id);
            if ($store) {
                $this->payment_accounts = $store->accounts()
                    ->where(function($q) {
                        $q->where('account_type_id', 2)
                          ->orWhere('title', 'Cash');
                    })
                    ->whereNotNull('parent_id')
                    ->where('root_type', 1)
                    ->get();
            } else {
                $this->payment_accounts = [];
            }
        } elseif ($value === 'Partial') {
            $this->paid_amount = min($this->paid_amount, $this->total_amount);
            $store = Store::find($this->store_id);
            if ($store) {
                $this->payment_accounts = $store->accounts()
                    ->where(function ($q) {
                        $q->where('account_type_id', 2)
                            ->orWhere('title', 'Cash');
                    })
                    ->whereNotNull('parent_id')
                    ->where('root_type', 1)
                    ->get();
            } else {
                $this->payment_accounts = [];
            }
        } else {
            $this->paid_amount = 0;
            $this->disable_payment_fields = true;
            $this->payment_accounts = [];
        }
    }

    public function handleCOGSAccounting($sale,$taskId)
    {
        $store = $sale->store;
        $inventoryAccount = $store->accounts()
            ->where('title', 'Inventory')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $cogsAccount = $store->accounts()
            ->where('title', 'Cost of Goods Sold')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        if (!$inventoryAccount || !$cogsAccount) {
            DB::rollBack();
            session()->flash('error', 'Inventory or Cost of Goods Sold account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $cogsAccount;
        $destinationAccount = $inventoryAccount;

        $totalCOGS = collect($sale->items)->sum(function ($item) use ($sale) {
            $product = $item->product;
            $purchase_cost = 0;
            if ($product && $sale->store_id) {
                $productStore = $product->productStores()
                    ->where('store_id', $sale->store_id)
                    ->first();
                $purchase_cost = $productStore ? ($productStore->purchase_cost ?? 0) : 0;
            }
            return $item->quantity * $purchase_cost;
        });

        $requestedData = array();
        $requestedData['date'] = $sale->sale_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $sale->note??null;
        $requestedData['amount'] = $totalCOGS;
        $requestedData['journalable_type'] = Sale::class;
        $requestedData['journalable_id'] = $sale->id;

        $deType = 'UPDOWN';
        $eventName = 'SALE-COGS';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }

    public function handlePaymentAccounting($sale, $taskId)
    {
        $store = $sale->store;
        $salesRevenueAccount = $store->accounts()
            ->where('title', 'Sales Revenue')
            ->where('root_type', 4)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $assetAccount = $store->accounts()
            ->where('id', $this->payment_account_id)
            ->first();

        if (!$salesRevenueAccount || !$assetAccount) {
            DB::rollBack();
            session()->flash('error', 'Sales Revenue or Assets account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $salesRevenueAccount;
        $destinationAccount = $assetAccount;

        $requestedData = array();
        $requestedData['date'] = $sale->sale_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $sale->note ?? null;
        $requestedData['amount'] = $sale->paid_amount;
        $requestedData['journalable_type'] = Sale::class;
        $requestedData['journalable_id'] = $sale->id;

        $deType = 'UPUP';
        $eventName = 'SALE-PAYMENT';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }

    public function handleDueAccounting($sale, $taskId)
    {
        $store = $sale->store;
        $customer = $sale->customer;
        $salesRevenueAccount = $store->accounts()
            ->where('title', 'Sales Revenue')
            ->where('root_type', 4)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();

        $customerReceivableAccount = $customer->accounts()
            ->where('title','like','%Receivable%')
            ->where('root_type', 1)
            ->where('account_type_id', 1)
            ->whereNotNull('parent_id')
            ->first();


        if (!$salesRevenueAccount || !$customerReceivableAccount) {
            DB::rollBack();
            session()->flash('error', 'Sales Revenue or Customer Receivables account not found for this store.');
            return false; // Stop further execution
        }

        $sourceAccount = $salesRevenueAccount;
        $destinationAccount = $customerReceivableAccount;

        $requestedData = array();
        $requestedData['date'] = $sale->sale_date;
        $requestedData['source_transactionable_id'] = $sourceAccount->accountable_id;
        $requestedData['destination_transactionable_id'] = $destinationAccount->accountable_id;
        $requestedData['source_transactionable_type'] = $sourceAccount->accountable ? get_class($sourceAccount->accountable) : null;
        $requestedData['destination_transactionable_type'] = $destinationAccount->accountable ? get_class($destinationAccount->accountable) : null;
        $requestedData['note'] = $sale->note ?? null;
        $requestedData['amount'] = $sale->due_amount;
        $requestedData['journalable_type'] = Sale::class;
        $requestedData['journalable_id'] = $sale->id;

        $deType = 'UPUP';
        $eventName = 'SALE-DUE';
        DE::store($sourceAccount, $destinationAccount, $requestedData, $deType, $taskId, $eventName);

        return true;
    }

    public function render()
    {
        return view('livewire.admin.sale-form');
    }
}
