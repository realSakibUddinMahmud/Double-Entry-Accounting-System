<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeJournal;

class PostEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic accounts directly using DB
        DB::table('accounts')->insert([
            [
                'id' => 1,
                'account_no' => 1000,
                'title' => 'Cash',
                'root_type' => 1, // Asset
                'accountable_type' => 1,
                'accountable_id' => 1,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 1,
                '_rgt' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'account_no' => 3000,
                'title' => 'Capital',
                'root_type' => 5, // Equity
                'accountable_type' => 1,
                'accountable_id' => 1,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 1,
                '_rgt' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_balanced_entry_creation()
    {
        $amount = 1000.00;
        
        // Create debit transaction (Cash)
        $debitTransaction = DeAccountTransaction::create([
            'account_id' => 1, // Cash account
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
            'note' => 'Test entry',
        ]);

        // Create credit transaction (Capital)
        $creditTransaction = DeAccountTransaction::create([
            'account_id' => 2, // Capital account
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
            'note' => 'Test entry',
        ]);

        // Create journal entry linking both transactions
        $journal = DeJournal::create([
            'date' => now()->format('Y-m-d'),
            'amount' => $amount,
            'debit_transaction_id' => $debitTransaction->id,
            'credit_transaction_id' => $creditTransaction->id,
            'transaction_type' => 'capital_investment',
            'created_by' => 1,
            'note' => 'Capital investment test',
        ]);

        // Assertions
        $this->assertNotNull($journal->id);
        $this->assertEquals($amount, $journal->amount);
        $this->assertEquals($debitTransaction->id, $journal->debit_transaction_id);
        $this->assertEquals($creditTransaction->id, $journal->credit_transaction_id);
        
        // Verify that the entry is balanced
        $this->assertEquals($debitTransaction->amount, $creditTransaction->amount);
        $this->assertEquals($debitTransaction->debit, $creditTransaction->credit);
        $this->assertEquals($amount, $debitTransaction->debit);
        $this->assertEquals($amount, $creditTransaction->credit);
    }

    public function test_transaction_amounts_match_debit_credit_columns()
    {
        $amount = 500.75;
        
        // Test debit transaction
        $debitTransaction = DeAccountTransaction::create([
            'account_id' => 1,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);
        
        // Test credit transaction
        $creditTransaction = DeAccountTransaction::create([
            'account_id' => 2,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
        ]);

        // Verify debit transaction
        $this->assertEquals($amount, $debitTransaction->amount);
        $this->assertEquals($amount, $debitTransaction->debit);
        $this->assertEquals(0, $debitTransaction->credit);
        $this->assertEquals('DEBIT', $debitTransaction->type);
        
        // Verify credit transaction
        $this->assertEquals($amount, $creditTransaction->amount);
        $this->assertEquals(0, $creditTransaction->debit);
        $this->assertEquals($amount, $creditTransaction->credit);
        $this->assertEquals('CREDIT', $creditTransaction->type);
    }

    public function test_decimal_precision_maintained()
    {
        $amount = 1234.56;
        
        $transaction = DeAccountTransaction::create([
            'account_id' => 1,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        // Verify precision is maintained
        $this->assertEquals(1234.56, $transaction->amount);
        $this->assertEquals('1234.56', number_format($transaction->amount, 2, '.', ''));
    }

    public function test_journal_entry_links_transactions()
    {
        $amount = 2500.00;
        
        $debitTxn = DeAccountTransaction::create([
            'account_id' => 1,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
            'created_by' => 1,
        ]);

        $creditTxn = DeAccountTransaction::create([
            'account_id' => 2,
            'amount' => $amount,
            'date' => now()->format('Y-m-d'),
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
            'created_by' => 1,
        ]);

        $journal = DeJournal::create([
            'date' => now()->format('Y-m-d'),
            'amount' => $amount,
            'debit_transaction_id' => $debitTxn->id,
            'credit_transaction_id' => $creditTxn->id,
            'transaction_type' => 'test',
            'created_by' => 1,
        ]);

        // Test relationships
        $this->assertEquals($debitTxn->id, $journal->debit_transaction_id);
        $this->assertEquals($creditTxn->id, $journal->credit_transaction_id);
        
        // Test the actual relationship methods
        $this->assertNotNull($journal->debitTransaction);
        $this->assertNotNull($journal->creditTransaction);
        $this->assertEquals($debitTxn->id, $journal->debitTransaction->id);
        $this->assertEquals($creditTxn->id, $journal->creditTransaction->id);
    }

    public function test_multiple_journal_entries_balance()
    {
        $entries = [
            ['debit_account' => 1, 'credit_account' => 2, 'amount' => 1000.00],
            ['debit_account' => 1, 'credit_account' => 2, 'amount' => 500.50],
            ['debit_account' => 1, 'credit_account' => 2, 'amount' => 250.25],
        ];

        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($entries as $entry) {
            $debitTxn = DeAccountTransaction::create([
                'account_id' => $entry['debit_account'],
                'amount' => $entry['amount'],
                'date' => now()->format('Y-m-d'),
                'debit' => $entry['amount'],
                'credit' => 0,
                'type' => 'DEBIT',
                'created_by' => 1,
            ]);

            $creditTxn = DeAccountTransaction::create([
                'account_id' => $entry['credit_account'],
                'amount' => $entry['amount'],
                'date' => now()->format('Y-m-d'),
                'debit' => 0,
                'credit' => $entry['amount'],
                'type' => 'CREDIT',
                'created_by' => 1,
            ]);

            $totalDebits += $debitTxn->debit;
            $totalCredits += $creditTxn->credit;

            DeJournal::create([
                'date' => now()->format('Y-m-d'),
                'amount' => $entry['amount'],
                'debit_transaction_id' => $debitTxn->id,
                'credit_transaction_id' => $creditTxn->id,
                'transaction_type' => 'multiple_test',
                'created_by' => 1,
            ]);
        }

        // Verify all entries balance
        $this->assertEquals(1750.75, $totalDebits);
        $this->assertEquals(1750.75, $totalCredits);
        $this->assertEquals($totalDebits, $totalCredits);

        // Verify we have 3 journal entries
        $journalCount = DeJournal::where('transaction_type', 'multiple_test')->count();
        $this->assertEquals(3, $journalCount);
    }
}