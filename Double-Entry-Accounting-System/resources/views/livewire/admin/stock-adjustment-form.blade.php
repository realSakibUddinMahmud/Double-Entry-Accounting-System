{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/livewire/admin/stock-adjustment-form.blade.php --}}

@can('stock-adjustment-create')
<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="store_id" class="form-label">Store <span class="text-danger">*</span></label>
                <select
                    wire:model="store_id"
                    wire:change="changeStore($event.target.value)"
                    class="form-control"
                    id="store_id"
                    x-data
                    @if(count($allStores) === 1)
                        x-init="$wire.set('store_id', '{{ $allStores[0]->id }}')"
                    @endif
                >
                    <option value="">Select Store</option>
                    @foreach($allStores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
                @error('store_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" wire:model="date" id="date" class="form-control">
                @error('date') <span class="text-danger">{{ $message }}</span> @enderror
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
                        <ul class="list-group position-absolute w-100" style="z-index: 10; left:0; right:0;">
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
                        <th>Base Unit</th>
                        <th>Action</th>
                        <th>Quantity</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product_items as $index => $item)
                        <tr>
                            <td>{{ $item['product_name'] ?? '' }}</td>
                            <td>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       value="{{ $item['base_unit_name'] ?? ($item['base_unit'] ?? '') }}"
                                       readonly>
                            </td>
                            <td>
                                <select wire:model="product_items.{{ $index }}.action" class="form-control form-control-sm">
                                    <option value="+">Increase</option>
                                    <option value="-">Decrease</option>
                                </select>
                            </td>
                            <td>
                                <input type="number"
                                       min="0.01"
                                       step="0.01"
                                       wire:model="product_items.{{ $index }}.quantity"
                                       class="form-control form-control-sm">
                            </td>
                            <td class="text-center">
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn p-0 text-danger" title="Remove">
                                    <i class="ti ti-x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if(count($product_items) == 0)
                        <tr>
                            <td colspan="5" class="text-center">No products added.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="note" class="form-label">Note</label>
                <input type="text" wire:model="note" id="note" class="form-control">
                @error('note') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-success">
                {{ $stock_adjustment_id ? 'Update Stock Adjustment' : 'Save Stock Adjustment' }}
            </button>
        </div>
    </form>
</div>
@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to create stock adjustments.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan