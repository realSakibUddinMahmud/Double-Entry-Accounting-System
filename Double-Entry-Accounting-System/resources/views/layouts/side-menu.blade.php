<div class="sidebar-inner slimscroll">
    <div id="sidebar-menu" class="sidebar-menu">
        <ul>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Home</h6>
                <ul>
                    @can('dashboard-view')
                    <li>
                        <a href="{{ route('home') }}">
                            <i class="ti ti-layout-grid fs-16 me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    @endcan
                    @can('superadmin-access')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-user-edit fs-16 me-2"></i><span>Super Admin</span><span class="menu-arrow"></span></a>
                        <ul>
                            @can('company-view')
                            <li><a href="{{ route('companies.index') }}">Companies</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                </ul>
            </li>
            
            @canany(['product-view', 'category-view', 'brand-view', 'unit-view'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">Inventory</h6>
                <ul>
                    @can('product-view')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i data-feather="box"></i><span>Products</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('products.index') }}">Products</a></li>
                            @can('product-create')
                            <li><a href="{{ route('products.create') }}">Create Product</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('category-view')
                    <li><a href="{{ route('categories.index') }}"><i class="ti ti-list-details fs-16 me-2"></i><span>Category</span></a></li>
                    @endcan
                    @can('brand-view')
                    <li><a href="{{ route('brands.index') }}"><i class="ti ti-triangles fs-16 me-2"></i><span>Brands</span></a></li>
                    @endcan
                    @can('unit-view')
                    <li><a href="{{ route('units.index') }}"><i class="ti ti-brand-unity fs-16 me-2"></i><span>Units</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany
            
            @can('stock-adjustment-view')
            <li class="submenu-open">
                <h6 class="submenu-hdr">Stock</h6>
                <ul>
                    <li><a href="{{ route('stock-adjustments.index') }}"><i class="ti ti-stairs-up fs-16 me-2"></i><span>Stock Adjustment</span></a></li>
                </ul>
            </li>
            @endcan
            
            @can('sale-view')
            <li class="submenu-open">
                <h6 class="submenu-hdr">Sales</h6>
                <ul>
                    <li><a href="{{ route('sales.index') }}"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Sales</span></a></li>
                    @can('sale-create')
                    <li><a href="{{ route('sales.create') }}"><i class="ti ti-file-plus fs-16 me-2"></i><span>Create Sale</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            
            @can('purchase-view')
            <li class="submenu-open">
                <h6 class="submenu-hdr">Purchases</h6>
                <ul>
                    <li><a href="{{ route('purchases.index') }}"><i class="ti ti-shopping-bag fs-16 me-2"></i><span>Purchases</span></a></li>
                    @can('purchase-create')
                    <li><a href="{{ route('purchases.create') }}"><i class="ti ti-shopping-cart-plus fs-16 me-2"></i><span>Create Purchase</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            
            @canany(['de-account-view', 'de-fund-transfer-view', 'de-payment-view', 'de-income-revenue-view', 'de-loan-investment-view', 'de-loan-invreturn-view', 'de-security-deposit-view', 'de-expense-view', 'de-journal-view', 'de-ledger-view', 'tax-view'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">Finance & Accounts</h6>
                <ul>
                    @can('de-account-view')
                    <li><a href="{{ route('de-account.index') }}"><i class="ti ti-building-bank fs-16 me-2"></i><span>Accounts</span></a></li>
                    @endcan
                    @can('de-fund-transfer-view')
                    <li><a href="{{ route('de-fund-transfer.index') }}"><i class="ti ti-arrows-exchange fs-16 me-2"></i><span>Fund Transfer</span></a></li>
                    @endcan
                    @can('de-payment-view')
                    <li><a href="{{ route('de-payment.index') }}"><i class="ti ti-credit-card fs-16 me-2"></i><span>Payments</span></a></li>
                    @endcan
                    @can('de-income-revenue-view')
                    <li><a href="{{ route('de-income-revenue.index') }}"><i class="ti ti-cash fs-16 me-2"></i><span>Income Revenue</span></a></li>
                    @endcan
                    @can('de-loan-investment-view')
                    <li><a href="{{ route('de-loan-investment.index') }}"><i class="ti ti-pig-money fs-16 me-2"></i><span>Loan/Investment</span></a></li>
                    @endcan
                    @can('de-loan-invreturn-view')
                    <li><a href="{{ route('de-loan-invreturn.index') }}"><i class="ti ti-rotate fs-16 me-2"></i><span>Loan/Investment Return</span></a></li>
                    @endcan
                    @can('de-security-deposit-view')
                    <li><a href="{{ route('de-security-deposit.index') }}"><i class="ti ti-lock fs-16 me-2"></i><span>Security Deposit</span></a></li>
                    @endcan
                    @can('de-expense-view')
                    <li><a href="{{ route('de-expense.index') }}"><i class="ti ti-file-stack fs-16 me-2"></i><span>Expenses</span></a></li>
                    @endcan
                    @can('de-journal-view')
                    <li><a href="{{ route('de-journal.index') }}"><i class="ti ti-notebook fs-16 me-2"></i><span>Journal</span></a></li>
                    @endcan
                    @can('de-ledger-view')
                    <li><a href="{{ route('de-ledger.index') }}"><i class="ti ti-book fs-16 me-2"></i><span>Ledger</span></a></li>
                    @endcan
                    @can('tax-view')
                    <li><a href="{{ route('taxes.index') }}"><i class="ti ti-file-infinity fs-16 me-2"></i><span>Tax</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany
            
            @canany(['customer-view', 'supplier-view', 'store-view'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">Peoples</h6>
                <ul>
                    @can('customer-view')
                    <li><a href="{{ route('customers.index') }}"><i class="ti ti-users-group fs-16 me-2"></i><span>Customers</span></a></li>
                    @endcan
                    @can('supplier-view')
                    <li><a href="{{ route('suppliers.index') }}"><i class="ti ti-user-dollar fs-16 me-2"></i><span>Suppliers</span></a></li>
                    @endcan
                    @can('store-view')
                    <li><a href="{{ route('stores.index') }}"><i class="ti ti-home-bolt fs-16 me-2"></i><span>Stores</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['report-sales-view', 'report-purchase-view', 'report-stock-view', 'report-balance-sheet-view', 'report-income-statement-view', 'report-trail-balance-view'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">Reports</h6>
                <ul>
                    @can('report-sales-view')
                    <li><a href="{{ route('report.sales') }}"><i class="ti ti-chart-line fs-16 me-2"></i><span>Sales Report</span></a></li>
                    @endcan
                    @can('report-purchase-view')
                    <li><a href="{{ route('report.purchase') }}"><i class="ti ti-chart-pie-2 fs-16 me-2"></i><span>Purchase Report</span></a></li>
                    @endcan
                    @can('report-stock-view')
                    <li><a href="{{ route('report.stock') }}"><i class="ti ti-chart-donut-2 fs-16 me-2"></i><span>Inventory Report</span></a></li>
                    @endcan
                    @can('report-income-statement-view')
                    <li><a href="{{ route('report.income-statement') }}"><i class="ti ti-report fs-16 me-2"></i><span>Income Statement Report</span></a></li>
                    @endcan
                    @can('report-balance-sheet-view')
                    <li><a href="{{ route('report.balance-sheet') }}"><i class="ti ti-report fs-16 me-2"></i><span>Balance Sheet Report</span></a></li>
                    @endcan
                    @can('report-balance-sheet-view')
                    <li><a href="{{ route('report.trial-balance') }}"><i class="ti ti-report fs-16 me-2"></i><span>Trial Balance Report</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany
            
            @canany(['user-view', 'role-view', 'permission-view'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">User Management</h6>
                <ul>
                    @can('user-view')
                    <li><a href="{{ route('admin.users.index') }}"><i class="ti ti-shield-up fs-16 me-2"></i><span>Users</span></a></li>
                    @endcan
                    <li><a href="{{ route('admin.activity-log.index') }}"><i class="ti ti-history fs-16 me-2"></i><span>Activity Log</span></a></li>
                    @canany(['role-view', 'permission-view'])
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-jump-rope fs-16 me-2"></i><span>Roles & Permissions</span><span class="menu-arrow"></span></a>
                        <ul>
                            @can('role-view')
                            <li><a href="{{ route('roles.index') }}">Roles</a></li>
                            @endcan
                            @can('permission-view')
                            <li><a href="{{ route('permissions.index') }}">Permissions</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endcanany
            
            @canany(['additional-field-view', 'company-profile-show'])
            <li class="submenu-open">
                <h6 class="submenu-hdr">Settings</h6>
                <ul>
                    @can('additional-field-view')
                    <li><a href="{{ route('additional-fields.index') }}"><i class="ti ti-shield-up fs-16 me-2"></i><span>Additional Field</span></a></li>
                    @endcan
                    @can('company-profile-show')
                    <li><a href="{{ route('company.profile') }}"><i class="ti ti-building fs-16 me-2"></i><span>Company Profile</span></a></li>
                    @endcan
                    <li><a href="{{ route('admin.settings.index') }}"><i class="ti ti-settings fs-16 me-2"></i><span>Settings</span></a></li>
                </ul>
            </li>
            @endcanany
        </ul>
    </div>
</div>