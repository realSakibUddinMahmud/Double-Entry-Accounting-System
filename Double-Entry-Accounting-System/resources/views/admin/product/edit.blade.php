@extends('layouts.app-admin')
@section('title', 'Edit Product')

@can('product-edit')
@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Product</h4>
        <h6>Update product details</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('products.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Product List
        </a>
    </div>
</div>

<form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @php
                        function renderCategoryTreeEdit($categories, $selected, $prefix = '') {
                            foreach ($categories as $category) {
                                $isSelected = $selected == $category->id ? 'selected' : '';
                                echo '<option value="' . $category->id . '" ' . $isSelected . '>' . $prefix . $category->name . '</option>';
                                if ($category->children && $category->children->count()) {
                                    renderCategoryTreeEdit($category->children, $selected, $prefix . '— ');
                                }
                            }
                        }
                        renderCategoryTreeEdit($categories, old('category_id', $product->category_id));
                    @endphp
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-control">
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Barcode</label>
                <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" required>
                    <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- Store-specific fields --}}
            <div class="col-md-4">
                <label class="form-label">Store <span class="text-danger">*</span></label>
                <select name="store_id" class="form-control" required>
                    <option value="">-- Select Store --</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id', $productStore->store_id ?? '') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Base Unit <span class="text-danger">*</span></label>
                <select name="base_unit_id" class="form-control" required>
                    <option value="">-- Select Base Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('base_unit_id', $productStore->base_unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Purchase Unit <span class="text-danger">*</span></label>
                <select name="purchase_unit_id" class="form-control" required>
                    <option value="">-- Select Purchase Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('purchase_unit_id', $productStore->purchase_unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sales Unit <span class="text-danger">*</span></label>
                <select name="sales_unit_id" class="form-control" required>
                    <option value="">-- Select Sales Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('sales_unit_id', $productStore->sales_unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Purchase Cost <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="purchase_cost" class="form-control" value="{{ old('purchase_cost', $productStore->purchase_cost ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">COGS <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="cogs" class="form-control" value="{{ old('cogs', $productStore->cogs ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sales Price <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="sales_price" class="form-control" value="{{ old('sales_price', $productStore->sales_price ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tax</label>
                <select name="tax_id" class="form-control">
                    <option value="">-- Select Tax --</option>
                    @foreach($taxes as $tax)
                        <option value="{{ $tax->id }}" {{ old('tax_id', $productStore->tax_id ?? '') == $tax->id ? 'selected' : '' }}>{{ $tax->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tax Method</label>
                <select name="tax_method" class="form-control">
                    <option value="exclusive" {{ old('tax_method', $productStore->tax_method ?? '') == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                    <option value="inclusive" {{ old('tax_method', $productStore->tax_method ?? '') == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                </select>
            </div>

            {{-- Additional Fields (place just before images) --}}
            @if(isset($customFields) && $customFields->count())
                <div class="col-12">
                    <h6 class="mb-2">Additional Fields</h6>
                    <div class="row">
                        @foreach($customFields as $field)
                            @php
                                $fieldValue = old(
                                    "additional_fields.{$field->name}",
                                    optional($product->customFieldValues->where('custom_field_id', $field->id)->first())->value
                                );
                            @endphp
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ $field->label }}@if($field->type !== 'checkbox') <span class="text-danger">*</span>@endif</label>
                                @if($field->type === 'text')
                                    <input type="text" name="additional_fields[{{ $field->name }}]" class="form-control" value="{{ $fieldValue }}">
                                @elseif($field->type === 'number')
                                    <input type="number" name="additional_fields[{{ $field->name }}]" class="form-control" value="{{ $fieldValue }}">
                                @elseif($field->type === 'date')
                                    <input type="date" name="additional_fields[{{ $field->name }}]" class="form-control" value="{{ $fieldValue }}">
                                @elseif($field->type === 'select')
                                    <select name="additional_fields[{{ $field->name }}]" class="form-select">
                                        <option value="">Select {{ $field->label }}</option>
                                        @foreach(explode(',', $field->options) as $option)
                                            <option value="{{ trim($option) }}" {{ trim($option) == $fieldValue ? 'selected' : '' }}>{{ trim($option) }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field->type === 'checkbox')
                                    <div class="form-check">
                                        <input type="checkbox" name="additional_fields[{{ $field->name }}]" class="form-check-input" value="1" id="field_{{ $field->name }}" {{ $fieldValue ? 'checked' : '' }}>
                                        <label class="form-check-label" for="field_{{ $field->name }}">{{ $field->label }}</label>
                                    </div>
                                @elseif($field->type === 'textarea')
                                    <textarea name="additional_fields[{{ $field->name }}]" class="form-control">{{ $fieldValue }}</textarea>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Images --}}
            <div class="col-md-12">
                <label class="form-label">Product Images</label>
                <input type="file" name="images[]" class="form-control" multiple>
                <div class="mt-2">
                    @if(isset($images) && count($images))
                        @foreach($images as $img)
                            <img src="{{ asset('storage/'.$img->path) }}" alt="Product Image" style="max-width:60px;max-height:60px;" class="me-1 mb-1 border rounded">
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</form>
@endsection
@else
@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Access Denied</h4>
        <h6>You don't have permission to edit products</h6>
    </div>
</div>
@endsection
@endcan