<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeederController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function refresh(Request $request)
    {
        ini_set('max_execution_time', 3000);

        $secret = $request->query('secret');
        if ($secret != 'admin1234') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 401);
        }

        try {
            $this->refreshRolesPermissions();

            return response()->json([
                'success' => true,
                'message' => 'Roles and permissions refreshed successfully!',
                'output' => 'Roles and permissions have been refreshed for current database.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh roles and permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function refreshRolesPermissions()
    {
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::connection('tenant')->table('permissions')->truncate();
        DB::connection('tenant')->table('roles')->truncate();
        DB::connection('tenant')->table('model_has_permissions')->truncate();
        DB::connection('tenant')->table('role_has_permissions')->truncate();
        DB::connection('tenant')->table('model_has_roles')->truncate();

        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define roles
        $roles = [
            'Super Admin',
            'Company Admin',
            'Account Manager',
            'Inventory Manager',
            'Sales Manager',
            'Employee',
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // All permissions 
        $permissions = [
            // Core Modules
            'dashboard-view',
            'home-view',
            'superadmin-access',

            // User Permissions
            'user-view',
            'user-create',
            'user-edit',
            'user-delete',
            'user-role-assign',

            // Role Permissions
            'role-view',
            'role-create',
            'role-edit',
            'role-delete',

            // Permission Permissions
            'permission-view',
            'permission-create',
            'permission-edit',
            'permission-delete',

            // Store Permissions
            'store-view',
            'store-create',
            'store-edit',
            'store-delete',
            'store-select',

            // Brand Permissions
            'brand-view',
            'brand-create',
            'brand-edit',
            'brand-delete',

            // Category Permissions
            'category-view',
            'category-create',
            'category-edit',
            'category-delete',

            // Unit Permissions
            'unit-view',
            'unit-create',
            'unit-edit',
            'unit-delete',

            // Product Permissions
            'product-view',
            'product-create',
            'product-edit',
            'product-delete',
            'product-modal-view',

            // Tax Permissions
            'tax-view',
            'tax-create',
            'tax-edit',
            'tax-modal-view',
            'tax-delete',

            // Additional Field Permissions
            'additional-field-view',
            'additional-field-create',
            'additional-field-edit',
            'additional-field-delete',

            // Supplier Permissions
            'supplier-view',
            'supplier-create',
            'supplier-edit',
            'supplier-delete',
            'supplier-show',

            // Customer Permissions
            'customer-view',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'customer-show',

            // Purchase Permissions
            'purchase-view',
            'purchase-create',
            'purchase-edit',
            'purchase-delete',
            'purchase-show',
            'purchase-payment-view',
            'purchase-payment-create',
            'purchase-payment-delete',
            'purchase-invoice-view',

            // Sale Permissions
            'sale-view',
            'sale-create',
            'sale-edit',
            'sale-delete',
            'sale-show',
            'sale-payment-view',
            'sale-payment-create',
            'sale-payment-delete',
            'sale-invoice-view',

            // Stock Adjustment Permissions
            'stock-adjustment-view',
            'stock-adjustment-create',
            'stock-adjustment-edit',
            'stock-adjustment-delete',
            'stock-adjustment-show',

            // Company Permissions
            'company-view',
            'company-create',
            'company-edit',
            'company-delete',
            'company-user-manage',

            // Report Permissions
            'report-sales-view',
            'report-purchase-view',
            'report-stock-view',
            'report-export',

            // Profile Permissions
            'profile-view',
            'profile-edit',
            'password-update',

            // Password Reset Permissions
            'password-reset-request',

            // Accounting Permissions
            'account-voucher-view',
            'report-balance-sheet-view',
            'report-income-statement-view',
            'report-trail-balance-view',
            'report-equity-statement-view',

            // DE Accounting - Account Management
            'de-account-view',
            'de-account-create',
            'de-account-edit',
            'de-account-delete',
            'de-account-balance-view',

            // DE Accounting - Expense Management
            'de-expense-view',
            'de-expense-create',
            'de-expense-edit',
            'de-expense-delete',

            // DE Accounting - Fund Transfer
            'de-fund-transfer-view',
            'de-fund-transfer-create',
            'de-fund-transfer-delete',

            // DE Accounting - Income/Revenue
            'de-income-revenue-view',
            'de-income-revenue-create',
            'de-income-revenue-edit',
            'de-income-revenue-delete',

            // DE Accounting - Journals & Ledgers
            'de-journal-view',
            'de-ledger-view',

            // DE Accounting - Loan/Investment
            'de-loan-investment-view',
            'de-loan-investment-create',
            'de-loan-investment-edit',
            'de-loan-investment-delete',

            // DE Accounting - Loan Return
            'de-loan-invreturn-view',
            'de-loan-invreturn-create',
            'de-loan-invreturn-edit',
            'de-loan-invreturn-delete',

            // DE Accounting - Payment
            'de-payment-view',
            'de-payment-create',
            'de-payment-edit',
            'de-payment-delete',

            // DE Accounting - Security Deposit
            'de-security-deposit-view',
            'de-security-deposit-create',
            'de-security-deposit-edit',
            'de-security-deposit-delete'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $rolePermissions = [
            'Super Admin' => Permission::all()->pluck('name')->toArray(),

            'Company Admin' => [
                // Core Modules
                'dashboard-view',
                'home-view',

                // User Management
                'user-view',
                'user-create',
                'user-edit',
                'user-delete',
                'user-role-assign',

                // Store Management
                'store-view',
                'store-create',
                'store-edit',
                'store-delete',
                'store-select',

                // Supplier Permissions
                'supplier-view',
                'supplier-create',
                'supplier-edit',
                'supplier-delete',
                'supplier-show',

                // Product and Inventory
                'product-view',
                'product-create',
                'product-edit',
                'product-delete',

                'brand-view',
                'brand-create',
                'brand-edit',
                'brand-delete',

                'category-view',
                'category-create',
                'category-edit',
                'category-delete',

                'unit-view',
                'unit-create',
                'unit-edit',
                'unit-delete',

                // Additional Field Permissions
                'additional-field-view',
                'additional-field-create',
                'additional-field-edit',
                'additional-field-delete',

                // Purchase and Sales
                'purchase-view',
                'purchase-create',
                'purchase-edit',
                'purchase-delete',
                'purchase-show',
                'purchase-payment-view',
                'sale-view',
                'sale-create',
                'sale-edit',
                'sale-delete',
                'sale-show',
                'sale-payment-view',

                // Report Viewing
                'report-sales-view',
                'report-purchase-view',
                'report-stock-view',

                // Company Management
                'company-view',
                'company-user-manage',

                // Customer
                'customer-view',
                'customer-create',
                'customer-edit',
                'customer-delete',
                'customer-show',

                // DE Accounting
                'de-account-view',
                'de-account-create',
                'de-account-edit',
                'de-account-delete',
                'de-account-balance-view',
                'de-expense-view',
                'de-expense-create',
                'de-expense-edit',
                'de-expense-delete',
                'de-fund-transfer-view',
                'de-fund-transfer-create',
                'de-fund-transfer-delete',
                'de-income-revenue-view',
                'de-income-revenue-create',
                'de-income-revenue-edit',
                'de-income-revenue-delete',
                'de-journal-view',
                'de-ledger-view',
                'de-loan-investment-view',
                'de-loan-investment-create',
                'de-loan-investment-edit',
                'de-loan-investment-delete',
                'de-loan-invreturn-view',
                'de-loan-invreturn-create',
                'de-loan-invreturn-edit',
                'de-loan-invreturn-delete',
                'de-payment-view',
                'de-payment-create',
                'de-payment-edit',
                'de-payment-delete',
                'de-security-deposit-view',
                'de-security-deposit-create',
                'de-security-deposit-edit',
                'de-security-deposit-delete',
                
                // Accounting & Finance
                'account-voucher-view',
                'report-balance-sheet-view', 
                'report-income-statement-view', 
                'report-equity-statement-view',

                // Tax Permissions
                'tax-view',
                'tax-create',
                'tax-edit',
                'tax-modal-view',
                'tax-delete',

                // Profile
                'profile-view',
                'profile-edit',
                'password-update',
            ],

            'Account Manager' => [
                // Core Modules
                'dashboard-view',
                'home-view',

                // Accounting & Finance
                'account-voucher-view',
                'report-balance-sheet-view',
                'report-income-statement-view',
                'report-equity-statement-view',

                // DE Accounting
                'de-account-view',
                'de-account-create',
                'de-account-edit',
                'de-account-delete',
                'de-account-balance-view',
                'de-expense-view',
                'de-expense-create',
                'de-expense-edit',
                'de-expense-delete',
                'de-fund-transfer-view',
                'de-fund-transfer-create',
                'de-fund-transfer-delete',
                'de-income-revenue-view',
                'de-income-revenue-create',
                'de-income-revenue-edit',
                'de-income-revenue-delete',
                'de-journal-view',
                'de-ledger-view',
                'de-loan-investment-view',
                'de-loan-investment-create',
                'de-loan-investment-edit',
                'de-loan-investment-delete',
                'de-loan-invreturn-view',
                'de-loan-invreturn-create',
                'de-loan-invreturn-edit',
                'de-loan-invreturn-delete',
                'de-payment-view',
                'de-payment-create',
                'de-payment-edit',
                'de-payment-delete',
                'de-security-deposit-view',
                'de-security-deposit-create',
                'de-security-deposit-edit',
                'de-security-deposit-delete',

                // Tax Permissions
                'tax-view',
                'tax-create',
                'tax-edit',
                'tax-modal-view',
                'tax-delete',

                // Profile
                'profile-view',
                'profile-edit',
                'password-update',
            ],

            'Inventory Manager' => [
                // Core Modules
                'dashboard-view',
                'home-view',

                // Inventory & Stock
                'store-view',
                'store-select',

                'brand-view',
                'brand-create',
                'brand-edit',
                'brand-delete',

                'category-view',
                'category-create',
                'category-edit',
                'category-delete',

                'unit-view',
                'unit-create',
                'unit-edit',
                'unit-delete',

                'product-view',
                'product-create',
                'product-edit',
                'product-delete',

                'stock-adjustment-view',
                'stock-adjustment-create',
                'stock-adjustment-edit',
                'stock-adjustment-delete',
                'stock-adjustment-show',

                // Supplier Permissions
                'supplier-view',
                'supplier-create',
                'supplier-edit',
                'supplier-delete',
                'supplier-show',

                // Profile
                'profile-view',
                'profile-edit',
                'password-update',
            ],

            'Sales Manager' => [
                // Core Modules
                'dashboard-view',
                'home-view',

                // Sales & Customer
                'sale-view',
                'sale-create',
                'sale-edit',
                'sale-delete',
                'sale-show',
                'sale-payment-view',
                'sale-payment-create',
                'sale-payment-delete',

                'customer-view',
                'customer-create',
                'customer-edit',
                'customer-delete',
                'customer-show',

                // Profile
                'profile-view',
                'profile-edit',
                'password-update',
            ],

            'Employee' => [
                // Core Modules & Profile
                'dashboard-view',
                'home-view',
                'profile-view',
                'profile-edit',
                'password-update',
            ],
        ];

        foreach ($rolePermissions as $role => $permissions) {
            $r = Role::where('name', $role)->first();
            $r->syncPermissions($permissions);
        }
    }
}
