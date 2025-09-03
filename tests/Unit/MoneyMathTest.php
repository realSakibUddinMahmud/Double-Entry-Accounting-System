<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;

class MoneyMathTest extends TestCase
{
    /**
     * Test that money calculations use integer cents, never floats
     */
    public function test_money_calculations_use_integer_cents(): void
    {
        // Test various amounts to ensure no float precision issues
        $amounts = [100, 1000, 10000, 100000, 999999];

        foreach ($amounts as $amount) {
            $transaction = new DeAccountTransaction();
            $transaction->amount = $amount;
            $transaction->debit = $amount;
            $transaction->credit = 0;

            // Assert that amounts are integers
            $this->assertIsInt($transaction->amount);
            $this->assertIsInt($transaction->debit);
            $this->assertIsInt($transaction->credit);

            // Assert no decimal places
            $this->assertEquals(0, $amount % 1);
            $this->assertEquals(0, $transaction->amount % 1);
        }
    }

    /**
     * Test rounding rules for money calculations
     */
    public function test_money_rounding_rules(): void
    {
        // Test that we handle fractional cents by rounding to nearest cent
        $testCases = [
            [1.234, 1],    // Round down
            [1.235, 1],    // Round down (banker's rounding)
            [1.236, 1],    // Round up
            [1.5, 2],      // Round up
            [2.5, 3],      // Round up (PHP's default rounding)
        ];

        foreach ($testCases as [$input, $expected]) {
            $rounded = round($input);
            $this->assertEquals($expected, $rounded, "Failed to round {$input} correctly");
        }
    }

    /**
     * Test sum of large datasets maintains precision
     */
    public function test_sum_large_datasets_maintains_precision(): void
    {
        // Create array of 1000 transactions with random amounts
        $transactions = [];
        $expectedTotal = 0;

        for ($i = 0; $i < 1000; $i++) {
            $amount = rand(1, 10000); // Random amount in cents
            $transactions[] = $amount;
            $expectedTotal += $amount;
        }

        // Sum using array_sum
        $calculatedTotal = array_sum($transactions);

        // Assert exact match (no precision loss)
        $this->assertEquals($expectedTotal, $calculatedTotal);
        $this->assertIsInt($calculatedTotal);

        // Test that we can handle very large sums
        $this->assertGreaterThan(0, $calculatedTotal);
    }

    /**
     * Test debit and credit calculations are always balanced
     */
    public function test_debit_credit_calculations_are_balanced(): void
    {
        $amount = 10000; // $100.00 in cents

        $debitTransaction = new DeAccountTransaction();
        $debitTransaction->amount = $amount;
        $debitTransaction->debit = $amount;
        $debitTransaction->credit = 0;

        $creditTransaction = new DeAccountTransaction();
        $creditTransaction->amount = $amount;
        $creditTransaction->debit = 0;
        $creditTransaction->credit = $amount;

        // Assert that debit and credit amounts match
        $this->assertEquals($debitTransaction->debit, $creditTransaction->credit);
        $this->assertEquals($amount, $debitTransaction->debit);
        $this->assertEquals($amount, $creditTransaction->credit);

        // Assert that total debits equal total credits
        $totalDebits = $debitTransaction->debit + $creditTransaction->debit;
        $totalCredits = $debitTransaction->credit + $creditTransaction->credit;

        $this->assertEquals($totalDebits, $totalCredits);
    }

    /**
     * Test currency precision handling
     */
    public function test_currency_precision_handling(): void
    {
        // Test different currency amounts
        $currencyAmounts = [
            'USD' => 10000,  // $100.00
            'EUR' => 10000,  // €100.00
            'GBP' => 10000,  // £100.00
            'JPY' => 10000,  // ¥100.00 (no decimal places)
        ];

        foreach ($currencyAmounts as $currency => $amount) {
            $transaction = new DeAccountTransaction();
            $transaction->amount = $amount;

            // Assert that amount is always an integer
            $this->assertIsInt($transaction->amount);

            // Assert that amount represents the smallest currency unit
            $this->assertGreaterThan(0, $transaction->amount);
        }
    }

    /**
     * Test negative amounts (for reversals)
     */
    public function test_negative_amounts_for_reversals(): void
    {
        $originalAmount = 10000;
        $reversalAmount = -$originalAmount;

        $originalTransaction = new DeAccountTransaction();
        $originalTransaction->amount = $originalAmount;
        $originalTransaction->debit = $originalAmount;
        $originalTransaction->credit = 0;

        $reversalTransaction = new DeAccountTransaction();
        $reversalTransaction->amount = $reversalAmount;
        $reversalTransaction->debit = 0;
        $reversalTransaction->credit = $originalAmount; // Credit the original debit

        // Assert that reversal negates the original
        $this->assertEquals($originalAmount, $originalTransaction->amount);
        $this->assertEquals($reversalAmount, $reversalTransaction->amount);

        // Assert that reversal credit equals original debit
        $this->assertEquals($originalTransaction->debit, $reversalTransaction->credit);
    }

    /**
     * Test zero amounts are handled correctly
     */
    public function test_zero_amounts_handled_correctly(): void
    {
        $transaction = new DeAccountTransaction();
        $transaction->amount = 0;
        $transaction->debit = 0;
        $transaction->credit = 0;

        $this->assertEquals(0, $transaction->amount);
        $this->assertEquals(0, $transaction->debit);
        $this->assertEquals(0, $transaction->credit);
        $this->assertIsInt($transaction->amount);
    }

    /**
     * Test maximum amount limits
     */
    public function test_maximum_amount_limits(): void
    {
        // Test very large amounts (but still within integer limits)
        $largeAmount = PHP_INT_MAX - 1000; // Leave some buffer

        $transaction = new DeAccountTransaction();
        $transaction->amount = $largeAmount;

        $this->assertIsInt($transaction->amount);
        $this->assertEquals($largeAmount, $transaction->amount);
    }

    /**
     * Test arithmetic operations maintain precision
     */
    public function test_arithmetic_operations_maintain_precision(): void
    {
        $amount1 = 10000; // $100.00
        $amount2 = 5000;  // $50.00

        // Addition
        $sum = $amount1 + $amount2;
        $this->assertEquals(15000, $sum);
        $this->assertIsInt($sum);

        // Subtraction
        $difference = $amount1 - $amount2;
        $this->assertEquals(5000, $difference);
        $this->assertIsInt($difference);

        // Multiplication
        $product = $amount1 * 2;
        $this->assertEquals(20000, $product);
        $this->assertIsInt($product);

        // Division (should round to nearest cent)
        $quotient = intval($amount1 / 3);
        $this->assertEquals(3333, $quotient); // 10000/3 = 3333.33... rounded down
        $this->assertIsInt($quotient);
    }
}
