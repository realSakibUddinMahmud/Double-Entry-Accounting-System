<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Database\Seeders\TestChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class InventoryManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test complete inventory management workflow
     */
    public function test_complete_inventory_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard');

            // Step 1: Add a new product
            $browser->clickLink('Products')
                ->assertPathIs('/admin/products')
                ->click('@add-product-btn')
                ->assertPathIs('/admin/products/create')
                ->type('name', 'Test Product')
                ->type('sku', 'TEST-001')
                ->type('description', 'A test product for browser testing')
                ->type('purchase_price', '50.00')
                ->type('sale_price', '75.00')
                ->select('category_id', '1')
                ->select('brand_id', '1')
                ->press('Save Product')
                ->assertSee('Product created successfully')
                ->assertPathIs('/admin/products');

            // Step 2: Create a purchase order
            $browser->clickLink('Purchases')
                ->assertPathIs('/admin/purchases')
                ->click('@add-purchase-btn')
                ->assertPathIs('/admin/purchases/create')
                ->type('supplier_id', '1')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'PO-001')
                ->click('@add-purchase-item-btn')
                ->select('product_id', '1') // Test Product
                ->type('quantity', '10')
                ->type('unit_price', '50.00')
                ->press('Save Purchase')
                ->assertSee('Purchase created successfully')
                ->assertPathIs('/admin/purchases');

            // Step 3: Post the purchase (receive stock)
            $browser->click('@post-purchase-btn')
                ->press('Confirm Post')
                ->assertSee('Purchase posted successfully')
                ->assertSee('Posted');

            // Step 4: Verify stock updated
            $browser->clickLink('Products')
                ->assertSee('Test Product')
                ->assertSee('10'); // Stock quantity

            // Step 5: Create a sale
            $browser->clickLink('Sales')
                ->assertPathIs('/admin/sales')
                ->click('@add-sale-btn')
                ->assertPathIs('/admin/sales/create')
                ->type('customer_id', '1')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'SO-001')
                ->click('@add-sale-item-btn')
                ->select('product_id', '1') // Test Product
                ->type('quantity', '5')
                ->type('unit_price', '75.00')
                ->press('Save Sale')
                ->assertSee('Sale created successfully')
                ->assertPathIs('/admin/sales');

            // Step 6: Post the sale
            $browser->click('@post-sale-btn')
                ->press('Confirm Post')
                ->assertSee('Sale posted successfully')
                ->assertSee('Posted');

            // Step 7: Verify stock reduced
            $browser->clickLink('Products')
                ->assertSee('Test Product')
                ->assertSee('5'); // Remaining stock (10 - 5)

            // Step 8: Check inventory reports
            $browser->clickLink('Reports')
                ->clickLink('Inventory Report')
                ->assertPathIs('/admin/reports/inventory')
                ->assertSee('Test Product')
                ->assertSee('5'); // Current stock
        });
    }

    /**
     * Test stock adjustment workflow
     */
    public function test_stock_adjustment_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // First create a product with stock
            $product = Product::factory()->create(['name' => 'Adjustment Test Product']);

            $browser->loginAs($user)
                ->visit('/admin/stock-adjustments')
                ->click('@add-adjustment-btn')
                ->assertPathIs('/admin/stock-adjustments/create')
                ->select('product_id', $product->id)
                ->select('store_id', '1')
                ->type('adjustment_type', 'increase')
                ->type('quantity', '5')
                ->type('reason', 'Stock found during physical count')
                ->press('Save Adjustment')
                ->assertSee('Stock adjustment created successfully')
                ->assertPathIs('/admin/stock-adjustments');

            // Post the adjustment
            $browser->click('@post-adjustment-btn')
                ->press('Confirm Post')
                ->assertSee('Stock adjustment posted successfully');

            // Verify stock updated
            $browser->clickLink('Products')
                ->assertSee('Adjustment Test Product')
                ->assertSee('5'); // Adjusted stock
        });
    }

    /**
     * Test low stock alerts
     */
    public function test_low_stock_alerts(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create product with low stock
            $product = Product::factory()->create([
                'name' => 'Low Stock Product',
                'minimum_stock' => 10,
                'current_stock' => 5
            ]);

            $browser->loginAs($user)
                ->visit('/admin/dashboard')
                ->assertSee('Low Stock Alert')
                ->assertSee('Low Stock Product')
                ->assertSee('5'); // Current stock

            // Click on low stock alert
            $browser->click('@low-stock-alert')
                ->assertPathIs('/admin/products')
                ->assertSee('Low Stock Product');
        });
    }

    /**
     * Test product search and filtering
     */
    public function test_product_search_and_filtering(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create test products
            Product::factory()->create(['name' => 'Widget A', 'sku' => 'WID-001']);
            Product::factory()->create(['name' => 'Widget B', 'sku' => 'WID-002']);
            Product::factory()->create(['name' => 'Gadget C', 'sku' => 'GAD-001']);

            $browser->loginAs($user)
                ->visit('/admin/products');

            // Test search by name
            $browser->type('@search-input', 'Widget')
                ->press('@search-btn')
                ->assertSee('Widget A')
                ->assertSee('Widget B')
                ->assertDontSee('Gadget C');

            // Test search by SKU
            $browser->clear('@search-input')
                ->type('@search-input', 'WID-001')
                ->press('@search-btn')
                ->assertSee('Widget A')
                ->assertDontSee('Widget B')
                ->assertDontSee('Gadget C');

            // Test category filtering
            $browser->select('@category-filter', '1')
                ->press('@filter-btn')
                ->assertSee('Filtered results');
        });
    }

    /**
     * Test purchase order approval workflow
     */
    public function test_purchase_order_approval_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create a purchase order
            $browser->loginAs($user)
                ->visit('/admin/purchases/create')
                ->type('supplier_id', '1')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'PO-APPROVAL-001')
                ->click('@add-purchase-item-btn')
                ->select('product_id', '1')
                ->type('quantity', '20')
                ->type('unit_price', '100.00')
                ->press('Save Purchase')
                ->assertSee('Purchase created successfully');

            // Submit for approval
            $browser->click('@submit-for-approval-btn')
                ->type('approval_notes', 'Please review and approve this purchase order')
                ->press('Submit for Approval')
                ->assertSee('Purchase order submitted for approval')
                ->assertSee('Pending Approval');

            // Switch to approver user
            $approver = User::factory()->create(['role' => 'approver']);
            $browser->logout()
                ->loginAs($approver)
                ->visit('/admin/purchases')
                ->assertSee('PO-APPROVAL-001')
                ->assertSee('Pending Approval');

            // Approve the purchase order
            $browser->click('@approve-purchase-btn')
                ->type('approval_notes', 'Approved for purchase')
                ->press('Approve')
                ->assertSee('Purchase order approved successfully')
                ->assertSee('Approved');
        });
    }

    /**
     * Test sales order workflow with customer selection
     */
    public function test_sales_order_customer_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/sales/create')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'SO-CUSTOMER-001');

            // Search and select customer
            $browser->click('@customer-search-btn')
                ->type('@customer-search-input', 'Test Customer')
                ->press('@search-customer-btn')
                ->click('@select-customer-btn')
                ->assertSee('Test Customer');

            // Add sale items
            $browser->click('@add-sale-item-btn')
                ->select('product_id', '1')
                ->type('quantity', '3')
                ->type('unit_price', '75.00')
                ->press('Save Sale')
                ->assertSee('Sale created successfully');

            // Verify customer information
            $browser->assertSee('Test Customer')
                ->assertSee('SO-CUSTOMER-001');
        });
    }

    /**
     * Test inventory valuation report
     */
    public function test_inventory_valuation_report(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/inventory-valuation')
                ->assertPathIs('/admin/reports/inventory-valuation')
                ->assertSee('Inventory Valuation Report');

            // Set date range
            $browser->type('@date-from', now()->subMonth()->format('Y-m-d'))
                ->type('@date-to', now()->format('Y-m-d'))
                ->press('@generate-report-btn')
                ->assertSee('Total Inventory Value')
                ->assertSee('$0.00'); // Initial value

            // Export report
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('inventory-valuation-report.pdf');
        });
    }

    /**
     * Test error handling for insufficient stock
     */
    public function test_insufficient_stock_error(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create product with limited stock
            $product = Product::factory()->create([
                'name' => 'Limited Stock Product',
                'current_stock' => 2
            ]);

            $browser->loginAs($user)
                ->visit('/admin/sales/create')
                ->type('customer_id', '1')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'SO-INSUFFICIENT-001')
                ->click('@add-sale-item-btn')
                ->select('product_id', $product->id)
                ->type('quantity', '5') // More than available stock
                ->type('unit_price', '50.00')
                ->press('Save Sale')
                ->assertSee('Insufficient stock available')
                ->assertSee('Available stock: 2');
        });
    }

    /**
     * Test mobile responsive inventory management
     */
    public function test_mobile_inventory_management(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->resize(375, 667) // iPhone SE size
                ->loginAs($user)
                ->visit('/admin/products')
                ->assertVisible('@mobile-product-list')
                ->click('@mobile-add-product-btn')
                ->assertPathIs('/admin/products/create')
                ->type('name', 'Mobile Test Product')
                ->type('sku', 'MOB-001')
                ->press('Save Product')
                ->assertSee('Product created successfully');
        });
    }
}
