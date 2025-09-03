<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Database\Seeders\TestChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FinancialReportingTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(TestChartOfAccountsSeeder::class);

        // Create sample transactions
        $this->createSampleTransactions();
    }

    /**
     * Test trial balance report generation
     */
    public function test_trial_balance_report(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/trial-balance')
                ->assertPathIs('/admin/reports/trial-balance')
                ->assertSee('Trial Balance Report');

            // Verify report structure
            $browser->assertSee('Account')
                ->assertSee('Debit Balance')
                ->assertSee('Credit Balance')
                ->assertSee('Total Debits')
                ->assertSee('Total Credits')
                ->assertSee('Net Balance');

            // Verify trial balance nets to zero
            $browser->assertSee('Net Balance: $0.00');

            // Test date range filtering
            $browser->type('@date-from', now()->subMonth()->format('Y-m-d'))
                ->type('@date-to', now()->format('Y-m-d'))
                ->press('@generate-report-btn')
                ->assertSee('Report generated for period');

            // Test export functionality
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('trial-balance-report.pdf');

            $browser->click('@export-excel-btn')
                ->waitForDownload()
                ->assertDownloaded('trial-balance-report.xlsx');
        });
    }

    /**
     * Test profit and loss statement
     */
    public function test_profit_and_loss_statement(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/profit-loss')
                ->assertPathIs('/admin/reports/profit-loss')
                ->assertSee('Profit & Loss Statement');

            // Verify P&L structure
            $browser->assertSee('Revenue')
                ->assertSee('Cost of Goods Sold')
                ->assertSee('Gross Profit')
                ->assertSee('Operating Expenses')
                ->assertSee('Net Income');

            // Test period selection
            $browser->select('@period-select', 'current-month')
                ->press('@generate-report-btn')
                ->assertSee('Profit & Loss Statement for Current Month');

            // Test custom date range
            $browser->select('@period-select', 'custom')
                ->type('@custom-start-date', now()->subQuarter()->format('Y-m-d'))
                ->type('@custom-end-date', now()->format('Y-m-d'))
                ->press('@generate-report-btn')
                ->assertSee('Profit & Loss Statement for Custom Period');

            // Test export
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('profit-loss-statement.pdf');
        });
    }

    /**
     * Test balance sheet report
     */
    public function test_balance_sheet_report(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/balance-sheet')
                ->assertPathIs('/admin/reports/balance-sheet')
                ->assertSee('Balance Sheet');

            // Verify balance sheet structure
            $browser->assertSee('Assets')
                ->assertSee('Current Assets')
                ->assertSee('Fixed Assets')
                ->assertSee('Liabilities')
                ->assertSee('Current Liabilities')
                ->assertSee('Equity')
                ->assertSee('Total Assets')
                ->assertSee('Total Liabilities & Equity');

            // Verify balance sheet balances
            $browser->assertSee('Total Assets: $0.00')
                ->assertSee('Total Liabilities & Equity: $0.00');

            // Test as-of date selection
            $browser->type('@as-of-date', now()->format('Y-m-d'))
                ->press('@generate-report-btn')
                ->assertSee('Balance Sheet as of');

            // Test export
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('balance-sheet.pdf');
        });
    }

    /**
     * Test cash flow statement
     */
    public function test_cash_flow_statement(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/cash-flow')
                ->assertPathIs('/admin/reports/cash-flow')
                ->assertSee('Cash Flow Statement');

            // Verify cash flow structure
            $browser->assertSee('Operating Activities')
                ->assertSee('Investing Activities')
                ->assertSee('Financing Activities')
                ->assertSee('Net Cash Flow')
                ->assertSee('Beginning Cash')
                ->assertSee('Ending Cash');

            // Test period selection
            $browser->select('@period-select', 'current-year')
                ->press('@generate-report-btn')
                ->assertSee('Cash Flow Statement for Current Year');

            // Test export
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('cash-flow-statement.pdf');
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

            // Verify inventory report structure
            $browser->assertSee('Product')
                ->assertSee('Quantity')
                ->assertSee('Unit Cost')
                ->assertSee('Total Value')
                ->assertSee('Total Inventory Value');

            // Test valuation method selection
            $browser->select('@valuation-method', 'fifo')
                ->press('@generate-report-btn')
                ->assertSee('Inventory Valuation Report (FIFO)');

            $browser->select('@valuation-method', 'lifo')
                ->press('@generate-report-btn')
                ->assertSee('Inventory Valuation Report (LIFO)');

            // Test export
            $browser->click('@export-excel-btn')
                ->waitForDownload()
                ->assertDownloaded('inventory-valuation-report.xlsx');
        });
    }

    /**
     * Test aging reports
     */
    public function test_aging_reports(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Test accounts receivable aging
            $browser->loginAs($user)
                ->visit('/admin/reports/aging-receivables')
                ->assertPathIs('/admin/reports/aging-receivables')
                ->assertSee('Accounts Receivable Aging Report');

            $browser->assertSee('Customer')
                ->assertSee('Current')
                ->assertSee('1-30 Days')
                ->assertSee('31-60 Days')
                ->assertSee('61-90 Days')
                ->assertSee('Over 90 Days')
                ->assertSee('Total Outstanding');

            // Test accounts payable aging
            $browser->visit('/admin/reports/aging-payables')
                ->assertPathIs('/admin/reports/aging-payables')
                ->assertSee('Accounts Payable Aging Report');

            $browser->assertSee('Supplier')
                ->assertSee('Current')
                ->assertSee('1-30 Days')
                ->assertSee('31-60 Days')
                ->assertSee('61-90 Days')
                ->assertSee('Over 90 Days')
                ->assertSee('Total Outstanding');

            // Test export functionality
            $browser->click('@export-pdf-btn')
                ->waitForDownload()
                ->assertDownloaded('aging-receivables-report.pdf');
        });
    }

    /**
     * Test sales and purchase reports
     */
    public function test_sales_and_purchase_reports(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Test sales report
            $browser->loginAs($user)
                ->visit('/admin/reports/sales')
                ->assertPathIs('/admin/reports/sales')
                ->assertSee('Sales Report');

            $browser->assertSee('Date')
                ->assertSee('Customer')
                ->assertSee('Product')
                ->assertSee('Quantity')
                ->assertSee('Unit Price')
                ->assertSee('Total')
                ->assertSee('Total Sales');

            // Test purchase report
            $browser->visit('/admin/reports/purchases')
                ->assertPathIs('/admin/reports/purchases')
                ->assertSee('Purchase Report');

            $browser->assertSee('Date')
                ->assertSee('Supplier')
                ->assertSee('Product')
                ->assertSee('Quantity')
                ->assertSee('Unit Cost')
                ->assertSee('Total')
                ->assertSee('Total Purchases');

            // Test filtering by date range
            $browser->type('@date-from', now()->subMonth()->format('Y-m-d'))
                ->type('@date-to', now()->format('Y-m-d'))
                ->press('@filter-btn')
                ->assertSee('Filtered results for period');

            // Test export
            $browser->click('@export-excel-btn')
                ->waitForDownload()
                ->assertDownloaded('purchases-report.xlsx');
        });
    }

    /**
     * Test report scheduling and automation
     */
    public function test_report_scheduling(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/schedule')
                ->assertPathIs('/admin/reports/schedule')
                ->assertSee('Scheduled Reports');

            // Create scheduled report
            $browser->click('@add-scheduled-report-btn')
                ->assertPathIs('/admin/reports/schedule/create')
                ->select('report_type', 'trial-balance')
                ->select('frequency', 'monthly')
                ->type('email_recipients', 'admin@example.com,accountant@example.com')
                ->type('schedule_date', now()->addDay()->format('Y-m-d'))
                ->press('Create Schedule')
                ->assertSee('Report schedule created successfully')
                ->assertPathIs('/admin/reports/schedule');

            // Verify scheduled report appears in list
            $browser->assertSee('Trial Balance')
                ->assertSee('Monthly')
                ->assertSee('admin@example.com,accountant@example.com');

            // Edit scheduled report
            $browser->click('@edit-schedule-btn')
                ->select('frequency', 'weekly')
                ->press('Update Schedule')
                ->assertSee('Report schedule updated successfully')
                ->assertSee('Weekly');

            // Delete scheduled report
            $browser->click('@delete-schedule-btn')
                ->press('Confirm Delete')
                ->assertSee('Report schedule deleted successfully')
                ->assertDontSee('Trial Balance');
        });
    }

    /**
     * Test report comparison functionality
     */
    public function test_report_comparison(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/comparison')
                ->assertPathIs('/admin/reports/comparison')
                ->assertSee('Report Comparison');

            // Select report type
            $browser->select('report_type', 'profit-loss')
                ->type('period1_start', now()->subMonth()->format('Y-m-d'))
                ->type('period1_end', now()->subMonth()->endOfMonth()->format('Y-m-d'))
                ->type('period2_start', now()->startOfMonth()->format('Y-m-d'))
                ->type('period2_end', now()->format('Y-m-d'))
                ->press('@generate-comparison-btn')
                ->assertSee('Profit & Loss Comparison Report');

            // Verify comparison structure
            $browser->assertSee('Account')
                ->assertSee('Previous Period')
                ->assertSee('Current Period')
                ->assertSee('Variance')
                ->assertSee('Variance %');

            // Test export
            $browser->click('@export-comparison-btn')
                ->waitForDownload()
                ->assertDownloaded('profit-loss-comparison.pdf');
        });
    }

    /**
     * Test report dashboard
     */
    public function test_report_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/reports/dashboard')
                ->assertPathIs('/admin/reports/dashboard')
                ->assertSee('Financial Reports Dashboard');

            // Verify dashboard widgets
            $browser->assertSee('Quick Reports')
                ->assertSee('Recent Reports')
                ->assertSee('Scheduled Reports')
                ->assertSee('Report Statistics');

            // Test quick report generation
            $browser->click('@quick-trial-balance-btn')
                ->assertSee('Trial Balance Report')
                ->assertPathIs('/admin/reports/trial-balance');

            $browser->back()
                ->click('@quick-profit-loss-btn')
                ->assertSee('Profit & Loss Statement')
                ->assertPathIs('/admin/reports/profit-loss');

            // Test recent reports section
            $browser->back()
                ->assertSee('Recent Reports')
                ->assertSee('Last Generated');
        });
    }

    /**
     * Test report permissions
     */
    public function test_report_permissions(): void
    {
        $this->browse(function (Browser $browser) {
            // Create user with limited report permissions
            $user = User::factory()->create();
            $user->givePermissionTo(['report-view-basic']);

            $browser->loginAs($user)
                ->visit('/admin/reports/trial-balance')
                ->assertSee('Trial Balance Report');

            // Test restricted report access
            $browser->visit('/admin/reports/profit-loss')
                ->assertSee('Access Denied')
                ->assertDontSee('Profit & Loss Statement');

            $browser->visit('/admin/reports/balance-sheet')
                ->assertSee('Access Denied')
                ->assertDontSee('Balance Sheet');
        });
    }

    /**
     * Helper method to create sample transactions
     */
    private function createSampleTransactions(): void
    {
        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        if ($cashAccount && $revenueAccount) {
            // Create sample transactions
            DeAccountTransaction::factory()
                ->debit()
                ->amount(10000)
                ->forAccount($cashAccount)
                ->create();

            DeAccountTransaction::factory()
                ->credit()
                ->amount(10000)
                ->forAccount($revenueAccount)
                ->create();
        }
    }
}
