<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use Hilinkz\DEAccounting\Models\DeAccount;
use Database\Seeders\TestChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class JournalWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test complete journal entry workflow
     */
    public function test_complete_journal_entry_workflow(): void
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
                ->type('note', 'Test journal entry from browser test')
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
            $browser->assertSee('Test journal entry from browser test')
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
     * Test unbalanced journal entry rejection
     */
    public function test_unbalanced_journal_entry_rejection(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals/create')
                ->type('date', now()->format('Y-m-d'))
                ->type('note', 'Unbalanced test entry')
                ->type('amount', '100.00')
                ->select('debit_account_id', '1110') // Cash
                ->select('credit_account_id', '4100') // Sales Revenue
                ->type('credit_amount', '99.99') // Intentionally unbalanced
                ->press('Save as Draft')
                ->assertSee('Journal entry must be balanced')
                ->assertPathIs('/admin/journals/create');
        });
    }

    /**
     * Test journal entry editing workflow
     */
    public function test_journal_entry_editing_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create a draft journal entry first
            $browser->loginAs($user)
                ->visit('/admin/journals/create')
                ->type('date', now()->format('Y-m-d'))
                ->type('note', 'Editable journal entry')
                ->type('amount', '50.00')
                ->select('debit_account_id', '1110')
                ->select('credit_account_id', '4100')
                ->press('Save as Draft')
                ->assertSee('Journal entry saved successfully');

            // Edit the entry
            $browser->click('@edit-journal-btn')
                ->assertPathIs('/admin/journals/*/edit')
                ->type('note', 'Updated journal entry note')
                ->type('amount', '75.00')
                ->press('Update Entry')
                ->assertSee('Journal entry updated successfully');

            // Verify changes
            $browser->assertSee('Updated journal entry note')
                ->assertSee('$75.00');
        });
    }

    /**
     * Test journal entry reversal workflow
     */
    public function test_journal_entry_reversal_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Create and post a journal entry
            $browser->loginAs($user)
                ->visit('/admin/journals/create')
                ->type('date', now()->format('Y-m-d'))
                ->type('note', 'Entry to be reversed')
                ->type('amount', '200.00')
                ->select('debit_account_id', '1110')
                ->select('credit_account_id', '4100')
                ->press('Post Entry')
                ->assertSee('Journal entry posted successfully');

            // Reverse the entry
            $browser->click('@reverse-journal-btn')
                ->type('reversal_note', 'Reversing entry due to error')
                ->press('Confirm Reversal')
                ->assertSee('Journal entry reversed successfully');

            // Verify reversal entry created
            $browser->assertSee('Reversing entry due to error')
                ->assertSee('Reversed');
        });
    }

    /**
     * Test journal entry search and filtering
     */
    public function test_journal_entry_search_and_filtering(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals');

            // Test search functionality
            $browser->type('@search-input', 'Test')
                ->press('@search-btn')
                ->assertSee('Test');

            // Test date filtering
            $browser->type('@date-from', now()->subDays(7)->format('Y-m-d'))
                ->type('@date-to', now()->format('Y-m-d'))
                ->press('@filter-btn')
                ->assertSee('Filtered results');

            // Test status filtering
            $browser->select('@status-filter', 'Draft')
                ->press('@filter-btn')
                ->assertSee('Draft');
        });
    }

    /**
     * Test permission-based access control
     */
    public function test_journal_entry_permission_control(): void
    {
        $this->browse(function (Browser $browser) {
            // Create user without journal permissions
            $user = User::factory()->create();
            // Note: In real implementation, you would assign specific roles/permissions

            $browser->loginAs($user)
                ->visit('/admin/journals')
                ->assertSee('Access Denied')
                ->assertDontSee('Create Journal Entry');
        });
    }

    /**
     * Test responsive design on mobile
     */
    public function test_mobile_responsive_design(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->resize(375, 667) // iPhone SE size
                ->loginAs($user)
                ->visit('/admin/journals')
                ->assertVisible('@mobile-menu-toggle')
                ->click('@mobile-menu-toggle')
                ->assertVisible('@mobile-navigation');
        });
    }

    /**
     * Test error handling and validation
     */
    public function test_journal_entry_error_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/journals/create')
                ->press('Save as Draft') // Submit without required fields
                ->assertSee('The date field is required')
                ->assertSee('The amount field is required')
                ->assertSee('The debit account field is required')
                ->assertSee('The credit account field is required');
        });
    }
}
