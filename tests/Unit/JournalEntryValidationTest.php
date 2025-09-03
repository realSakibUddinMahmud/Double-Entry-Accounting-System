<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class JournalEntryValidationTest extends TestCase
{
    /**
     * Test that balanced journal entries pass validation
     */
    public function test_balanced_entry_validation()
    {
        $journalEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $isBalanced = $this->validateJournalEntry($journalEntry);
        
        $this->assertTrue($isBalanced, 'Balanced journal entry should pass validation');
    }

    /**
     * Test that unbalanced journal entries fail validation
     */
    public function test_unbalanced_entry_validation()
    {
        $journalEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 999.99], // Unbalanced
            ]
        ];
        
        $isBalanced = $this->validateJournalEntry($journalEntry);
        
        $this->assertFalse($isBalanced, 'Unbalanced journal entry should fail validation');
    }

    /**
     * Test compound journal entry validation
     */
    public function test_compound_entry_validation()
    {
        $compoundEntry = [
            'lines' => [
                ['account' => 'Equipment', 'debit' => 5000.00, 'credit' => 0],
                ['account' => 'Office Supplies', 'debit' => 300.00, 'credit' => 0],
                ['account' => 'Cash', 'debit' => 0, 'credit' => 2300.00],
                ['account' => 'Accounts Payable', 'debit' => 0, 'credit' => 3000.00],
            ]
        ];
        
        $isBalanced = $this->validateJournalEntry($compoundEntry);
        
        $this->assertTrue($isBalanced, 'Balanced compound entry should pass validation');
        
        // Check totals
        $validation = $this->getJournalValidationDetails($compoundEntry);
        $this->assertEquals(5300.00, $validation['total_debits']);
        $this->assertEquals(5300.00, $validation['total_credits']);
    }

    /**
     * Test journal entry with zero amounts
     */
    public function test_zero_amount_validation()
    {
        $zeroEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 0, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 0],
            ]
        ];
        
        $validation = $this->getJournalValidationDetails($zeroEntry);
        
        $this->assertTrue($validation['is_balanced'], 'Zero amount entries should be balanced');
        $this->assertEquals(0, $validation['total_debits']);
        $this->assertEquals(0, $validation['total_credits']);
    }

    /**
     * Test that entries have at least one debit and one credit
     */
    public function test_debit_credit_requirement()
    {
        // Entry with only debits (invalid)
        $debitOnlyEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Equipment', 'debit' => 500.00, 'credit' => 0],
            ]
        ];
        
        $hasDebitAndCredit = $this->hasDebitAndCredit($debitOnlyEntry);
        $this->assertFalse($hasDebitAndCredit, 'Entry with only debits should fail validation');
        
        // Entry with only credits (invalid)
        $creditOnlyEntry = [
            'lines' => [
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
                ['account' => 'Revenue', 'debit' => 0, 'credit' => 500.00],
            ]
        ];
        
        $hasDebitAndCredit = $this->hasDebitAndCredit($creditOnlyEntry);
        $this->assertFalse($hasDebitAndCredit, 'Entry with only credits should fail validation');
        
        // Entry with both debits and credits (valid)
        $validEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasDebitAndCredit = $this->hasDebitAndCredit($validEntry);
        $this->assertTrue($hasDebitAndCredit, 'Entry with both debits and credits should pass validation');
    }

    /**
     * Test decimal precision validation
     */
    public function test_decimal_precision_validation()
    {
        $preciseEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1234.567, 'credit' => 0], // Too many decimal places
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1234.567],
            ]
        ];
        
        $normalized = $this->normalizeDecimalPrecision($preciseEntry);
        
        // Should round to 2 decimal places
        $this->assertEquals(1234.57, $normalized['lines'][0]['debit']);
        $this->assertEquals(1234.57, $normalized['lines'][1]['credit']);
        
        // Verify still balanced after rounding
        $isBalanced = $this->validateJournalEntry($normalized);
        $this->assertTrue($isBalanced, 'Entry should remain balanced after decimal normalization');
    }

    /**
     * Test minimum entry requirements
     */
    public function test_minimum_entry_requirements()
    {
        // Entry with no lines
        $emptyEntry = ['lines' => []];
        $this->assertFalse($this->validateMinimumRequirements($emptyEntry), 'Empty entry should fail validation');
        
        // Entry with only one line
        $singleLineEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
            ]
        ];
        $this->assertFalse($this->validateMinimumRequirements($singleLineEntry), 'Single line entry should fail validation');
        
        // Valid entry with minimum two lines
        $validEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        $this->assertTrue($this->validateMinimumRequirements($validEntry), 'Two-line entry should pass validation');
    }

    /**
     * Test account validation requirements
     */
    public function test_account_validation()
    {
        // Entry with missing account names
        $invalidEntry = [
            'lines' => [
                ['account' => '', 'debit' => 1000.00, 'credit' => 0], // Missing account
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasValidAccounts = $this->validateAccounts($invalidEntry);
        $this->assertFalse($hasValidAccounts, 'Entry with missing account should fail validation');
        
        // Entry with valid account names
        $validEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasValidAccounts = $this->validateAccounts($validEntry);
        $this->assertTrue($hasValidAccounts, 'Entry with valid accounts should pass validation');
    }

    /**
     * Test negative amount validation
     */
    public function test_negative_amount_validation()
    {
        $negativeEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => -1000.00, 'credit' => 0], // Negative debit
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasValidAmounts = $this->validateAmounts($negativeEntry);
        $this->assertFalse($hasValidAmounts, 'Entry with negative amounts should fail validation');
        
        // Test with negative credit
        $negativeCreditEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => -1000.00], // Negative credit
            ]
        ];
        
        $hasValidAmounts = $this->validateAmounts($negativeCreditEntry);
        $this->assertFalse($hasValidAmounts, 'Entry with negative credit should fail validation');
    }

    /**
     * Test that a line cannot have both debit and credit amounts
     */
    public function test_exclusive_debit_credit_validation()
    {
        $invalidEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 500.00, 'credit' => 300.00], // Both debit and credit
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasExclusiveAmounts = $this->validateExclusiveAmounts($invalidEntry);
        $this->assertFalse($hasExclusiveAmounts, 'Line with both debit and credit should fail validation');
        
        $validEntry = [
            'lines' => [
                ['account' => 'Cash', 'debit' => 1000.00, 'credit' => 0],
                ['account' => 'Capital', 'debit' => 0, 'credit' => 1000.00],
            ]
        ];
        
        $hasExclusiveAmounts = $this->validateExclusiveAmounts($validEntry);
        $this->assertTrue($hasExclusiveAmounts, 'Lines with exclusive amounts should pass validation');
    }

    // Helper methods for validation

    private function validateJournalEntry(array $entry): bool
    {
        $validation = $this->getJournalValidationDetails($entry);
        return $validation['is_balanced'];
    }

    private function getJournalValidationDetails(array $entry): array
    {
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach ($entry['lines'] as $line) {
            $totalDebits += $line['debit'] ?? 0;
            $totalCredits += $line['credit'] ?? 0;
        }
        
        return [
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.005, // Tighter tolerance for unbalanced test
        ];
    }

    private function hasDebitAndCredit(array $entry): bool
    {
        $hasDebit = false;
        $hasCredit = false;
        
        foreach ($entry['lines'] as $line) {
            if ($line['debit'] > 0) $hasDebit = true;
            if ($line['credit'] > 0) $hasCredit = true;
        }
        
        return $hasDebit && $hasCredit;
    }

    private function normalizeDecimalPrecision(array $entry): array
    {
        foreach ($entry['lines'] as &$line) {
            $line['debit'] = round($line['debit'], 2);
            $line['credit'] = round($line['credit'], 2);
        }
        
        return $entry;
    }

    private function validateMinimumRequirements(array $entry): bool
    {
        return isset($entry['lines']) && count($entry['lines']) >= 2;
    }

    private function validateAccounts(array $entry): bool
    {
        foreach ($entry['lines'] as $line) {
            if (empty($line['account']) || !is_string($line['account'])) {
                return false;
            }
        }
        return true;
    }

    private function validateAmounts(array $entry): bool
    {
        foreach ($entry['lines'] as $line) {
            if ($line['debit'] < 0 || $line['credit'] < 0) {
                return false;
            }
        }
        return true;
    }

    private function validateExclusiveAmounts(array $entry): bool
    {
        foreach ($entry['lines'] as $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                return false; // Cannot have both debit and credit on same line
            }
        }
        return true;
    }
}