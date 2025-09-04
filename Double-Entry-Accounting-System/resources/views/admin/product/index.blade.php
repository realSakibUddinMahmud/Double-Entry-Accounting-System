@extends('layouts.app-admin')
@section('title', 'Product')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>All Products</h4>
            <h6>Manage your Products</h6>
        </div>
    </div>
    @can('product-create')
    <div class="page-btn">
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>Add Product
        </a>
    </div>
    @endcan
</div>
<!-- /product list -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Search & Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search Product Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by product name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Store</label>
                <select name="store_id" class="form-select">
                    <option value="">All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Search
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Product List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $products->total() }} products</span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        {{-- <th>SKU</th> --}}
                        <th>Store</th>
                        <th>Unit</th>
                        <th>Cost</th>
                        <th>Cogs (Avg)</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $storeData = $product->productStores->first();
                            // Fetch stock quantity from the view for this product and store
                            $stockQty = null;
                            if ($storeData) {
                                $stockQty = optional(
                                    $product->productStoreStockViews
                                        ->where('store_id', $storeData->store_id)
                                        ->first()
                                )->current_stock_qty;
                            }
                            $cogsAvg = optional(
                                $product->cogsAvgView($storeData?->store_id)->first()
                            )->cogs_avg ?? 0;
                            $additionalFields = [];
                            foreach ($customFields as $field) {
                                $value = optional(
                                    $product->customFieldValues->where('custom_field_id', $field->id)->first()
                                )->value;
                                $additionalFields[$field->label] = $value ?? '-';
                            }
                        @endphp
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $product->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="text-gray-9">
                                {{ $product->name }}
                            </td>
                            <td>
                                {{ optional($product->category)->name ?? '-' }}
                            </td>
                            <td>
                                {{ optional($product->brand)->name ?? '-' }}
                            </td>
                            {{-- <td>
                                {{ $product->sku ?? '-' }}
                            </td> --}}
                            <td>
                                {{ optional($storeData?->store)->name ?? '-' }}
                            </td>
                            <td>
                                {{ optional($storeData?->base_unit)->name ?? '-' }}
                            </td>
                            <td>
                                {{ $storeData->purchase_cost ?? '-' }}
                            </td>
                            <td>
                                {{ is_numeric($cogsAvg) ? number_format($cogsAvg, 2) : '-' }}
                            </td>
                            <td>
                                {{ $storeData->sales_price ?? '-' }}
                            </td>
                            <td>
                                {{ is_numeric($stockQty) ? number_format($stockQty, 2) : '-' }}
                            </td>
                            <td>
                                @if($product->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('product-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#viewProductModal"
                                       data-id="{{ $product->id }}"
                                       data-name="{{ $product->name }}"
                                       data-category="{{ optional($product->category)->name }}"
                                       data-brand="{{ optional($product->brand)->name }}"
                                       data-sku="{{ $product->sku }}"
                                       data-barcode="{{ $product->barcode }}"
                                       data-status="{{ $product->status }}"
                                       data-description="{{ $product->description }}"
                                       data-store="{{ optional($storeData?->store)->name }}"
                                       data-base_unit="{{ optional($storeData?->base_unit)->name }}"
                                       data-purchase_cost="{{ $storeData->purchase_cost ?? '-' }}"
                                       data-sales_price="{{ $storeData->sales_price ?? '-' }}"
                                       data-images="{{ json_encode($product->images->map(fn($img) => asset('storage/'.$img->path))) }}"
                                       data-additional_fields='@json($additionalFields)'>
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    @can('product-edit')
                                    <a class="me-2 p-2"
                                       href="{{ route('products.edit', $product->id) }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('product-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteProductModal"
                                       data-id="{{ $product->id }}">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Note:Nothing will be add here --}}
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <x-admin.pagination :paginator="$products" info-text="products" />
    </div>
</div>
<!-- /product list -->

@can('product-delete')
@include('admin.product.delete-modal')
@endcan
@can('product-view')
@include('admin.product.view-modal')
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('editProductModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var category_id = button.getAttribute('data-category_id');
            var brand_id = button.getAttribute('data-brand_id');
            var sku = button.getAttribute('data-sku');
            var barcode = button.getAttribute('data-barcode');
            var status = button.getAttribute('data-status');
            var description = button.getAttribute('data-description');

            document.getElementById('editProductForm').action = "{{ url('products') }}/" + id;
            document.getElementById('edit_product_name').value = name;
            document.getElementById('edit_product_category_id').value = category_id;
            document.getElementById('edit_product_brand_id').value = brand_id;
            document.getElementById('edit_product_sku').value = sku;
            document.getElementById('edit_product_barcode').value = barcode;
            document.getElementById('edit_product_status').value = status;
            document.getElementById('edit_product_description').value = description;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('deleteProductModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteProductForm').action = "{{ url('products') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('viewProductModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_product_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_product_category').textContent = button.getAttribute('data-category');
            document.getElementById('view_product_brand').textContent = button.getAttribute('data-brand');
            document.getElementById('view_product_sku').textContent = button.getAttribute('data-sku');
            document.getElementById('view_product_barcode').textContent = button.getAttribute('data-barcode');
            document.getElementById('view_product_status').textContent = button.getAttribute('data-status') == 1 ? 'Active' : 'Inactive';
            document.getElementById('view_product_description').textContent = button.getAttribute('data-description');

            // Additional Fields (expects a JSON object in data-additional_fields)
            var additionalFieldsData = button.getAttribute('data-additional_fields');
            var additionalFieldsContainer = document.getElementById('view_product_additional_fields');
            if (additionalFieldsContainer) {
                additionalFieldsContainer.innerHTML = '';
                if (additionalFieldsData) {
                    try {
                        var fields = JSON.parse(additionalFieldsData);
                        if (fields && typeof fields === 'object') {
                            Object.keys(fields).forEach(function(label) {
                                var dt = document.createElement('dt');
                                dt.className = 'col-sm-4';
                                dt.textContent = label + ':';
                                var dd = document.createElement('dd');
                                dd.className = 'col-sm-8';
                                dd.textContent = fields[label];
                                additionalFieldsContainer.appendChild(dt);
                                additionalFieldsContainer.appendChild(dd);
                            });
                        }
                    } catch (e) {
                        // Ignore if invalid
                    }
                }
            }
        });
    }
});
</script>





