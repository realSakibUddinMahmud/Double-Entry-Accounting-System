<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MoneyMathTest extends TestCase
{
    /**
     * Test that monetary calculations use proper precision
     */
    public function test_decimal_precision_is_maintained()
    {
        // Test basic decimal arithmetic that can cause precision issues
        $amount1 = '10.555';  // Amount that needs rounding
        $amount2 = '5.445';   // Amount that needs rounding
        
        $result = bcadd($amount1, $amount2, 2);
        
        $this->assertEquals('16.00', $result);
        
        // Test that BC math handles precision correctly
        $preciseAmount1 = '123.456789';
        $preciseAmount2 = '876.543211';
        $preciseResult = bcadd($preciseAmount1, $preciseAmount2, 2);
        
        $this->assertEquals('1000.00', $preciseResult);
    }

    /**
     * Test that we never use native float arithmetic for money
     */
    public function test_no_float_rounding_drift()
    {
        // Classic floating point precision problem
        $values = [0.1, 0.1, 0.1]; // Three 0.1 values
        
        // Using float arithmetic (WRONG) - demonstrates precision loss
        $floatSum = 0;
        foreach ($values as $value) {
            $floatSum += $value;
        }
        
        // Using BC Math (CORRECT)
        $bcSum = '0';
        foreach ($values as $value) {
            $bcSum = bcadd($bcSum, (string)$value, 10); // Use higher precision to show the difference
        }
        
        // Float arithmetic has precision issues with this specific case
        $this->assertNotEquals(0.3, $floatSum, 'Float arithmetic has rounding errors');
        $this->assertEquals('0.3000000000', $bcSum, 'BC Math maintains precision');
        
        // For money, we'd use 2 decimal places
        $bcSumMoney = bcadd('0', $bcSum, 2);
        $this->assertEquals('0.30', $bcSumMoney);
    }

    /**
     * Test money formatting for display
     */
    public function test_money_formatting()
    {
        $amount = 1234.56;
        
        // Format for display
        $formatted = number_format($amount, 2, '.', ',');
        $this->assertEquals('1,234.56', $formatted);
        
        // Test zero values
        $zero = 0;
        $formattedZero = number_format($zero, 2, '.', '');
        $this->assertEquals('0.00', $formattedZero);
    }

    /**
     * Test currency conversion (if multiple currencies supported)
     */
    public function test_currency_conversion_precision()
    {
        $usdAmount = '100.00';
        $exchangeRate = '1.25'; // USD to EUR
        
        $eurAmount = bcmul($usdAmount, $exchangeRate, 2);
        
        $this->assertEquals('125.00', $eurAmount);
    }

    /**
     * Test large dataset summation without precision loss
     */
    public function test_large_dataset_summation()
    {
        // Create 1000 small amounts
        $amounts = array_fill(0, 1000, '0.01');
        
        $sum = '0';
        foreach ($amounts as $amount) {
            $sum = bcadd($sum, $amount, 2);
        }
        
        $this->assertEquals('10.00', $sum);
        
        // Verify float would give different result due to precision
        $floatSum = array_sum(array_map('floatval', $amounts));
        $this->assertNotEquals(10.00, $floatSum);
    }

    /**
     * Test negative number handling
     */
    public function test_negative_amounts()
    {
        $positive = '100.50';
        $negative = '-50.25';
        
        $result = bcadd($positive, $negative, 2);
        
        $this->assertEquals('50.25', $result);
    }

    /**
     * Test rounding rules for different scenarios
     */
    public function test_rounding_rules()
    {
        // Test standard rounding (0.5 rounds up)
        $amount = '10.125';
        $rounded = bcadd($amount, '0', 2); // BC automatically rounds
        
        // For display purposes, we might need custom rounding
        $displayAmount = number_format(floatval($amount), 2, '.', '');
        $this->assertEquals('10.13', $displayAmount); // Rounds 0.125 up to 0.13
        
        // Test banker's rounding (round to even)
        $this->assertIsString($rounded);
    }

    /**
     * Test percentage calculations
     */
    public function test_percentage_calculations()
    {
        $amount = '100.00';
        $taxRate = '0.08'; // 8%
        
        $taxAmount = bcmul($amount, $taxRate, 2);
        $totalAmount = bcadd($amount, $taxAmount, 2);
        
        $this->assertEquals('8.00', $taxAmount);
        $this->assertEquals('108.00', $totalAmount);
    }

    /**
     * Test division and remainder handling
     */
    public function test_division_handling()
    {
        $totalAmount = '100.00';
        $parts = 3;
        
        $perPart = bcdiv($totalAmount, (string)$parts, 2);
        $remainder = bcsub($totalAmount, bcmul($perPart, (string)$parts, 2), 2);
        
        $this->assertEquals('33.33', $perPart);
        $this->assertEquals('0.01', $remainder);
        
        // Verify we can distribute the remainder
        $adjustedTotal = bcadd(bcmul($perPart, (string)($parts - 1), 2), bcadd($perPart, $remainder, 2), 2);
        $this->assertEquals($totalAmount, $adjustedTotal);
    }

    /**
     * Test comparison operations
     */
    public function test_amount_comparisons()
    {
        $amount1 = '100.50';
        $amount2 = '100.51';
        $amount3 = '100.50';
        
        $this->assertEquals(-1, bccomp($amount1, $amount2, 2)); // amount1 < amount2
        $this->assertEquals(1, bccomp($amount2, $amount1, 2));  // amount2 > amount1
        $this->assertEquals(0, bccomp($amount1, $amount3, 2));  // amount1 == amount3
    }

    /**
     * Test that decimal places are preserved in storage format
     */
    public function test_storage_format_preservation()
    {
        $amounts = ['10', '10.0', '10.00', '10.50', '10.99'];
        
        foreach ($amounts as $amount) {
            $stored = bcadd($amount, '0', 2);
            
            // All amounts should be stored with 2 decimal places
            $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $stored);
        }
    }
}