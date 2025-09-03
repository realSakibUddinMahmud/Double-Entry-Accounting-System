<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TrialBalanceTest extends TestCase
{
    /**
     * Test that a simple trial balance nets to zero
     */
    public function test_simple_trial_balance_nets_zero()
    {
        // Create a simple set of transactions
        $transactions = [
            ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
        ];
        
        $totalDebits = array_sum(array_column($transactions, 'debit'));
        $totalCredits = array_sum(array_column($transactions, 'credit'));
        
        $this->assertEquals(1000.00, $totalDebits);
        $this->assertEquals(1000.00, $totalCredits);
        $this->assertEquals($totalDebits, $totalCredits, 'Trial balance must net to zero');
    }

    /**
     * Test complex trial balance with multiple transactions
     */
    public function test_complex_trial_balance()
    {
        $transactions = [
            // Initial capital investment
            ['account' => 'Cash', 'debit' => 10000.00, 'credit' => 0],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 10000.00],
            
            // Purchase equipment
            ['account' => 'Equipment', 'debit' => 5000.00, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 5000.00],
            
            // Purchase supplies on credit
            ['account' => 'Office Supplies', 'debit' => 500.00, 'credit' => 0],
            ['account' => 'Accounts Payable', 'debit' => 0, 'credit' => 500.00],
            
            // Make a sale
            ['account' => 'Cash', 'debit' => 2000.00, 'credit' => 0],
            ['account' => 'Sales Revenue', 'debit' => 0, 'credit' => 2000.00],
            
            // Pay rent
            ['account' => 'Rent Expense', 'debit' => 800.00, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 800.00],
        ];
        
        $totalDebits = array_sum(array_column($transactions, 'debit'));
        $totalCredits = array_sum(array_column($transactions, 'credit'));
        
        $this->assertEquals($totalDebits, $totalCredits, 'Complex trial balance must net to zero');
        $this->assertEquals(18300.00, $totalDebits);
        $this->assertEquals(18300.00, $totalCredits);
    }

    /**
     * Test trial balance by account grouping
     */
    public function test_trial_balance_by_account()
    {
        $transactions = [
            ['account' => 'Cash', 'debit' => 10000.00, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 2000.00, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 5000.00],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 800.00],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 10000.00],
            ['account' => 'Equipment', 'debit' => 5000.00, 'credit' => 0],
            ['account' => 'Sales Revenue', 'debit' => 0, 'credit' => 2000.00],
            ['account' => 'Rent Expense', 'debit' => 800.00, 'credit' => 0],
        ];
        
        // Group by account
        $accountBalances = $this->calculateAccountBalances($transactions);
        
        // Expected balances
        $expected = [
            'Cash' => 6200.00,           // 10000 + 2000 - 5000 - 800 = 6200 (Debit balance)
            'Capital' => -10000.00,      // Credit balance of 10000
            'Equipment' => 5000.00,      // Debit balance
            'Sales Revenue' => -2000.00, // Credit balance
            'Rent Expense' => 800.00,    // Debit balance
        ];
        
        foreach ($expected as $account => $expectedBalance) {
            $this->assertEquals(
                $expectedBalance, 
                $accountBalances[$account], 
                "Account {$account} balance should be {$expectedBalance}"
            );
        }
        
        // Total of all balances should be zero
        $totalBalance = array_sum($accountBalances);
        $this->assertEquals(0, $totalBalance, 'Sum of all account balances must be zero');
    }

    /**
     * Test trial balance with decimal precision
     */
    public function test_trial_balance_decimal_precision()
    {
        $transactions = [
            ['account' => 'Cash', 'debit' => 1234.56, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 567.89],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 1234.56],
            ['account' => 'Expense', 'debit' => 567.89, 'credit' => 0],
        ];
        
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach ($transactions as $transaction) {
            $totalDebits = bcadd((string)$totalDebits, (string)$transaction['debit'], 2);
            $totalCredits = bcadd((string)$totalCredits, (string)$transaction['credit'], 2);
        }
        
        $this->assertEquals('1802.45', $totalDebits);
        $this->assertEquals('1802.45', $totalCredits);
        $this->assertEquals(0, bccomp($totalDebits, $totalCredits, 2), 'Precise trial balance must equal');
    }

    /**
     * Test unbalanced trial balance detection
     */
    public function test_unbalanced_trial_balance_detection()
    {
        $unbalancedTransactions = [
            ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 999.99], // Intentionally unbalanced
        ];
        
        $totalDebits = array_sum(array_column($unbalancedTransactions, 'debit'));
        $totalCredits = array_sum(array_column($unbalancedTransactions, 'credit'));
        
        $this->assertNotEquals($totalDebits, $totalCredits, 'Should detect unbalanced transactions');
        $this->assertEquals(0.01, round(abs($totalDebits - $totalCredits), 2), 'Imbalance should be 0.01');
    }

    /**
     * Test trial balance with large dataset
     */
    public function test_trial_balance_large_dataset()
    {
        $transactions = [];
        
        // Create 1000 balanced transactions
        for ($i = 1; $i <= 1000; $i++) {
            $amount = mt_rand(100, 10000) / 100; // Random amount between $1.00 and $100.00
            
            $transactions[] = ['account' => "Account_A_{$i}", 'debit' => $amount, 'credit' => 0];
            $transactions[] = ['account' => "Account_B_{$i}", 'debit' => 0, 'credit' => $amount];
        }
        
        $totalDebits = array_sum(array_column($transactions, 'debit'));
        $totalCredits = array_sum(array_column($transactions, 'credit'));
        
        $this->assertEquals($totalDebits, $totalCredits, 'Large dataset trial balance must equal');
        $this->assertCount(2000, $transactions, 'Should have 2000 transaction lines');
    }

    /**
     * Test trial balance filtering by date range
     */
    public function test_trial_balance_date_filtering()
    {
        $transactionsWithDates = [
            ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0, 'date' => '2024-01-01'],
            ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00, 'date' => '2024-01-01'],
            ['account' => 'Cash', 'debit' => 500.00, 'credit' => 0, 'date' => '2024-02-01'],
            ['account' => 'Revenue', 'debit' => 0, 'credit' => 500.00, 'date' => '2024-02-01'],
            ['account' => 'Expense', 'debit' => 200.00, 'credit' => 0, 'date' => '2024-03-01'],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 200.00, 'date' => '2024-03-01'],
        ];
        
        // Filter January transactions
        $januaryTransactions = array_filter($transactionsWithDates, function($t) {
            return substr($t['date'], 0, 7) === '2024-01';
        });
        
        $januaryDebits = array_sum(array_column($januaryTransactions, 'debit'));
        $januaryCredits = array_sum(array_column($januaryTransactions, 'credit'));
        
        $this->assertEquals(1000.00, $januaryDebits);
        $this->assertEquals(1000.00, $januaryCredits);
        $this->assertEquals($januaryDebits, $januaryCredits, 'January trial balance must equal');
        
        // Filter all transactions through February
        $throughFebruaryTransactions = array_filter($transactionsWithDates, function($t) {
            return $t['date'] <= '2024-02-28';
        });
        
        $throughFebDebits = array_sum(array_column($throughFebruaryTransactions, 'debit'));
        $throughFebCredits = array_sum(array_column($throughFebruaryTransactions, 'credit'));
        
        $this->assertEquals(1500.00, $throughFebDebits);
        $this->assertEquals(1500.00, $throughFebCredits);
        $this->assertEquals($throughFebDebits, $throughFebCredits, 'Through February trial balance must equal');
    }

    /**
     * Helper method to calculate account balances
     * Positive = Debit balance, Negative = Credit balance
     */
    private function calculateAccountBalances(array $transactions): array
    {
        $balances = [];
        
        foreach ($transactions as $transaction) {
            $account = $transaction['account'];
            $debit = $transaction['debit'];
            $credit = $transaction['credit'];
            
            if (!isset($balances[$account])) {
                $balances[$account] = 0;
            }
            
            $balances[$account] += ($debit - $credit);
        }
        
        return $balances;
    }

    /**
     * Test that closing entries maintain trial balance
     */
    public function test_closing_entries_maintain_balance()
    {
        // Revenue and expense accounts before closing
        $preClosingTransactions = [
            ['account' => 'Sales Revenue', 'debit' => 0, 'credit' => 5000.00],
            ['account' => 'Rent Expense', 'debit' => 1200.00, 'credit' => 0],
            ['account' => 'Salary Expense', 'debit' => 2800.00, 'credit' => 0],
            // Other balancing entries
            ['account' => 'Cash', 'debit' => 5000.00, 'credit' => 0],
            ['account' => 'Cash', 'debit' => 0, 'credit' => 4000.00],
        ];
        
        // Closing entries
        $closingEntries = [
            // Close revenue to income summary
            ['account' => 'Sales Revenue', 'debit' => 5000.00, 'credit' => 0],
            ['account' => 'Income Summary', 'debit' => 0, 'credit' => 5000.00],
            // Close expenses to income summary
            ['account' => 'Income Summary', 'debit' => 4000.00, 'credit' => 0], // 1200 + 2800 = 4000
            ['account' => 'Rent Expense', 'debit' => 0, 'credit' => 1200.00],
            ['account' => 'Salary Expense', 'debit' => 0, 'credit' => 2800.00],
            // Close income summary to retained earnings (net income = 5000 - 4000 = 1000)
            ['account' => 'Income Summary', 'debit' => 1000.00, 'credit' => 0], 
            ['account' => 'Retained Earnings', 'debit' => 0, 'credit' => 1000.00],
        ];
        
        $allTransactions = array_merge($preClosingTransactions, $closingEntries);
        
        $totalDebits = array_sum(array_column($allTransactions, 'debit'));
        $totalCredits = array_sum(array_column($allTransactions, 'credit'));
        
        $this->assertEquals($totalDebits, $totalCredits, 'Trial balance must remain balanced after closing entries');
    }
}