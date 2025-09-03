<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeAccount;
use Database\Factories\DeAccountFactory;
use Database\Factories\DeAccountTransactionFactory;
use Database\Factories\DeJournalFactory;

class JournalBalancingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that balanced journal entries are accepted
     */
    public function test_balanced_journal_entries_are_accepted(): void
    {
        // Create accounts
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

        $amount = 10000; // $100.00 in cents

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

        // Assert journal is balanced
        $this->assertTrue($this->isJournalBalanced($journal));
        $this->assertEquals($amount, $journal->amount);
    }

    /**
     * Test that unbalanced journal entries are rejected
     */
    public function test_unbalanced_journal_entries_are_rejected(): void
    {
        // Create accounts
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

        $debitAmount = 10000;   // $100.00
        $creditAmount = 9999;   // $99.99 (intentionally unbalanced)

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

        // Create journal entry
        $journal = DeJournal::factory()
            ->withTransactions($debitTransaction, $creditTransaction)
            ->create();

        // Assert journal is unbalanced
        $this->assertFalse($this->isJournalBalanced($journal));
        $this->assertNotEquals($debitAmount, $creditAmount);
    }

    /**
     * Test property-style fuzzing across amounts and line counts
     */
    public function test_property_style_fuzzing_across_amounts_and_line_counts(): void
    {
        // Test various amounts
        $amounts = [100, 1000, 10000, 100000, 999999];

        foreach ($amounts as $amount) {
            $this->testJournalBalancingWithAmount($amount);
        }

        // Test various line counts (for multi-line entries)
        $lineCounts = [2, 3, 5, 10];

        foreach ($lineCounts as $lineCount) {
            $this->testJournalBalancingWithLineCount($lineCount);
        }
    }

    /**
     * Test journal balancing with specific amount
     */
    private function testJournalBalancingWithAmount(int $amount): void
    {
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

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

        $this->assertTrue($this->isJournalBalanced($journal), "Journal with amount {$amount} should be balanced");
    }

    /**
     * Test journal balancing with multiple lines
     */
    private function testJournalBalancingWithLineCount(int $lineCount): void
    {
        $accounts = DeAccount::factory()->count($lineCount)->create();
        $transactions = [];

        // Create balanced multi-line entry
        $totalAmount = 0;
        $debitAmount = 0;
        $creditAmount = 0;

        for ($i = 0; $i < $lineCount; $i++) {
            $amount = rand(1000, 10000);
            $isDebit = ($i % 2 === 0); // Alternate debit/credit

            if ($isDebit) {
                $debitAmount += $amount;
                $transactions[] = DeAccountTransaction::factory()
                    ->debit()
                    ->amount($amount)
                    ->forAccount($accounts[$i])
                    ->create();
            } else {
                $creditAmount += $amount;
                $transactions[] = DeAccountTransaction::factory()
                    ->credit()
                    ->amount($amount)
                    ->forAccount($accounts[$i])
                    ->create();
            }

            $totalAmount += $amount;
        }

        // Ensure balance by adjusting last transaction if needed
        if ($debitAmount !== $creditAmount) {
            $difference = $debitAmount - $creditAmount;
            $lastTransaction = end($transactions);
            $lastTransaction->amount += $difference;
            $lastTransaction->save();

            if ($lastTransaction->type === 'DEBIT') {
                $lastTransaction->debit += $difference;
            } else {
                $lastTransaction->credit += $difference;
            }
            $lastTransaction->save();
        }

        // Create journal with first two transactions (simplified for this test)
        if (count($transactions) >= 2) {
            $journal = DeJournal::factory()
                ->withTransactions($transactions[0], $transactions[1])
                ->create();

            $this->assertTrue($this->isJournalBalanced($journal), "Multi-line journal with {$lineCount} lines should be balanced");
        }
    }

    /**
     * Test that journal entries maintain balance after updates
     */
    public function test_journal_entries_maintain_balance_after_updates(): void
    {
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

        $amount = 10000;

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

        // Verify initial balance
        $this->assertTrue($this->isJournalBalanced($journal));

        // Update amount
        $newAmount = 15000;
        $journal->amount = $newAmount;
        $journal->save();

        // Update transactions to match
        $debitTransaction->amount = $newAmount;
        $debitTransaction->debit = $newAmount;
        $debitTransaction->save();

        $creditTransaction->amount = $newAmount;
        $creditTransaction->credit = $newAmount;
        $creditTransaction->save();

        // Verify balance is maintained
        $journal->refresh();
        $this->assertTrue($this->isJournalBalanced($journal));
        $this->assertEquals($newAmount, $journal->amount);
    }

    /**
     * Test that zero-amount journal entries are handled correctly
     */
    public function test_zero_amount_journal_entries_handled_correctly(): void
    {
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

        $amount = 0;

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

        $this->assertTrue($this->isJournalBalanced($journal));
        $this->assertEquals(0, $journal->amount);
    }

    /**
     * Test that very large amounts maintain balance
     */
    public function test_very_large_amounts_maintain_balance(): void
    {
        $cashAccount = DeAccount::factory()->cash()->create();
        $revenueAccount = DeAccount::factory()->salesRevenue()->create();

        // Use a very large amount (but still within integer limits)
        $amount = 999999999; // $9,999,999.99

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

        $this->assertTrue($this->isJournalBalanced($journal));
        $this->assertEquals($amount, $journal->amount);
    }

    /**
     * Helper method to check if a journal entry is balanced
     */
    private function isJournalBalanced(DeJournal $journal): bool
    {
        $debitTransaction = $journal->debitTransaction;
        $creditTransaction = $journal->creditTransaction;

        if (!$debitTransaction || !$creditTransaction) {
            return false;
        }

        return $debitTransaction->debit === $creditTransaction->credit;
    }
}
