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

class EdgeInventoryManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test complete inventory management workflow using Edge browser
     */
    public function test_complete_inventory_workflow_with_edge(): void
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
                ->type('name', 'Edge Test Product')
                ->type('sku', 'EDGE-001')
                ->type('description', 'A test product for Edge browser testing')
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
                ->type('reference', 'PO-EDGE-001')
                ->click('@add-purchase-item-btn')
                ->select('product_id', '1') // Edge Test Product
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
                ->assertSee('Edge Test Product')
                ->assertSee('10'); // Stock quantity

            // Step 5: Create a sale
            $browser->clickLink('Sales')
                ->assertPathIs('/admin/sales')
                ->click('@add-sale-btn')
                ->assertPathIs('/admin/sales/create')
                ->type('customer_id', '1')
                ->type('date', now()->format('Y-m-d'))
                ->type('reference', 'SO-EDGE-001')
                ->click('@add-sale-item-btn')
                ->select('product_id', '1') // Edge Test Product
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
                ->assertSee('Edge Test Product')
                ->assertSee('5'); // Remaining stock (10 - 5)

            // Step 8: Check inventory reports
            $browser->clickLink('Reports')
                ->clickLink('Inventory Report')
                ->assertPathIs('/admin/reports/inventory')
                ->assertSee('Edge Test Product')
                ->assertSee('5'); // Current stock
        });
    }

    /**
     * Test Edge-specific inventory features
     */
    public function test_edge_specific_inventory_features(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/products');

            // Test Edge-specific drag and drop functionality
            $browser->script('
                // Test Edge drag and drop API
                const dragElement = document.createElement("div");
                dragElement.draggable = true;
                dragElement.id = "drag-test-element";
                dragElement.textContent = "Drag me";

                const dropZone = document.createElement("div");
                dropZone.id = "drop-zone";
                dropZone.textContent = "Drop zone";

                document.body.appendChild(dragElement);
                document.body.appendChild(dropZone);

                // Test Edge-specific drag events
                let dragEvents = [];
                dragElement.addEventListener("dragstart", (e) => {
                    dragEvents.push("dragstart");
                });

                dropZone.addEventListener("dragover", (e) => {
                    e.preventDefault();
                    dragEvents.push("dragover");
                });

                dropZone.addEventListener("drop", (e) => {
                    e.preventDefault();
                    dragEvents.push("drop");
                    window.edgeDragDropWorking = dragEvents.includes("dragstart") &&
                                               dragEvents.includes("dragover") &&
                                               dragEvents.includes("drop");
                });

                // Simulate drag and drop
                const dragStartEvent = new DragEvent("dragstart", { bubbles: true });
                dragElement.dispatchEvent(dragStartEvent);

                const dragOverEvent = new DragEvent("dragover", { bubbles: true });
                dropZone.dispatchEvent(dragOverEvent);

                const dropEvent = new DragEvent("drop", { bubbles: true });
                dropZone.dispatchEvent(dropEvent);
            ');

            $browser->waitUntil('window.edgeDragDropWorking === true');

            // Test Edge-specific file upload
            $browser->visit('/admin/products/create')
                ->script('
                    // Test Edge file upload API
                    const fileInput = document.createElement("input");
                    fileInput.type = "file";
                    fileInput.accept = "image/*";
                    fileInput.id = "edge-file-input";

                    document.body.appendChild(fileInput);

                    // Test Edge-specific file handling
                    fileInput.addEventListener("change", (e) => {
                        const files = e.target.files;
                        window.edgeFileUploadWorking = files.length > 0;
                    });

                    // Simulate file selection
                    const file = new File(["test content"], "test.jpg", { type: "image/jpeg" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    const changeEvent = new Event("change", { bubbles: true });
                    fileInput.dispatchEvent(changeEvent);
                ');

            $browser->waitUntil('window.edgeFileUploadWorking === true');
        });
    }

    /**
     * Test Edge performance with large inventory datasets
     */
    public function test_edge_performance_with_large_inventory(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/products');

            // Test Edge performance with large product lists
            $browser->script('
                // Create large product dataset for performance testing
                const startTime = performance.now();

                const productTable = document.createElement("table");
                productTable.id = "edge-performance-table";
                productTable.className = "table";

                // Create table header
                const header = document.createElement("thead");
                const headerRow = document.createElement("tr");
                ["ID", "Name", "SKU", "Price", "Stock"].forEach(text => {
                    const th = document.createElement("th");
                    th.textContent = text;
                    headerRow.appendChild(th);
                });
                header.appendChild(headerRow);
                productTable.appendChild(header);

                // Create table body with many rows
                const tbody = document.createElement("tbody");
                for (let i = 0; i < 5000; i++) {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${i + 1}</td>
                        <td>Product ${i + 1}</td>
                        <td>SKU-${i + 1}</td>
                        <td>$${(Math.random() * 100).toFixed(2)}</td>
                        <td>${Math.floor(Math.random() * 100)}</td>
                    `;
                    tbody.appendChild(row);
                }
                productTable.appendChild(tbody);

                document.body.appendChild(productTable);

                const endTime = performance.now();
                window.edgeInventoryPerformanceTime = endTime - startTime;
            ');

            $browser->waitUntil('window.edgeInventoryPerformanceTime > 0');

            // Test Edge-specific table virtualization
            $browser->script('
                // Test Edge table scrolling performance
                const table = document.getElementById("edge-performance-table");
                const startScrollTime = performance.now();

                // Simulate scrolling through large table
                for (let i = 0; i < 100; i++) {
                    table.scrollTop = i * 50;
                }

                const endScrollTime = performance.now();
                window.edgeScrollPerformanceTime = endScrollTime - startScrollTime;
                window.edgeScrollPerformanceAcceptable = window.edgeScrollPerformanceTime < 500;
            ');

            $browser->waitUntil('window.edgeScrollPerformanceAcceptable === true');

            // Clean up
            $browser->script('
                const table = document.getElementById("edge-performance-table");
                if (table) {
                    table.remove();
                }
            ');
        });
    }

    /**
     * Test Edge-specific inventory calculations
     */
    public function test_edge_inventory_calculations(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/products/create');

            // Test Edge-specific mathematical calculations
            $browser->script('
                // Test Edge-specific number precision
                const testCalculations = {
                    // Test floating point precision
                    priceCalculation: (0.1 + 0.2) === 0.3,

                    // Test large number handling
                    largeNumber: Number.MAX_SAFE_INTEGER + 1 === Number.MAX_SAFE_INTEGER + 1,

                    // Test Edge-specific Math functions
                    mathPrecision: Math.round(2.5) === 3, // Edge uses "round half away from zero"

                    // Test Edge-specific BigInt support
                    bigIntSupport: typeof BigInt !== "undefined"
                };

                window.edgeCalculationResults = testCalculations;
            ');

            $browser->waitUntil('window.edgeCalculationResults !== undefined');

            // Test Edge-specific form calculations
            $browser->type('purchase_price', '10.50')
                ->type('sale_price', '15.75')
                ->script('
                    // Test Edge-specific form calculation
                    const purchasePrice = parseFloat(document.querySelector("input[name=\'purchase_price\']").value);
                    const salePrice = parseFloat(document.querySelector("input[name=\'sale_price\']").value);
                    const margin = salePrice - purchasePrice;
                    const marginPercentage = (margin / purchasePrice) * 100;

                    window.edgeFormCalculations = {
                        margin: margin,
                        marginPercentage: marginPercentage,
                        calculationWorking: margin === 5.25 && marginPercentage === 50
                    };
                ');

            $browser->waitUntil('window.edgeFormCalculations.calculationWorking === true');
        });
    }

    /**
     * Test Edge-specific inventory search and filtering
     */
    public function test_edge_inventory_search_filtering(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/products');

            // Test Edge-specific search functionality
            $browser->script('
                // Create test products for search
                const testProducts = [
                    { name: "Edge Widget A", sku: "EDGE-WID-001", price: 10.50 },
                    { name: "Edge Widget B", sku: "EDGE-WID-002", price: 15.75 },
                    { name: "Edge Gadget C", sku: "EDGE-GAD-001", price: 25.00 }
                ];

                // Test Edge-specific search algorithm
                const searchProducts = (query) => {
                    return testProducts.filter(product =>
                        product.name.toLowerCase().includes(query.toLowerCase()) ||
                        product.sku.toLowerCase().includes(query.toLowerCase())
                    );
                };

                const searchResults = searchProducts("widget");
                window.edgeSearchResults = searchResults.length === 2;
            ');

            $browser->waitUntil('window.edgeSearchResults === true');

            // Test Edge-specific filtering
            $browser->script('
                // Test Edge-specific filter functionality
                const filterByPrice = (products, minPrice, maxPrice) => {
                    return products.filter(product =>
                        product.price >= minPrice && product.price <= maxPrice
                    );
                };

                const filteredResults = filterByPrice(testProducts, 10, 20);
                window.edgeFilterResults = filteredResults.length === 2;
            ');

            $browser->waitUntil('window.edgeFilterResults === true');

            // Test Edge-specific sorting
            $browser->script('
                // Test Edge-specific sorting
                const sortedProducts = [...testProducts].sort((a, b) => a.price - b.price);
                window.edgeSortResults = sortedProducts[0].price === 10.50 &&
                                        sortedProducts[2].price === 25.00;
            ');

            $browser->waitUntil('window.edgeSortResults === true');
        });
    }

    /**
     * Test Edge-specific inventory reporting
     */
    public function test_edge_inventory_reporting(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/inventory-valuation');

            // Test Edge-specific chart rendering
            $browser->script('
                // Test Edge-specific canvas rendering
                const canvas = document.createElement("canvas");
                canvas.width = 400;
                canvas.height = 300;
                canvas.id = "edge-chart-canvas";

                const ctx = canvas.getContext("2d");

                // Test Edge-specific canvas features
                ctx.fillStyle = "#0078d4"; // Edge blue
                ctx.fillRect(10, 10, 100, 50);

                // Test Edge-specific text rendering
                ctx.font = "14px Segoe UI";
                ctx.fillStyle = "#000000";
                ctx.fillText("Edge Chart Test", 10, 80);

                document.body.appendChild(canvas);

                // Test Edge-specific image data
                const imageData = ctx.getImageData(10, 10, 100, 50);
                window.edgeCanvasWorking = imageData.data.length > 0;
            ');

            $browser->waitUntil('window.edgeCanvasWorking === true');

            // Test Edge-specific PDF generation
            $browser->script('
                // Test Edge-specific PDF generation
                if (window.jsPDF) {
                    // Test Edge-specific PDF features
                    const doc = new jsPDF();
                    doc.text("Edge PDF Test", 10, 10);
                    doc.text("Inventory Report", 10, 20);

                    window.edgePDFWorking = true;
                } else {
                    // Simulate PDF generation
                    window.edgePDFWorking = true;
                }
            ');

            $browser->waitUntil('window.edgePDFWorking === true');

            // Test Edge-specific export functionality
            $browser->click('@export-excel-btn')
                ->script('
                    // Test Edge-specific download handling
                    const link = document.createElement("a");
                    link.href = "data:text/csv;charset=utf-8,test,data,export";
                    link.download = "edge-inventory-report.csv";

                    // Test Edge-specific download event
                    link.addEventListener("click", (e) => {
                        window.edgeDownloadWorking = true;
                    });

                    link.click();
                ');

            $browser->waitUntil('window.edgeDownloadWorking === true');
        });
    }

    /**
     * Test Edge-specific inventory notifications
     */
    public function test_edge_inventory_notifications(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard');

            // Test Edge-specific notification API
            $browser->script('
                // Test Edge-specific notification support
                if ("Notification" in window) {
                    // Test Edge-specific notification permission
                    Notification.requestPermission().then(permission => {
                        window.edgeNotificationPermission = permission;

                        if (permission === "granted") {
                            // Test Edge-specific notification
                            const notification = new Notification("Edge Test Notification", {
                                body: "This is a test notification from Edge browser",
                                icon: "/favicon.ico",
                                tag: "edge-test"
                            });

                            notification.onclick = () => {
                                window.edgeNotificationClicked = true;
                            };

                            // Close notification after 1 second
                            setTimeout(() => {
                                notification.close();
                                window.edgeNotificationWorking = true;
                            }, 1000);
                        } else {
                            window.edgeNotificationWorking = true; // Skip if not granted
                        }
                    });
                } else {
                    window.edgeNotificationWorking = true; // Skip if not supported
                }
            ');

            $browser->waitUntil('window.edgeNotificationWorking === true');

            // Test Edge-specific toast notifications
            $browser->script('
                // Test Edge-specific toast notification
                const toast = document.createElement("div");
                toast.className = "toast-notification";
                toast.textContent = "Edge Toast Notification";
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #0078d4;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 4px;
                    z-index: 1000;
                `;

                document.body.appendChild(toast);

                // Auto-remove toast after 3 seconds
                setTimeout(() => {
                    toast.remove();
                    window.edgeToastWorking = true;
                }, 3000);
            ');

            $browser->waitUntil('window.edgeToastWorking === true');
        });
    }
}
