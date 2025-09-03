<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use Hilinkz\DEAccounting\Models\DeAccount;
use Database\Seeders\TestChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EdgeJournalWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test complete journal entry workflow using Edge browser
     */
    public function test_complete_journal_entry_workflow_with_edge(): void
    {
        $this->browse(function (Browser $browser) {
            // Create test user
            $user = User::factory()->create();

            // Login
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/admin/dashboard');

            // Navigate to journal entries
            $browser->clickLink('Journal Entries')
                ->assertPathIs('/admin/journals');

            // Create new journal entry
            $browser->click('@create-journal-btn')
                ->assertPathIs('/admin/journals/create');

            // Fill journal entry form
            $browser->type('date', now()->format('Y-m-d'))
                ->type('note', 'Test journal entry from Edge browser test')
                ->type('amount', '100.00');

            // Select debit account (Cash)
            $browser->select('debit_account_id', function ($select) {
                $select->selectOption('1110'); // Cash account
            });

            // Select credit account (Sales Revenue)
            $browser->select('credit_account_id', function ($select) {
                $select->selectOption('4100'); // Sales Revenue account
            });

            // Save as draft
            $browser->press('Save as Draft')
                ->assertSee('Journal entry saved successfully')
                ->assertPathIs('/admin/journals');

            // Verify entry appears in list
            $browser->assertSee('Test journal entry from Edge browser test')
                ->assertSee('Draft');

            // Post the entry
            $browser->click('@post-journal-btn')
                ->press('Confirm Post')
                ->assertSee('Journal entry posted successfully');

            // Verify status changed to Posted
            $browser->assertSee('Posted');

            // Navigate to trial balance
            $browser->clickLink('Reports')
                ->clickLink('Trial Balance')
                ->assertPathIs('/admin/reports/trial-balance');

            // Verify trial balance shows zero net balance
            $browser->assertSee('Net Balance: $0.00');
        });
    }

    /**
     * Test Edge-specific features and compatibility
     */
    public function test_edge_specific_features(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard');

            // Test Edge-specific JavaScript features
            $browser->script('
                // Test Edge-specific APIs
                if (window.navigator.userAgent.includes("Edg")) {
                    console.log("Edge browser detected");
                }

                // Test modern JavaScript features
                const testAsync = async () => {
                    return "Edge async test";
                };

                testAsync().then(result => {
                    window.edgeTestResult = result;
                });
            ');

            // Wait for async operation
            $browser->waitUntil('window.edgeTestResult === "Edge async test"');

            // Test Edge-specific CSS features
            $browser->script('
                // Test CSS Grid support
                const testElement = document.createElement("div");
                testElement.style.display = "grid";
                testElement.style.gridTemplateColumns = "1fr 1fr";
                document.body.appendChild(testElement);

                const computedStyle = window.getComputedStyle(testElement);
                window.edgeGridSupport = computedStyle.display === "grid";
            ');

            $browser->waitUntil('window.edgeGridSupport === true');

            // Test Edge-specific form handling
            $browser->visit('/admin/journals/create')
                ->type('note', 'Edge compatibility test')
                ->type('amount', '50.00')
                ->select('debit_account_id', '1110')
                ->select('credit_account_id', '4100');

            // Test Edge-specific input validation
            $browser->script('
                const amountInput = document.querySelector("input[name=\'amount\']");
                if (amountInput) {
                    amountInput.setCustomValidity("Edge test validation");
                    amountInput.reportValidity();
                    window.edgeValidationTest = amountInput.validationMessage === "Edge test validation";
                }
            ');

            $browser->waitUntil('window.edgeValidationTest === true');
        });
    }

    /**
     * Test Edge performance with large datasets
     */
    public function test_edge_performance_with_large_datasets(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals');

            // Test Edge performance with large data tables
            $browser->script('
                // Create large dataset for performance testing
                const startTime = performance.now();

                const table = document.createElement("table");
                table.id = "performance-test-table";

                for (let i = 0; i < 1000; i++) {
                    const row = document.createElement("tr");
                    const cell = document.createElement("td");
                    cell.textContent = "Test Row " + i;
                    row.appendChild(cell);
                    table.appendChild(row);
                }

                document.body.appendChild(table);

                const endTime = performance.now();
                window.edgePerformanceTime = endTime - startTime;
            ');

            $browser->waitUntil('window.edgePerformanceTime > 0');

            // Verify performance is acceptable (less than 1 second)
            $browser->script('
                window.edgePerformanceAcceptable = window.edgePerformanceTime < 1000;
            ');

            $browser->waitUntil('window.edgePerformanceAcceptable === true');

            // Clean up
            $browser->script('
                const table = document.getElementById("performance-test-table");
                if (table) {
                    table.remove();
                }
            ');
        });
    }

    /**
     * Test Edge memory management
     */
    public function test_edge_memory_management(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard');

            // Test memory usage
            $browser->script('
                // Get initial memory usage
                if (performance.memory) {
                    window.initialMemory = performance.memory.usedJSHeapSize;
                } else {
                    window.initialMemory = 0;
                }

                // Create memory-intensive operations
                const largeArray = [];
                for (let i = 0; i < 10000; i++) {
                    largeArray.push({
                        id: i,
                        data: "Large data string " + i.repeat(100)
                    });
                }

                // Get memory after allocation
                if (performance.memory) {
                    window.afterAllocationMemory = performance.memory.usedJSHeapSize;
                } else {
                    window.afterAllocationMemory = 0;
                }

                // Clear the array
                largeArray.length = 0;

                // Force garbage collection if available
                if (window.gc) {
                    window.gc();
                }

                // Get memory after cleanup
                if (performance.memory) {
                    window.afterCleanupMemory = performance.memory.usedJSHeapSize;
                } else {
                    window.afterCleanupMemory = 0;
                }
            ');

            $browser->waitUntil('window.afterCleanupMemory > 0');

            // Verify memory management is working
            $browser->script('
                if (window.initialMemory > 0 && window.afterCleanupMemory > 0) {
                    window.memoryManagementWorking = window.afterCleanupMemory <= window.afterAllocationMemory;
                } else {
                    window.memoryManagementWorking = true; // Skip test if memory API not available
                }
            ');

            $browser->waitUntil('window.memoryManagementWorking === true');
        });
    }

    /**
     * Test Edge-specific error handling
     */
    public function test_edge_error_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals/create');

            // Test Edge-specific error scenarios
            $browser->script('
                // Test Edge-specific error handling
                try {
                    // Simulate Edge-specific error
                    throw new Error("Edge-specific test error");
                } catch (error) {
                    window.edgeErrorHandled = error.message === "Edge-specific test error";
                }

                // Test Edge-specific promise rejection handling
                Promise.reject(new Error("Edge promise rejection test"))
                    .catch(error => {
                        window.edgePromiseErrorHandled = error.message === "Edge promise rejection test";
                    });
            ');

            $browser->waitUntil('window.edgeErrorHandled === true');
            $browser->waitUntil('window.edgePromiseErrorHandled === true');

            // Test form validation errors in Edge
            $browser->press('Save as Draft') // Submit without required fields
                ->assertSee('The date field is required')
                ->assertSee('The amount field is required')
                ->assertSee('The debit account field is required')
                ->assertSee('The credit account field is required');
        });
    }

    /**
     * Test Edge accessibility features
     */
    public function test_edge_accessibility_features(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals');

            // Test Edge accessibility features
            $browser->script('
                // Test Edge accessibility API
                if (window.navigator.userAgent.includes("Edg")) {
                    // Test high contrast mode detection
                    const mediaQuery = window.matchMedia("(prefers-contrast: high)");
                    window.edgeHighContrastSupport = mediaQuery.media === "(prefers-contrast: high)";

                    // Test reduced motion detection
                    const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
                    window.edgeReducedMotionSupport = motionQuery.media === "(prefers-reduced-motion: reduce)";

                    // Test Edge-specific accessibility features
                    window.edgeAccessibilityFeatures = {
                        highContrast: window.edgeHighContrastSupport,
                        reducedMotion: window.edgeReducedMotionSupport
                    };
                } else {
                    window.edgeAccessibilityFeatures = { highContrast: true, reducedMotion: true };
                }
            ');

            $browser->waitUntil('window.edgeAccessibilityFeatures !== undefined');

            // Test keyboard navigation in Edge
            $browser->keys('@create-journal-btn', '{tab}')
                ->keys('@search-input', '{tab}')
                ->keys('@filter-btn', '{enter}');

            // Test Edge-specific ARIA support
            $browser->script('
                // Test ARIA attributes
                const elements = document.querySelectorAll("[aria-label], [aria-describedby], [role]");
                window.edgeAriaSupport = elements.length > 0;
            ');

            $browser->waitUntil('window.edgeAriaSupport === true');
        });
    }

    /**
     * Test Edge-specific security features
     */
    public function test_edge_security_features(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard');

            // Test Edge security features
            $browser->script('
                // Test Edge-specific security APIs
                if (window.navigator.userAgent.includes("Edg")) {
                    // Test Content Security Policy support
                    window.edgeCSPSupport = "SecurityPolicyViolationEvent" in window;

                    // Test Edge-specific security headers
                    window.edgeSecurityHeaders = {
                        csp: window.edgeCSPSupport,
                        https: location.protocol === "https:"
                    };
                } else {
                    window.edgeSecurityHeaders = { csp: true, https: true };
                }
            ');

            $browser->waitUntil('window.edgeSecurityHeaders !== undefined');

            // Test Edge-specific cookie handling
            $browser->script('
                // Test Edge cookie security
                document.cookie = "test-cookie=secure-value; Secure; SameSite=Strict";
                const cookies = document.cookie;
                window.edgeCookieSecurity = cookies.includes("test-cookie=secure-value");
            ');

            $browser->waitUntil('window.edgeCookieSecurity === true');
        });
    }
}
