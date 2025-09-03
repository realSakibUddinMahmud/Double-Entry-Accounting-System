<div class="sidebar-inner slimscroll">
    <div id="sidebar-menu" class="sidebar-menu">
        <ul>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Home</h6>
                <ul>
                    <li>
                        <a href="{{ route('home') }}">
                            <i class="ti ti-layout-grid fs-16 me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-user-edit fs-16 me-2"></i><span>Super Admin</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('companies.index') }}">Companies</a></li>
                            <li><a href="subscription.html">Subscriptions</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Inventory</h6>
                <ul>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i data-feather="box"></i><span>Products</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('products.index') }}">Products</a></li>
                            <li><a href="{{ route('products.create') }}">Create Product</a></li>
                        </ul>
                    </li>
                    {{-- <li><a href="{{ route('products.index') }}"><i data-feather="box"></i><span>Products</span></a></li> --}}
                    <li><a href="add-product.html"><i class="ti ti-table-plus fs-16 me-2"></i><span>Create Product</span></a></li>
                    <li><a href="expired-products.html"><i class="ti ti-progress-alert fs-16 me-2"></i><span>Expired Products</span></a></li>
                    <li><a href="low-stocks.html"><i class="ti ti-trending-up-2 fs-16 me-2"></i><span>Low Stocks</span></a></li>
                    <li><a href="{{ route('categories.index') }}"><i class="ti ti-list-details fs-16 me-2"></i><span>Category</span></a></li>
                    <li><a href="sub-categories.html"><i class="ti ti-carousel-vertical fs-16 me-2"></i><span>Sub Category</span></a></li>
                    <li><a href="{{ route('brands.index') }}"><i class="ti ti-triangles fs-16 me-2"></i><span>Brands</span></a></li>
                    <li><a href="{{ route('units.index') }}"><i class="ti ti-brand-unity fs-16 me-2"></i><span>Units</span></a></li>
                    <li><a href="varriant-attributes.html"><i class="ti ti-checklist fs-16 me-2"></i><span>Variant Attributes</span></a></li>
                    <li><a href="warranty.html"><i class="ti ti-certificate fs-16 me-2"></i><span>Warranties</span></a></li>
                    <li><a href="barcode.html"><i class="ti ti-barcode fs-16 me-2"></i><span>Print Barcode</span></a></li>
                    <li><a href="qrcode.html"><i class="ti ti-qrcode fs-16 me-2"></i><span>Print QR Code</span></a></li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Stock</h6>
                <ul>
                    <li><a href="manage-stocks.html"><i class="ti ti-stack-3 fs-16 me-2"></i><span>Manage Stock</span></a></li>
                    <li><a href="{{ route('stock-adjustments.index') }}"><i class="ti ti-stairs-up fs-16 me-2"></i><span>Stock Adjustment</span></a></li>
                    <li><a href="stock-transfer.html"><i class="ti ti-stack-pop fs-16 me-2"></i><span>Stock Transfer</span></a></li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Sales</h6>
                <ul>
                    <li><a href="{{ route('sales.index') }}"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Sales</span></a></li>
                    <li><a href="invoice.html"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Invoices</span></a></li>
                    <li><a href="sales-returns.html"><i class="ti ti-receipt-refund fs-16 me-2"></i><span>Sales Return</span></a></li>
                    <li><a href="quotation-list.html"><i class="ti ti-files fs-16 me-2"></i><span>Quotation</span></a></li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-device-laptop fs-16 me-2"></i><span>POS</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="pos.html">POS 1</a></li>
                            <li><a href="pos-2.html">POS 2</a></li>
                            <li><a href="pos-3.html">POS 3</a></li>
                            <li><a href="pos-4.html">POS 4</a></li>
                            <li><a href="pos-5.html">POS 5</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Promo</h6>
                <ul>
                    <li><a href="coupons.html"><i class="ti ti-ticket fs-16 me-2"></i><span>Coupons</span></a></li>
                    <li><a href="gift-cards.html"><i class="ti ti-cards fs-16 me-2"></i><span>Gift Cards</span></a></li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-file-percent fs-16 me-2"></i><span>Discount</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="discount-plan.html">Discount Plan</a></li>
                            <li><a href="discount.html">Discount</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Purchases</h6>
                <ul>
                    <li><a href="{{ route('purchases.index') }}"><i class="ti ti-shopping-bag fs-16 me-2"></i><span>Purchases</span></a></li>
                    <li><a href="purchase-order-report.html"><i class="ti ti-file-unknown fs-16 me-2"></i><span>Purchase Order</span></a></li>
                    <li><a href="purchase-returns.html"><i class="ti ti-file-upload fs-16 me-2"></i><span>Purchase Return</span></a></li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Finance & Accounts</h6>
                <ul>
                <li><a href="{{ route('de-account.index') }}"><i class="ti ti-building-bank fs-16 me-2"></i><span>Accounts</span></a></li>
                <li><a href="{{ route('de-fund-transfer.index') }}"><i class="ti ti-arrows-exchange fs-16 me-2"></i><span>Fund Transfer</span></a></li>
                <li><a href="{{ route('de-payment.index') }}"><i class="ti ti-credit-card fs-16 me-2"></i><span>Payments</span></a></li>
                <li><a href="{{ route('de-income-revenue.index') }}"><i class="ti ti-cash fs-16 me-2"></i><span>Income Revenue</span></a></li>
                <li><a href="{{ route('de-loan-investment.index') }}"><i class="ti ti-pig-money fs-16 me-2"></i><span>Loan/Investment</span></a></li>
                <li><a href="{{ route('de-loan-invreturn.index') }}"><i class="ti ti-rotate fs-16 me-2"></i><span>Loan/Investment Return</span></a></li>
                <li><a href="{{ route('de-security-deposit.index') }}"><i class="ti ti-lock fs-16 me-2"></i><span>Security Deposit</span></a></li>
                <li><a href="{{ route('de-expense.index') }}"><i class="ti ti-file-stack fs-16 me-2"></i><span>Expenses</span></a></li>
                <li><a href="{{ route('de-journal.index') }}"><i class="ti ti-notebook fs-16 me-2"></i><span>Journal</span></a></li>
                <li><a href="{{ route('de-ledger.index') }}"><i class="ti ti-book fs-16 me-2"></i><span>Ledger</span></a></li>
                <li><a href="{{ route('taxes.index') }}"><i class="ti ti-file-infinity fs-16 me-2"></i><span>Tax</span></a></li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Peoples</h6>
                <ul>
                    <li><a href="{{ route('customers.index') }}"><i class="ti ti-users-group fs-16 me-2"></i><span>Customers</span></a></li>
                    <li><a href="billers.html"><i class="ti ti-user-up fs-16 me-2"></i><span>Billers</span></a></li>
                    <li><a href="{{ route('suppliers.index') }}"><i class="ti ti-user-dollar fs-16 me-2"></i><span>Suppliers</span></a></li>
                    <li><a href="{{ route('stores.index') }}"><i class="ti ti-home-bolt fs-16 me-2"></i><span>Stores</span></a></li>
                    <li><a href="warehouse.html"><i class="ti ti-archive fs-16 me-2"></i><span>Warehouses</span></a>
                    </li>
                </ul>
            </li>

            <li class="submenu-open">
                <h6 class="submenu-hdr">Reports</h6>
                <ul>
                    <li><a href="{{ route('report.sales') }}"><i class="ti ti-chart-line fs-16 me-2"></i><span>Sales Report</span></a></li>
                    <li><a href="{{ route('report.purchase') }}"><i class="ti ti-chart-pie-2 fs-16 me-2"></i><span>Purchase Report</span></a></li>
                    <li><a href="{{ route('report.stock') }}"><i class="ti ti-chart-donut-2 fs-16 me-2"></i><span>Inventory Report</span></a></li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-triangle-inverted fs-16 me-2"></i><span>Inventory Report</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="inventory-report.html">Inventory Report</a></li>
                            <li><a href="stock-history.html">Stock History</a></li>
                            <li><a href="sold-stock.html">Sold Stock</a></li>
                        </ul>
                    </li>
                    <li><a href="invoice-report.html"><i class="ti ti-businessplan fs-16 me-2"></i><span>Invoice Report</span></a></li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-user-star fs-16 me-2"></i><span>Supplier Report</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="supplier-report.html">Supplier Report</a></li>
                            <li><a href="supplier-due-report.html">Supplier Due Report</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-report fs-16 me-2"></i><span>Customer Report</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="customer-report.html">Customer Report</a></li>
                            <li><a href="customer-due-report.html">Customer Due Report</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-report-analytics fs-16 me-2"></i><span>Product Report</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="product-report.html">Product Report</a></li>
                            <li><a href="product-expiry-report.html">Product Expiry Report</a></li>
                            <li><a href="product-quantity-alert.html">Product Quantity Alert</a></li>
                        </ul>
                    </li>
                    <li><a href="expense-report.html"><i class="ti ti-file-vector fs-16 me-2"></i><span>Expense Report</span></a></li>
                    {{-- <li><a href="income-report.html"><i class="ti ti-chart-ppf fs-16 me-2"></i><span>Income Report</span></a></li> --}}
                    <li><a href="tax-reports.html"><i class="ti ti-chart-dots-2 fs-16 me-2"></i><span>Tax Report</span></a></li>
                    {{-- <li><a href="profit-and-loss.html"><i class="ti ti-chart-donut fs-16 me-2"></i><span>Profit & Loss</span></a></li> --}}
                    <li><a href="annual-report.html"><i class="ti ti-report-search fs-16 me-2"></i><span>Annual Report</span></a></li>

                    <li><a href="{{ route('report.income-statement') }}"><i class="ti ti-report fs-16 me-2"></i><span>Income Statement Report</span></a></li>
                    <li><a href="{{ route('report.balance-sheet') }}"><i class="ti ti-report fs-16 me-2"></i><span>Balance Sheet Report</span></a></li>
                    <li><a href="{{ route('report.trial-balance') }}"><i class="ti ti-report fs-16 me-2"></i><span>Trial Balance Report</span></a></li>

                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">User Management</h6>
                <ul>
                    <li><a href="admin/users"><i class="ti ti-shield-up fs-16 me-2"></i><span>Users</span></a></li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-jump-rope fs-16 me-2"></i><span>Roles & Permissions</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('roles.index') }}">Roles</a></li>
                            <li><a href="{{ route('permissions.index') }}">Permissions</a></li>
                        </ul>
                    </li>
                    <li><a href="delete-account.html"><i class="ti ti-trash-x fs-16 me-2"></i><span>Delete Account Request</span></a></li>
                </ul>
            </li>
            <li class="submenu-open">
                <h6 class="submenu-hdr">Settings</h6>
                <ul>
                    <li><a href="{{ route('additional-fields.index') }}"><i class="ti ti-shield-up fs-16 me-2"></i><span>Additional Field</span></a></li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-settings fs-16 me-2"></i><span>General Settings</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="general-settings.html">Profile</a></li>
                            <li><a href="security-settings.html">Security</a></li>
                            <li><a href="notification.html">Notifications</a></li>
                            <li><a href="connected-apps.html">Connected Apps</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-world fs-16 me-2"></i><span>Website Settings</span><span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="system-settings.html">System Settings</a></li>
                            <li><a href="company-settings.html">Company Settings </a></li>
                            <li><a href="localization-settings.html">Localization</a></li>
                            <li><a href="prefixes.html">Prefixes</a></li>
                            <li><a href="preference.html">Preference</a></li>
                            <li><a href="appearance.html">Appearance</a></li>
                            <li><a href="social-authentication.html">Social Authentication</a></li>
                            <li><a href="language-settings.html">Language</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-device-mobile fs-16 me-2"></i>
                            <span>App Settings</span><span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li class="submenu submenu-two"><a href="javascript:void(0);">Invoice<span class="menu-arrow inside-submenu"></span></a>
                                <ul>
                                    <li><a href="invoice-settings.html">Invoice Settings</a></li>
                                    <li><a href="invoice-template.html">Invoice Template</a></li>
                                </ul>
                            </li>
                            <li><a href="printer-settings.html">Printer</a></li>
                            <li><a href="pos-settings.html">POS</a></li>
                            <li><a href="custom-fields.html">Custom Fields</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-device-desktop fs-16 me-2"></i>
                            <span>System Settings</span><span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li class="submenu submenu-two"><a href="javascript:void(0);">Email<span class="menu-arrow inside-submenu"></span></a>
                                <ul>
                                    <li><a href="email-settings.html">Email Settings</a></li>
                                    <li><a href="email-template.html">Email Template</a></li>
                                </ul>
                            </li>
                            <li class="submenu submenu-two"><a href="javascript:void(0);">SMS<span class="menu-arrow inside-submenu"></span></a>
                                <ul>
                                    <li><a href="sms-settings.html">SMS Settings</a></li>
                                    <li><a href="sms-template.html">SMS Template</a></li>
                                </ul>
                            </li>
                            <li><a href="otp-settings.html">OTP</a></li>
                            <li><a href="gdpr-settings.html">GDPR Cookies</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-settings-dollar fs-16 me-2"></i>
                            <span>Financial Settings</span><span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="payment-gateway-settings.html">Payment Gateway</a></li>
                            <li><a href="bank-settings-grid.html">Bank Accounts</a></li>
                            <li><a href="tax-rates.html">Tax Rates</a></li>
                            <li><a href="currency-settings.html">Currencies</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="ti ti-settings-2 fs-16 me-2"></i>
                            <span>Other Settings</span><span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="storage-settings.html">Storage</a></li>
                            <li><a href="ban-ip-address.html">Ban IP Address</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="signin.html"><i class="ti ti-logout fs-16 me-2"></i><span>Logout</span> </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>