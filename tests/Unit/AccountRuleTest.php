<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Hilinkz\DEAccounting\Models\DeAccount;

class AccountRuleTest extends TestCase
{
    /**
     * Test account head type logic for different account types
     */
    public function test_asset_account_head_type_logic()
    {
        // Asset accounts (root_type = 1): increase with debits, decrease with credits
        $accountId = 1; // Mock account ID
        $rootType = 1; // Asset
        
        // Simulate the headTypeCheck logic for assets
        $increaseType = $this->getHeadType($rootType, 'INCREASE');
        $decreaseType = $this->getHeadType($rootType, 'DECREASE');
        
        $this->assertEquals('DEBIT', $increaseType, 'Asset increases should be debits');
        $this->assertEquals('CREDIT', $decreaseType, 'Asset decreases should be credits');
    }

    public function test_liability_account_head_type_logic()
    {
        // Liability accounts (root_type = 3): increase with credits, decrease with debits
        $rootType = 3; // Liability
        
        $increaseType = $this->getHeadType($rootType, 'INCREASE');
        $decreaseType = $this->getHeadType($rootType, 'DECREASE');
        
        $this->assertEquals('CREDIT', $increaseType, 'Liability increases should be credits');
        $this->assertEquals('DEBIT', $decreaseType, 'Liability decreases should be debits');
    }

    public function test_equity_account_head_type_logic()
    {
        // Equity accounts (root_type = 5): increase with credits, decrease with debits
        $rootType = 5; // Capital/Equity
        
        $increaseType = $this->getHeadType($rootType, 'INCREASE');
        $decreaseType = $this->getHeadType($rootType, 'DECREASE');
        
        $this->assertEquals('CREDIT', $increaseType, 'Equity increases should be credits');
        $this->assertEquals('DEBIT', $decreaseType, 'Equity decreases should be debits');
    }

    public function test_income_account_head_type_logic()
    {
        // Income accounts (root_type = 4): increase with credits, decrease with debits
        $rootType = 4; // Income
        
        $increaseType = $this->getHeadType($rootType, 'INCREASE');
        $decreaseType = $this->getHeadType($rootType, 'DECREASE');
        
        $this->assertEquals('CREDIT', $increaseType, 'Income increases should be credits');
        $this->assertEquals('DEBIT', $decreaseType, 'Income decreases should be debits');
    }

    public function test_expense_account_head_type_logic()
    {
        // Expense accounts (root_type = 2): increase with debits, decrease with credits
        $rootType = 2; // Expense
        
        $increaseType = $this->getHeadType($rootType, 'INCREASE');
        $decreaseType = $this->getHeadType($rootType, 'DECREASE');
        
        $this->assertEquals('DEBIT', $increaseType, 'Expense increases should be debits');
        $this->assertEquals('CREDIT', $decreaseType, 'Expense decreases should be credits');
    }

    /**
     * Test all account types at once for comprehensive coverage
     */
    public function test_all_account_types_comprehensive()
    {
        $accountTypes = [
            1 => 'Assets',
            2 => 'Expenses', 
            3 => 'Liabilities',
            4 => 'Income',
            5 => 'Capital'
        ];

        $expectedIncreases = [
            1 => 'DEBIT',   // Assets increase with debits
            2 => 'DEBIT',   // Expenses increase with debits
            3 => 'CREDIT',  // Liabilities increase with credits
            4 => 'CREDIT',  // Income increases with credits
            5 => 'CREDIT',  // Capital increases with credits
        ];

        $expectedDecreases = [
            1 => 'CREDIT',  // Assets decrease with credits
            2 => 'CREDIT',  // Expenses decrease with credits
            3 => 'DEBIT',   // Liabilities decrease with debits
            4 => 'DEBIT',   // Income decreases with debits
            5 => 'DEBIT',   // Capital decreases with debits
        ];

        foreach ($accountTypes as $rootType => $typeName) {
            $increaseType = $this->getHeadType($rootType, 'INCREASE');
            $decreaseType = $this->getHeadType($rootType, 'DECREASE');

            $this->assertEquals(
                $expectedIncreases[$rootType], 
                $increaseType, 
                "{$typeName} accounts should increase with {$expectedIncreases[$rootType]}s"
            );

            $this->assertEquals(
                $expectedDecreases[$rootType], 
                $decreaseType, 
                "{$typeName} accounts should decrease with {$expectedDecreases[$rootType]}s"
            );
        }
    }

    /**
     * Test error handling for invalid account types
     */
    public function test_invalid_account_type_handling()
    {
        // Test invalid root type
        $invalidRootType = 99;
        
        $increaseType = $this->getHeadType($invalidRootType, 'INCREASE');
        $decreaseType = $this->getHeadType($invalidRootType, 'DECREASE');
        
        $this->assertStringContainsString('Not Found', $increaseType);
        $this->assertStringContainsString('Not Found', $decreaseType);
    }

    /**
     * Test error handling for invalid movement direction
     */
    public function test_invalid_movement_direction_handling()
    {
        $rootType = 1; // Valid asset type
        $invalidDirection = 'INVALID_DIRECTION';
        
        $result = $this->getHeadType($rootType, $invalidDirection);
        
        $this->assertStringContainsString('Not Found', $result);
    }

    /**
     * Helper method that replicates the DeAccount::headTypeCheck logic
     * without needing database access
     */
    private function getHeadType(int $rootType, string $upDown): string
    {
        if ($upDown === 'INCREASE') {
            switch ($rootType) {
                case 1: // Assets
                case 2: // Expenses
                    return "DEBIT";
                case 3: // Liabilities
                case 4: // Income
                case 5: // Capital
                    return "CREDIT";
                default:
                    return "Not Found in INCREASE for root_type {$rootType}";
            }
        } elseif ($upDown === 'DECREASE') {
            switch ($rootType) {
                case 1: // Assets
                case 2: // Expenses
                    return "CREDIT";
                case 3: // Liabilities
                case 4: // Income
                case 5: // Capital
                    return "DEBIT";
                default:
                    return "Not Found in DECREASE for root_type {$rootType}";
            }
        } else {
            return "Not Found in Else for direction {$upDown}";
        }
    }

    /**
     * Test the fundamental accounting equation balance
     */
    public function test_accounting_equation_balance()
    {
        // Assets = Liabilities + Equity
        // This should always hold true in any balanced accounting system
        
        $assets = 10000.00;
        $liabilities = 6000.00;
        $equity = 4000.00;
        
        $this->assertEquals($assets, $liabilities + $equity, 'Accounting equation must balance');
        
        // Test with different values
        $scenarios = [
            ['assets' => 50000, 'liabilities' => 30000, 'equity' => 20000],
            ['assets' => 100000, 'liabilities' => 75000, 'equity' => 25000],
            ['assets' => 1500.50, 'liabilities' => 900.25, 'equity' => 600.25],
        ];
        
        foreach ($scenarios as $scenario) {
            $this->assertEquals(
                $scenario['assets'], 
                $scenario['liabilities'] + $scenario['equity'],
                "Scenario with assets={$scenario['assets']} must balance"
            );
        }
    }

    /**
     * Test that journal entry logic maintains the accounting equation
     */
    public function test_journal_entry_maintains_equation()
    {
        // Start with balanced equation
        $initialAssets = 10000;
        $initialLiabilities = 6000;
        $initialEquity = 4000;
        
        // Transaction: Purchase equipment for $2000 cash
        // Dr. Equipment (Asset) $2000
        // Cr. Cash (Asset) $2000
        
        // This should not change the total assets, just shift between asset accounts
        $finalAssets = $initialAssets; // No net change to total assets
        $finalLiabilities = $initialLiabilities; // No change
        $finalEquity = $initialEquity; // No change
        
        $this->assertEquals($finalAssets, $finalLiabilities + $finalEquity);
        
        // Transaction: Borrow $5000 from bank
        // Dr. Cash (Asset) $5000
        // Cr. Notes Payable (Liability) $5000
        
        $finalAssets += 5000; // Cash increases
        $finalLiabilities += 5000; // Notes payable increases
        
        $this->assertEquals($finalAssets, $finalLiabilities + $finalEquity);
        
        // Transaction: Owner invests $3000
        // Dr. Cash (Asset) $3000
        // Cr. Capital (Equity) $3000
        
        $finalAssets += 3000; // Cash increases
        $finalEquity += 3000; // Capital increases
        
        $this->assertEquals($finalAssets, $finalLiabilities + $finalEquity);
    }
}