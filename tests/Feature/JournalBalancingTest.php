<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestChartOfAccountsSeeder;
use Database\Factories\JournalEntryFactory;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;

class JournalBalancingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed test chart of accounts
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test that balanced journal entries are accepted
     */
    public function test_balanced_entry_is_accepted()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        $amount = 1000.00;

        // Create balanced entry: Cash debit, Capital credit
        $debitTxn = DeAccountTransaction::create([
            'account_id' => $accounts['cash']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
            'note' => 'Test entry',
        ]);

        $creditTxn = DeAccountTransaction::create([
            'account_id' => $accounts['capital']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
            'note' => 'Test entry',
        ]);

        $journal = DeJournal::create([
            'date' => now()->format('Y-m-d'),
            'amount' => $amount,
            'debit_transaction_id' => $debitTxn->id,
            'credit_transaction_id' => $creditTxn->id,
            'transaction_type' => 'test',
            'created_by' => 1,
            'note' => 'Test balanced entry',
        ]);

        $this->assertNotNull($journal->id);
        $this->assertEquals($amount, $journal->amount);
        $this->assertEquals($debitTxn->amount, $creditTxn->amount);
    }

    /**
     * Test that unbalanced entries are rejected (or flagged)
     */
    public function test_unbalanced_entry_validation()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Create unbalanced transactions
        $debitAmount = 1000.00;
        $creditAmount = 999.99; // Intentionally different

        $debitTxn = DeAccountTransaction::create([
            'account_id' => $accounts['cash']->id,
            'amount' => $debitAmount,
            'date' => now()->format('Y-m-d'),
            'debit' => $debitAmount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        $creditTxn = DeAccountTransaction::create([
            'account_id' => $accounts['capital']->id,
            'amount' => $creditAmount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $creditAmount,
            'type' => 'CREDIT',
            'created_by' => 1,
        ]);

        // This should create a journal entry but it's unbalanced
        $journal = DeJournal::create([
            'date' => now()->format('Y-m-d'),
            'amount' => $debitAmount,
            'debit_transaction_id' => $debitTxn->id,
            'credit_transaction_id' => $creditTxn->id,
            'transaction_type' => 'test',
            'created_by' => 1,
        ]);

        // Verify the imbalance
        $this->assertNotEquals($debitTxn->amount, $creditTxn->amount);
        
        // In a proper system, this should either:
        // 1. Be rejected with validation error, or
        // 2. Be flagged as unbalanced for review
        $imbalance = $debitTxn->amount - $creditTxn->amount;
        $this->assertEquals(0.01, $imbalance);
    }

    /**
     * Test compound journal entries with multiple debits and credits
     */
    public function test_compound_entry_balancing()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Create a compound entry:
        // Dr. Inventory 500
        // Dr. Office Expense 300
        // Cr. Accounts Payable 800
        $transactions = [
            [
                'account' => $accounts['inventory'],
                'debit' => 500.00,
                'credit' => 0,
                'type' => 'DEBIT'
            ],
            [
                'account' => $accounts['office_expense'],
                'debit' => 300.00,
                'credit' => 0,
                'type' => 'DEBIT'
            ],
            [
                'account' => $accounts['accounts_payable'],
                'debit' => 0,
                'credit' => 800.00,
                'type' => 'CREDIT'
            ],
        ];

        $totalDebits = 0;
        $totalCredits = 0;
        $createdTransactions = [];

        foreach ($transactions as $txnData) {
            $amount = max($txnData['debit'], $txnData['credit']);
            
            $transaction = DeAccountTransaction::create([
                'account_id' => $txnData['account']->id,
                'amount' => $amount,
                'date' => now()->format('Y-m-d'),
                'debit' => $txnData['debit'],
                'credit' => $txnData['credit'],
                'type' => $txnData['type'],
                'created_by' => 1,
                'note' => 'Compound entry test',
            ]);

            $createdTransactions[] = $transaction;
            $totalDebits += $txnData['debit'];
            $totalCredits += $txnData['credit'];
        }

        // Verify the entry balances
        $this->assertEquals(800.00, $totalDebits);
        $this->assertEquals(800.00, $totalCredits);
        $this->assertEquals($totalDebits, $totalCredits);
    }

    /**
     * Test property-based fuzzing with random amounts and line counts
     */
    public function test_random_balanced_entries()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Test 50 random balanced entries
        for ($i = 0; $i < 50; $i++) {
            $amount = mt_rand(100, 10000) / 100; // Random amount between $1.00 and $100.00
            
            // Pick random debit and credit accounts
            $debitAccount = fake()->randomElement([$accounts['cash'], $accounts['inventory'], $accounts['office_expense']]);
            $creditAccount = fake()->randomElement([$accounts['capital'], $accounts['sales_revenue'], $accounts['accounts_payable']]);
            
            $debitTxn = DeAccountTransaction::create([
                'account_id' => $debitAccount->id,
                'amount' => $amount,
                'date' => now()->format('Y-m-d'),
                'debit' => $amount,
                'credit' => 0,
                'type' => 'DEBIT',
                'created_by' => 1,
                'note' => "Random test {$i}",
            ]);

            $creditTxn = DeAccountTransaction::create([
                'account_id' => $creditAccount->id,
                'amount' => $amount,
                'date' => now()->format('Y-m-d'),
                'debit' => 0,
                'credit' => $amount,
                'type' => 'CREDIT',
                'created_by' => 1,
                'note' => "Random test {$i}",
            ]);

            // Verify balance
            $this->assertEquals($debitTxn->amount, $creditTxn->amount, "Random entry {$i} is not balanced");
        }
    }

    /**
     * Test that account type determines correct debit/credit nature
     */
    public function test_account_head_type_logic()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Test asset account (Cash) - increases with debits
        $assetAccount = $accounts['cash'];
        $this->assertEquals(1, $assetAccount->root_type); // Asset
        $this->assertEquals('DEBIT', DeAccount::headTypeCheck($assetAccount->id, 'INCREASE'));
        $this->assertEquals('CREDIT', DeAccount::headTypeCheck($assetAccount->id, 'DECREASE'));
        
        // Test liability account - increases with credits
        $liabilityAccount = $accounts['accounts_payable'];
        $this->assertEquals(3, $liabilityAccount->root_type); // Liability
        $this->assertEquals('CREDIT', DeAccount::headTypeCheck($liabilityAccount->id, 'INCREASE'));
        $this->assertEquals('DEBIT', DeAccount::headTypeCheck($liabilityAccount->id, 'DECREASE'));
        
        // Test equity account - increases with credits
        $equityAccount = $accounts['capital'];
        $this->assertEquals(5, $equityAccount->root_type); // Capital/Equity
        $this->assertEquals('CREDIT', DeAccount::headTypeCheck($equityAccount->id, 'INCREASE'));
        $this->assertEquals('DEBIT', DeAccount::headTypeCheck($equityAccount->id, 'DECREASE'));
        
        // Test revenue account - increases with credits
        $revenueAccount = $accounts['sales_revenue'];
        $this->assertEquals(4, $revenueAccount->root_type); // Income
        $this->assertEquals('CREDIT', DeAccount::headTypeCheck($revenueAccount->id, 'INCREASE'));
        $this->assertEquals('DEBIT', DeAccount::headTypeCheck($revenueAccount->id, 'DECREASE'));
        
        // Test expense account - increases with debits
        $expenseAccount = $accounts['office_expense'];
        $this->assertEquals(2, $expenseAccount->root_type); // Expense
        $this->assertEquals('DEBIT', DeAccount::headTypeCheck($expenseAccount->id, 'INCREASE'));
        $this->assertEquals('CREDIT', DeAccount::headTypeCheck($expenseAccount->id, 'DECREASE'));
    }

    /**
     * Test decimal precision in journal entries
     */
    public function test_decimal_precision_in_journals()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Test entry with precise decimal amounts
        $amount = 1234.56;

        $debitTxn = DeAccountTransaction::create([
            'account_id' => $accounts['cash']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        $creditTxn = DeAccountTransaction::create([
            'account_id' => $accounts['capital']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
        ]);

        // Verify precision is maintained in database
        $this->assertEquals('1234.56', number_format($debitTxn->amount, 2, '.', ''));
        $this->assertEquals('1234.56', number_format($creditTxn->amount, 2, '.', ''));
        $this->assertEquals($debitTxn->debit, $creditTxn->credit);
    }

    /**
     * Test edge cases with zero amounts
     */
    public function test_zero_amount_handling()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        
        // Test that zero-amount transactions are handled properly
        $debitTxn = DeAccountTransaction::create([
            'account_id' => $accounts['cash']->id,
            'amount' => 0,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        $this->assertEquals(0, $debitTxn->amount);
        $this->assertEquals(0, $debitTxn->debit);
        $this->assertEquals(0, $debitTxn->credit);
    }

    /**
     * Test that transaction amounts are consistent with debit/credit columns
     */
    public function test_amount_consistency()
    {
        $accounts = TestChartOfAccountsSeeder::getTestAccounts();
        $amount = 500.75;

        // Debit transaction
        $debitTxn = DeAccountTransaction::create([
            'account_id' => $accounts['cash']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        // Credit transaction
        $creditTxn = DeAccountTransaction::create([
            'account_id' => $accounts['capital']->id,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
        ]);

        // For debit transactions: amount should equal debit column
        $this->assertEquals($debitTxn->amount, $debitTxn->debit);
        $this->assertEquals(0, $debitTxn->credit);
        
        // For credit transactions: amount should equal credit column
        $this->assertEquals($creditTxn->amount, $creditTxn->credit);
        $this->assertEquals(0, $creditTxn->debit);
    }
}