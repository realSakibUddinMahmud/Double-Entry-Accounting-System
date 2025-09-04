<div>
    <form wire:submit.prevent="save">
        @session('error')
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endsession
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                <select wire:model="customer_id" id="customer_id" class="form-control">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
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
                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                <input type="date" wire:model="sale_date" id="sale_date" class="form-control">
                @error('sale_date') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="tax_id" class="form-label">Tax</label>
                <select wire:model="tax_id" id="tax_id" class="form-control">
                    <option value="">Select Tax</option>
                    @foreach($taxes as $tax)
                        <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                    @endforeach
                </select>
                @error('tax_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="shipping_cost" class="form-label">Shipping Cost</label>
                <input type="number" step="0.01" wire:input="changeShippingCost($event.target.value)" wire:model="shipping_cost" id="shipping_cost" class="form-control">
                @error('shipping_cost') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
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
                <div style="position: relative;">
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
                        <ul class="list-group position-absolute w-100" style="z-index: 10; left: 0; right: 0; width: 100%;">
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
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product_items as $index => $item)
                        <tr>
                            <td>{{ $item['product_name'] ?? '' }}</td>
                            <td>
                                <select wire:model="product_items.{{ $index }}.unit_id" class="form-control form-control-sm">
                                    @foreach($units as $unit)
                                        @if($unit->id == ($item['base_unit_id'] ?? null) || $unit->parent_id == ($item['base_unit_id'] ?? null))
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number"
                                       min="0"
                                       step="0.01"
                                       wire:input="changeProductQty($event.target.value, {{ $index }})"
                                       wire:model="product_items.{{ $index }}.quantity"
                                       class="form-control form-control-sm">
                            </td>
                            <td>
                                <input type="number"
                                       min="0"
                                       step="0.01"
                                       wire:input="changeProductPrice($event.target.value, {{ $index }})"
                                       wire:model="product_items.{{ $index }}.per_unit_price"
                                       class="form-control form-control-sm">
                            </td>
                            <td>{{ $item['quantity'] * $item['per_unit_price'] }}</td>
                            <td class="text-center">
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn p-0 text-danger" title="Remove">
                                    <i class="ti ti-x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if(count($product_items) == 0)
                        <tr>
                            <td colspan="6" class="text-center">No products added.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Total Amount</label>
                <input type="text" class="form-control" value="{{ $total_amount }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Status</label>
                <select wire:model="payment_status" wire:change="changePaymentStatus($event.target.value)" class="form-control" @if($disable_payment_fields) disabled @endif>
                    <option value="Pending">Pending</option>
                    <option value="Partial">Partial</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Paid Amount</label>
                <input
                    type="number"
                    step="0.01"
                    wire:model="paid_amount"
                    wire:input="changePaidAmount($event.target.value)"
                    class="form-control"
                    @if($disable_payment_fields || $payment_status == 'Paid' || $payment_status == 'Pending') readonly @endif
                    value="{{ $payment_status == 'Paid' ? $total_amount : $paid_amount }}"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Amount</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $payment_status == 'Paid' ? 0 : $due_amount }}"
                    readonly
                >
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Payment Account</label>
                <select wire:model="payment_account_id" class="form-control" @if($disable_payment_fields || $payment_status == 'Pending') disabled @endif>
                    <option value="">Select Account</option>
                    @foreach($payment_accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($disable_payment_fields)
            <div class="alert alert-warning mt-2">
                You cannot change payment status, paid amount, due amount, or payment account because multiple payments exist for this sale.
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Note</label>
                <textarea wire:model="note" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                @error('note') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-success">
            {{ $mode === 'edit' ? 'Update Sale' : 'Save Sale' }}
        </button>
    </form>
</div>

