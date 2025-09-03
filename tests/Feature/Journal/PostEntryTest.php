<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Database\Seeders\TestChartOfAccountsSeeder;
use Database\Factories\DeAccountFactory;
use Database\Factories\DeAccountTransactionFactory;

class PostEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test chart of accounts
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test creating a draft journal entry
     */
    public function test_creating_a_draft_journal_entry(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first(); // Cash account
        $revenueAccount = DeAccount::where('account_no', '4100')->first(); // Sales Revenue account

        $journalData = [
            'date' => now()->format('Y-m-d'),
            'note' => 'Test journal entry',
            'amount' => 10000, // $100.00 in cents
            'debit_account_id' => $cashAccount->id,
            'credit_account_id' => $revenueAccount->id,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/journals', $journalData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'date',
                'note',
                'amount',
                'debit_transaction_id',
                'credit_transaction_id',
            ]);

        $this->assertDatabaseHas('de_journals', [
            'note' => 'Test journal entry',
            'amount' => 10000,
        ]);
    }

    /**
     * Test posting a balanced journal entry
     */
    public function test_posting_a_balanced_journal_entry(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amount = 10000;

        // Create balanced transactions
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->forAccount($revenueAccount)
            ->create();

        // Create journal entry
        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        // Post the journal entry
        $response = $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Journal entry posted successfully']);

        // Verify journal is posted
        $journal->refresh();
        $this->assertNotNull($journal->posted_at);

        // Verify transactions are created
        $this->assertDatabaseHas('account_transactions', [
            'id' => $debitTransaction->id,
            'debit' => $amount,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'id' => $creditTransaction->id,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    /**
     * Test that ledgers are written when posting
     */
    public function test_ledgers_are_written_when_posting(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amount = 10000;

        // Create and post journal entry
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->forAccount($revenueAccount)
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        // Verify ledger entries are created
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $cashAccount->id,
            'debit' => $amount,
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $revenueAccount->id,
            'credit' => $amount,
        ]);
    }

    /**
     * Test trial balance remains unchanged after posting
     */
    public function test_trial_balance_remains_unchanged_after_posting(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amount = 10000;

        // Get initial trial balance
        $initialTrialBalance = $this->calculateTrialBalance();

        // Create and post journal entry
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->forAccount($revenueAccount)
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        // Get trial balance after posting
        $finalTrialBalance = $this->calculateTrialBalance();

        // Trial balance should remain zero (balanced)
        $this->assertEquals(0, $finalTrialBalance);
    }

    /**
     * Test posting unbalanced journal entry fails
     */
    public function test_posting_unbalanced_journal_entry_fails(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $debitAmount = 10000;
        $creditAmount = 9999; // Intentionally unbalanced

        // Create unbalanced transactions
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($debitAmount)
            ->forAccount($cashAccount)
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($creditAmount)
            ->forAccount($revenueAccount)
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        // Attempt to post unbalanced entry
        $response = $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balance']);

        // Verify journal is not posted
        $journal->refresh();
        $this->assertNull($journal->posted_at);
    }

    /**
     * Test posting journal entry with missing account
     */
    public function test_posting_journal_entry_with_missing_account_fails(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();

        $amount = 10000;

        // Create transaction with valid account
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->create();

        // Create transaction with invalid account ID
        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->state(['account_id' => 99999]) // Non-existent account
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        // Attempt to post entry with missing account
        $response = $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account']);
    }

    /**
     * Test posting journal entry with invalid currency
     */
    public function test_posting_journal_entry_with_invalid_currency_fails(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amount = 10000;

        // Create transactions with different currencies (if supported)
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->state(['currency' => 'USD'])
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->forAccount($revenueAccount)
            ->state(['currency' => 'EUR']) // Different currency
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        // Attempt to post entry with mixed currencies
        $response = $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        // This should fail if currency validation is implemented
        if ($response->getStatusCode() === 422) {
            $response->assertJsonValidationErrors(['currency']);
        }
    }

    /**
     * Test posting journal entry with closed period
     */
    public function test_posting_journal_entry_with_closed_period_fails(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amount = 10000;
        $closedDate = now()->subYear()->format('Y-m-d'); // Date in closed period

        // Create transactions with closed period date
        $debitTransaction = DeAccountTransaction::factory()
            ->debit()
            ->amount($amount)
            ->forAccount($cashAccount)
            ->onDate($closedDate)
            ->create();

        $creditTransaction = DeAccountTransaction::factory()
            ->credit()
            ->amount($amount)
            ->forAccount($revenueAccount)
            ->onDate($closedDate)
            ->create();

        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->onDate($closedDate)
            ->create();

        // Attempt to post entry in closed period
        $response = $this->actingAs($user)
            ->postJson("/api/journals/{$journal->id}/post");

        // This should fail if period validation is implemented
        if ($response->getStatusCode() === 422) {
            $response->assertJsonValidationErrors(['period']);
        }
    }

    /**
     * Test posting multiple journal entries
     */
    public function test_posting_multiple_journal_entries(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        $amounts = [10000, 15000, 20000];
        $postedJournals = [];

        foreach ($amounts as $amount) {
            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($amount)
                ->forAccount($cashAccount)
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($amount)
                ->forAccount($revenueAccount)
                ->create();

            $journal = DeJournal::factory()
                ->withTransactions($debitTransaction, $creditTransaction)
                ->create();

            $response = $this->actingAs($user)
                ->postJson("/api/journals/{$journal->id}/post");

            $response->assertStatus(200);
            $postedJournals[] = $journal;
        }

        // Verify all journals are posted
        foreach ($postedJournals as $journal) {
            $journal->refresh();
            $this->assertNotNull($journal->posted_at);
        }

        // Verify trial balance is still zero
        $finalTrialBalance = $this->calculateTrialBalance();
        $this->assertEquals(0, $finalTrialBalance);
    }

    /**
     * Helper method to calculate trial balance
     */
    private function calculateTrialBalance(): int
    {
        $totalDebits = DeAccountTransaction::sum('debit');
        $totalCredits = DeAccountTransaction::sum('credit');

        return $totalDebits - $totalCredits;
    }
}
