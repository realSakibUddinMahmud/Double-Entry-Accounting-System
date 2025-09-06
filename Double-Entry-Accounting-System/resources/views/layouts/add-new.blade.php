{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/layouts/add-new.blade.php --}}

<li class="nav-item dropdown link-nav">
    <a href="javascript:void(0);" class="btn btn-primary btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
        <i class="ti ti-circle-plus me-1"></i>Add New
    </a>
    <div class="dropdown-menu dropdown-xl dropdown-menu-center">
        <div class="row g-2">
            @can('category-view')
            <div class="col-md-2">
                <a href="{{ route('categories.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-brand-codepen"></i>
                    </span>
                    <p>Category</p>
                </a>
            </div>
            @endcan
            
            @can('product-create')
            <div class="col-md-2">
                <a href="{{ route('products.create') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-square-plus"></i>
                    </span>
                    <p>Product</p>
                </a>
            </div>
            @endcan
            
            @can('purchase-create')
            <div class="col-md-2">
                <a href="{{ route('purchases.create') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-shopping-bag"></i>
                    </span>
                    <p>Purchase</p>
                </a>
            </div>
            @endcan
            
            @can('sale-create')
            <div class="col-md-2">
                <a href="{{ route('sales.create') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-shopping-cart"></i>
                    </span>
                    <p>Sale</p>
                </a>
            </div>
            @endcan
            
            @can('de-expense-view')
            <div class="col-md-2">
                <a href="{{ route('de-expense.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-file-text"></i>
                    </span>
                    <p>Expense</p>
                </a>
            </div>
            @endcan
            
            @can('customer-view')
            <div class="col-md-2">
                <a href="{{ route('customers.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-users"></i>
                    </span>
                    <p>Customer</p>
                </a>
            </div>
            @endcan
            
            @can('supplier-view')
            <div class="col-md-2">
                <a href="{{ route('suppliers.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-user-check"></i>
                    </span>
                    <p>Supplier</p>
                </a>
            </div>
            @endcan
            
            @can('brand-view')
            <div class="col-md-2">
                <a href="{{ route('brands.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-triangles"></i>
                    </span>
                    <p>Brand</p>
                </a>
            </div>
            @endcan
            
            @can('unit-view')
            <div class="col-md-2">
                <a href="{{ route('units.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-brand-unity"></i>
                    </span>
                    <p>Unit</p>
                </a>
            </div>
            @endcan
            
            @can('store-view')
            <div class="col-md-2">
                <a href="{{ route('stores.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-home-bolt"></i>
                    </span>
                    <p>Store</p>
                </a>
            </div>
            @endcan
            
            @can('stock-adjustment-view')
            <div class="col-md-2">
                <a href="{{ route('stock-adjustments.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-stairs-up"></i>
                    </span>
                    <p>Stock</p>
                </a>
            </div>
            @endcan
            
            @can('tax-view')
            <div class="col-md-2">
                <a href="{{ route('taxes.index') }}" class="link-item">
                    <span class="link-icon">
                        <i class="ti ti-file-infinity"></i>
                    </span>
                    <p>Tax</p>
                </a>
            </div>
            @endcan
        </div>
    </div>
</li>