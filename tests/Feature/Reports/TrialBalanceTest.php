<?php

namespace Tests\Feature\Reports;

use PHPUnit\Framework\TestCase;

class TrialBalanceTest extends TestCase
{
    /**
     * Test trial balance report generation logic
     */
    public function test_trial_balance_report_structure()
    {
        // Mock account data for trial balance
        $accounts = [
            ['id' => 1, 'title' => 'Cash', 'root_type' => 1, 'balance' => 5000.00], // Asset - Debit balance
            ['id' => 2, 'title' => 'Accounts Receivable', 'root_type' => 1, 'balance' => 3000.00], // Asset - Debit balance
            ['id' => 3, 'title' => 'Accounts Payable', 'root_type' => 3, 'balance' => -2000.00], // Liability - Credit balance
            ['id' => 4, 'title' => 'Capital', 'root_type' => 5, 'balance' => -5000.00], // Equity - Credit balance
            ['id' => 5, 'title' => 'Sales Revenue', 'root_type' => 4, 'balance' => -3000.00], // Income - Credit balance
            ['id' => 6, 'title' => 'Rent Expense', 'root_type' => 2, 'balance' => 2000.00], // Expense - Debit balance
        ];
        
        $trialBalance = $this->generateTrialBalance($accounts);
        
        // Test structure
        $this->assertArrayHasKey('accounts', $trialBalance);
        $this->assertArrayHasKey('totals', $trialBalance);
        $this->assertArrayHasKey('is_balanced', $trialBalance);
        
        // Test totals
        $this->assertEquals(10000.00, $trialBalance['totals']['total_debits']);
        $this->assertEquals(10000.00, $trialBalance['totals']['total_credits']);
        $this->assertTrue($trialBalance['is_balanced']);
        
        // Test account count
        $this->assertCount(6, $trialBalance['accounts']);
    }

    /**
     * Test trial balance with date filtering
     */
    public function test_trial_balance_date_filtering()
    {
        $transactions = [
            ['account_id' => 1, 'date' => '2024-01-15', 'debit' => 1000, 'credit' => 0],
            ['account_id' => 2, 'date' => '2024-01-15', 'debit' => 0, 'credit' => 1000],
            ['account_id' => 1, 'date' => '2024-02-15', 'debit' => 500, 'credit' => 0],
            ['account_id' => 3, 'date' => '2024-02-15', 'debit' => 0, 'credit' => 500],
            ['account_id' => 1, 'date' => '2024-03-15', 'debit' => 300, 'credit' => 0],
            ['account_id' => 4, 'date' => '2024-03-15', 'debit' => 0, 'credit' => 300],
        ];
        
        // Test January only
        $januaryTransactions = $this->filterTransactionsByDate($transactions, '2024-01-01', '2024-01-31');
        $januaryBalance = $this->calculateTrialBalanceFromTransactions($januaryTransactions);
        
        $this->assertEquals(1000.00, $januaryBalance['total_debits']);
        $this->assertEquals(1000.00, $januaryBalance['total_credits']);
        
        // Test through February
        $throughFebTransactions = $this->filterTransactionsByDate($transactions, '2024-01-01', '2024-02-28');
        $throughFebBalance = $this->calculateTrialBalanceFromTransactions($throughFebTransactions);
        
        $this->assertEquals(1500.00, $throughFebBalance['total_debits']);
        $this->assertEquals(1500.00, $throughFebBalance['total_credits']);
        
        // Test full period
        $fullPeriodTransactions = $this->filterTransactionsByDate($transactions, '2024-01-01', '2024-12-31');
        $fullPeriodBalance = $this->calculateTrialBalanceFromTransactions($fullPeriodTransactions);
        
        $this->assertEquals(1800.00, $fullPeriodBalance['total_debits']);
        $this->assertEquals(1800.00, $fullPeriodBalance['total_credits']);
    }

    /**
     * Test trial balance grouping by account type
     */
    public function test_trial_balance_account_type_grouping()
    {
        $accounts = [
            ['title' => 'Cash', 'root_type' => 1, 'balance' => 5000.00],
            ['title' => 'Equipment', 'root_type' => 1, 'balance' => 3000.00],
            ['title' => 'Accounts Payable', 'root_type' => 3, 'balance' => -2000.00],
            ['title' => 'Notes Payable', 'root_type' => 3, 'balance' => -1000.00],
            ['title' => 'Capital', 'root_type' => 5, 'balance' => -3000.00],
            ['title' => 'Sales Revenue', 'root_type' => 4, 'balance' => -4000.00],
            ['title' => 'Rent Expense', 'root_type' => 2, 'balance' => 1500.00],
            ['title' => 'Office Expense', 'root_type' => 2, 'balance' => 500.00],
        ];
        
        $groupedBalance = $this->groupTrialBalanceByType($accounts);
        
        // Test asset group (root_type = 1)
        $this->assertEquals(8000.00, $groupedBalance['assets']['total_debit']);
        $this->assertEquals(0, $groupedBalance['assets']['total_credit']);
        $this->assertCount(2, $groupedBalance['assets']['accounts']);
        
        // Test liability group (root_type = 3)
        $this->assertEquals(0, $groupedBalance['liabilities']['total_debit']);
        $this->assertEquals(3000.00, $groupedBalance['liabilities']['total_credit']);
        $this->assertCount(2, $groupedBalance['liabilities']['accounts']);
        
        // Test equity group (root_type = 5)
        $this->assertEquals(0, $groupedBalance['equity']['total_debit']);
        $this->assertEquals(3000.00, $groupedBalance['equity']['total_credit']);
        $this->assertCount(1, $groupedBalance['equity']['accounts']);
        
        // Test income group (root_type = 4)
        $this->assertEquals(0, $groupedBalance['income']['total_debit']);
        $this->assertEquals(4000.00, $groupedBalance['income']['total_credit']);
        $this->assertCount(1, $groupedBalance['income']['accounts']);
        
        // Test expense group (root_type = 2)
        $this->assertEquals(2000.00, $groupedBalance['expenses']['total_debit']);
        $this->assertEquals(0, $groupedBalance['expenses']['total_credit']);
        $this->assertCount(2, $groupedBalance['expenses']['accounts']);
    }

    /**
     * Test trial balance zero balance filtering
     */
    public function test_trial_balance_zero_balance_filtering()
    {
        $accounts = [
            ['title' => 'Cash', 'balance' => 5000.00],
            ['title' => 'Unused Account', 'balance' => 0], // Zero balance
            ['title' => 'Capital', 'balance' => -5000.00],
            ['title' => 'Another Unused', 'balance' => 0], // Zero balance
        ];
        
        // Include zero balances
        $withZeros = $this->filterTrialBalance($accounts, true);
        $this->assertCount(4, $withZeros);
        
        // Exclude zero balances  
        $withoutZeros = $this->filterTrialBalance($accounts, false);
        $this->assertCount(2, $withoutZeros);
        
        // Verify the filtered accounts are correct
        $accountTitles = array_column($withoutZeros, 'title');
        $this->assertContains('Cash', $accountTitles);
        $this->assertContains('Capital', $accountTitles);
        $this->assertNotContains('Unused Account', $accountTitles);
        $this->assertNotContains('Another Unused', $accountTitles);
    }

    /**
     * Test trial balance precision and rounding
     */
    public function test_trial_balance_precision()
    {
        $accounts = [
            ['title' => 'Cash', 'balance' => 1234.567], // More than 2 decimal places
            ['title' => 'Capital', 'balance' => -1234.563], // Different rounding
        ];
        
        $trialBalance = $this->generateTrialBalance($accounts);
        
        // Should round to 2 decimal places
        $cashAccount = $this->findAccountByTitle($trialBalance['accounts'], 'Cash');
        $capitalAccount = $this->findAccountByTitle($trialBalance['accounts'], 'Capital');
        
        $this->assertEquals(1234.57, $cashAccount['debit_balance']);
        $this->assertEquals(0, $cashAccount['credit_balance']);
        $this->assertEquals(0, $capitalAccount['debit_balance']);
        $this->assertEquals(1234.56, $capitalAccount['credit_balance']);
        
        // Check if still balanced after rounding
        $this->assertEquals(1234.57, $trialBalance['totals']['total_debits']);
        $this->assertEquals(1234.56, $trialBalance['totals']['total_credits']);
        
        $this->assertFalse($trialBalance['is_balanced']);
        $this->assertEquals(0.01, $trialBalance['totals']['difference']);
    }

    /**
     * Test trial balance report formatting
     */
    public function test_trial_balance_formatting()
    {
        $accounts = [
            ['title' => 'Cash', 'root_type' => 1, 'balance' => 5000.00],
            ['title' => 'Capital', 'root_type' => 5, 'balance' => -5000.00],
        ];
        
        $formattedReport = $this->formatTrialBalanceReport($accounts);
        
        $this->assertArrayHasKey('title', $formattedReport);
        $this->assertArrayHasKey('date_range', $formattedReport);
        $this->assertArrayHasKey('headers', $formattedReport);
        $this->assertArrayHasKey('rows', $formattedReport);
        $this->assertArrayHasKey('totals', $formattedReport);
        
        // Test headers
        $expectedHeaders = ['Account', 'Debit', 'Credit'];
        $this->assertEquals($expectedHeaders, $formattedReport['headers']);
        
        // Test row formatting
        $this->assertCount(2, $formattedReport['rows']);
        
        $cashRow = $formattedReport['rows'][0];
        $this->assertEquals('Cash', $cashRow['account']);
        $this->assertEquals('5,000.00', $cashRow['debit']);
        $this->assertEquals('-', $cashRow['credit']);
        
        $capitalRow = $formattedReport['rows'][1];
        $this->assertEquals('Capital', $capitalRow['account']);
        $this->assertEquals('-', $capitalRow['debit']);
        $this->assertEquals('5,000.00', $capitalRow['credit']);
        
        // Test total formatting
        $this->assertEquals('5,000.00', $formattedReport['totals']['total_debits']);
        $this->assertEquals('5,000.00', $formattedReport['totals']['total_credits']);
    }

    /**
     * Test trial balance error detection
     */
    public function test_trial_balance_error_detection()
    {
        // Unbalanced trial balance
        $unbalancedAccounts = [
            ['title' => 'Cash', 'balance' => 5000.00],
            ['title' => 'Capital', 'balance' => -4999.99], // Off by $0.01
        ];
        
        $unbalancedReport = $this->generateTrialBalance($unbalancedAccounts);
        
        $this->assertFalse($unbalancedReport['is_balanced']);
        $this->assertEquals(0.01, round($unbalancedReport['totals']['difference'], 2));
        $this->assertArrayHasKey('errors', $unbalancedReport);
        $this->assertContains('Trial balance does not balance', $unbalancedReport['errors']);
        
        // Large imbalance
        $largeImbalanceAccounts = [
            ['title' => 'Cash', 'balance' => 10000.00],
            ['title' => 'Capital', 'balance' => -9000.00], // Off by $1000
        ];
        
        $largeImbalanceReport = $this->generateTrialBalance($largeImbalanceAccounts);
        $this->assertEquals(1000.00, $largeImbalanceReport['totals']['difference']);
        $this->assertContains('Significant imbalance detected', $largeImbalanceReport['errors']);
    }

    // Helper methods

    private function generateTrialBalance(array $accounts): array
    {
        $trialBalance = [
            'accounts' => [],
            'totals' => ['total_debits' => 0, 'total_credits' => 0, 'difference' => 0],
            'is_balanced' => true,
            'errors' => []
        ];
        
        foreach ($accounts as $account) {
            $debitBalance = $account['balance'] > 0 ? $account['balance'] : 0;
            $creditBalance = $account['balance'] < 0 ? abs($account['balance']) : 0;
            
            // Round to 2 decimal places
            $debitBalance = round($debitBalance, 2);
            $creditBalance = round($creditBalance, 2);
            
            $trialBalance['accounts'][] = [
                'title' => $account['title'],
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance,
            ];
            
            $trialBalance['totals']['total_debits'] += $debitBalance;
            $trialBalance['totals']['total_credits'] += $creditBalance;
        }
        
        $trialBalance['totals']['difference'] = round(abs($trialBalance['totals']['total_debits'] - $trialBalance['totals']['total_credits']), 2);
        $trialBalance['is_balanced'] = $trialBalance['totals']['difference'] < 0.005;
        
        if (!$trialBalance['is_balanced']) {
            $trialBalance['errors'][] = 'Trial balance does not balance';
            if ($trialBalance['totals']['difference'] > 100) {
                $trialBalance['errors'][] = 'Significant imbalance detected';
            }
        }
        
        return $trialBalance;
    }

    private function filterTransactionsByDate(array $transactions, string $startDate, string $endDate): array
    {
        return array_filter($transactions, function($transaction) use ($startDate, $endDate) {
            return $transaction['date'] >= $startDate && $transaction['date'] <= $endDate;
        });
    }

    private function calculateTrialBalanceFromTransactions(array $transactions): array
    {
        $totals = ['total_debits' => 0, 'total_credits' => 0];
        
        foreach ($transactions as $transaction) {
            $totals['total_debits'] += $transaction['debit'];
            $totals['total_credits'] += $transaction['credit'];
        }
        
        return $totals;
    }

    private function groupTrialBalanceByType(array $accounts): array
    {
        $groups = [
            'assets' => ['total_debit' => 0, 'total_credit' => 0, 'accounts' => []],
            'liabilities' => ['total_debit' => 0, 'total_credit' => 0, 'accounts' => []],
            'equity' => ['total_debit' => 0, 'total_credit' => 0, 'accounts' => []],
            'income' => ['total_debit' => 0, 'total_credit' => 0, 'accounts' => []],
            'expenses' => ['total_debit' => 0, 'total_credit' => 0, 'accounts' => []],
        ];
        
        foreach ($accounts as $account) {
            $debitBalance = $account['balance'] > 0 ? $account['balance'] : 0;
            $creditBalance = $account['balance'] < 0 ? abs($account['balance']) : 0;
            
            switch ($account['root_type']) {
                case 1: // Assets
                    $groups['assets']['total_debit'] += $debitBalance;
                    $groups['assets']['total_credit'] += $creditBalance;
                    $groups['assets']['accounts'][] = $account;
                    break;
                case 2: // Expenses
                    $groups['expenses']['total_debit'] += $debitBalance;
                    $groups['expenses']['total_credit'] += $creditBalance;
                    $groups['expenses']['accounts'][] = $account;
                    break;
                case 3: // Liabilities
                    $groups['liabilities']['total_debit'] += $debitBalance;
                    $groups['liabilities']['total_credit'] += $creditBalance;
                    $groups['liabilities']['accounts'][] = $account;
                    break;
                case 4: // Income
                    $groups['income']['total_debit'] += $debitBalance;
                    $groups['income']['total_credit'] += $creditBalance;
                    $groups['income']['accounts'][] = $account;
                    break;
                case 5: // Equity
                    $groups['equity']['total_debit'] += $debitBalance;
                    $groups['equity']['total_credit'] += $creditBalance;
                    $groups['equity']['accounts'][] = $account;
                    break;
            }
        }
        
        return $groups;
    }

    private function filterTrialBalance(array $accounts, bool $includeZeroBalances): array
    {
        if ($includeZeroBalances) {
            return $accounts;
        }
        
        return array_filter($accounts, function($account) {
            return abs($account['balance']) > 0.01; // Exclude accounts with zero or near-zero balances
        });
    }

    private function findAccountByTitle(array $accounts, string $title): ?array
    {
        foreach ($accounts as $account) {
            if ($account['title'] === $title) {
                return $account;
            }
        }
        return null;
    }

    private function formatTrialBalanceReport(array $accounts): array
    {
        $trialBalance = $this->generateTrialBalance($accounts);
        
        return [
            'title' => 'Trial Balance',
            'date_range' => 'As of ' . date('Y-m-d'),
            'headers' => ['Account', 'Debit', 'Credit'],
            'rows' => array_map(function($account) {
                return [
                    'account' => $account['title'],
                    'debit' => $account['debit_balance'] > 0 ? number_format($account['debit_balance'], 2) : '-',
                    'credit' => $account['credit_balance'] > 0 ? number_format($account['credit_balance'], 2) : '-',
                ];
            }, $trialBalance['accounts']),
            'totals' => [
                'total_debits' => number_format($trialBalance['totals']['total_debits'], 2),
                'total_credits' => number_format($trialBalance['totals']['total_credits'], 2),
            ]
        ];
    }
}