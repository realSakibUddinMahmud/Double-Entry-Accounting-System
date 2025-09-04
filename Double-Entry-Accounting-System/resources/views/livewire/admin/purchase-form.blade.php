{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/livewire/admin/purchase-form.blade.php --}}

@can('purchase-create')
<div>
    <form wire:submit.prevent="save">
        @session('error')
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endsession
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                <select wire:model="supplier_id" id="supplier_id" class="form-control">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="store_id" class="form-label">Store <span class="text-danger">*</span></label>
                <select
                    wire:model="store_id"
                    wire:change="changeStore($event.target.value)"
                    class="form-control"
                    id="store_id"
                    x-data
                    @if(count($stores) === 1)
                        x-init="$wire.set('store_id', '{{ $stores[0]->id }}')"
                    @endif
                >
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
                @error('store_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                <input type="date" wire:model="purchase_date" id="purchase_date" class="form-control">
                @error('purchase_date') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="row">
            {{-- <div class="col-md-3 mb-3">
                <label for="shipping_cost" class="form-label">Shipping Cost</label>
                <input type="number" step="0.01" wire:input="changeShippingCost($event.target.value)" wire:model="shipping_cost" id="shipping_cost" class="form-control">
                @error('shipping_cost') <span class="text-danger">{{ $message }}</span> @enderror
            </div> --}}
            <div class="col-md-3 mb-3">
                <label for="discount_amount" class="form-label">Discount</label>
                <input type="number" step="0.01" wire:input="changeDiscountAmount($event.target.value)" wire:model="discount_amount" id="discount_amount" class="form-control">
                @error('discount_amount') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <hr>
        <h5>Add Products</h5>
        <div class="row align-items-end mb-2">
            <div class="col-md-6 position-relative">
                <label for="product_search" class="form-label">Search Product</label>
                <div class="position-relative">
                    <input type="text"
                           wire:input="inputProductSearch($event.target.value)"
                           wire:model="product_search"
                           id="product_search"
                           class="form-control"
                           placeholder="Type product name, SKU, or barcode"
                           @if(!$store_id) disabled @endif>
                    @if(!$store_id)
                        <small class="text-danger">Please select a store first.</small>
                    @elseif($product_search && count($product_suggestions) > 0)
                        <ul class="list-group position-absolute w-100" style="z-index: 10; left:0; top:100%; min-width:100%;">
                            @foreach($product_suggestions as $suggested)
                                <li class="list-group-item list-group-item-action"
                                    wire:click="selectProduct({{ $suggested->id }})"
                                    style="cursor:pointer;">
                                    {{ $suggested->name }}
                                    @if($suggested->sku) | {{ $suggested->sku }} @endif
                                    @if($suggested->barcode) | {{ $suggested->barcode }} @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>COGS</th>
                        <th>Tax</th>
                        <th>Tax Amount</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product_items as $index => $item)
                        <tr>
                            <td>{{ $item['product_name'] ?? '' }}</td>
                            <td>
                                <select wire:model="product_items.{{ $index }}.unit_id" class="form-control">
                                    @foreach($units as $unit)
                                        @if($unit->id == $item['base_unit_id'] || $unit->parent_id == $item['base_unit_id'])
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number"
                                       step="0.01"
                                       min="1"
                                       wire:input="changeProductQty($event.target.value, {{ $index }})"
                                       wire:model="product_items.{{ $index }}.quantity"
                                       class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" min="1" step="0.01" wire:input="changeProductCost($event.target.value, {{ $index }})" wire:model="product_items.{{ $index }}.per_unit_cost" class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number" min="1" step="0.01" wire:model="product_items.{{ $index }}.per_unit_cogs" class="form-control form-control-sm">
                            </td>
                            <td>
                                <select wire:model="product_items.{{ $index }}.tax_id" class="form-control form-control-sm" wire:change="calculateTotals">
                                    <option value="">No Tax</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}" {{ $tax->id == ($item['tax_id'] ?? null) ? 'selected' : '' }}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                {{ number_format($this->calculateItemTaxAmount($item), 2) }} ({{ $item['tax_method'] ?? 'exclusive' }})
                            </td>
                            <td>{{ number_format($this->calculateItemTotal($item), 2) }}</td>
                            <td class="text-center">
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn p-0 text-danger" title="Remove">
                                    <i class="ti ti-x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if(count($product_items) == 0)
                        <tr>
                            <td colspan="8" class="text-center">No products added.</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th></th>
                        <th>
                            {{ number_format(collect($product_items)->sum(fn($item) => $this->calculateItemTaxAmount($item)), 2) }}
                        </th>
                        <th>
                            {{ number_format(collect($product_items)->sum(fn($item) => $this->calculateItemTotal($item)), 2) }}
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Total Amount</label>
                <input type="text" class="form-control" value="{{ $total_amount }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Paid Amount</label>
                <input type="number" step="0.01" wire:input="changePaidAmount" wire:model="paid_amount" class="form-control" readonly>
                @error('paid_amount') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Amount</label>
                <input type="text" class="form-control" value="{{ $due_amount }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Status</label>
                <select wire:model="payment_status" class="form-control" disabled>
                    <option value="Pending">Pending</option>
                    <option value="Partial">Partial</option>
                    <option value="Paid">Paid</option>
                </select>
                @error('payment_status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Note</label>
                <textarea wire:model="note" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                @error('note') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-success">
            {{ $mode === 'edit' ? 'Update Purchase' : 'Save Purchase' }}
        </button>
    </form>
</div>
@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to create purchases.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="ti ti-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endcan

