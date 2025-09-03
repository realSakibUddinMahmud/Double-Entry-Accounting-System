<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;
use App\Models\Company;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeAccount>
 */
class AccountFactory extends Factory
{
    protected $model = DeAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => null, // Will be set by the calling test
            'account_no' => $this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->words(2, true),
            'account_type_id' => null,
            'accountable_type' => 1, // Company by default
            'accountable_id' => 1,   // Default company ID
            'created_by' => 1,       // Default user ID
            'status' => 'active',
            'root_type' => 1,        // Assets by default
            'financial_statement_placement' => 'balance_sheet',
            '_lft' => 1,
            '_rgt' => 2,
            'parent_id' => null,
        ];
    }

    /**
     * Create an asset account
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 1,
            'title' => $this->faker->randomElement(['Cash', 'Bank', 'Accounts Receivable', 'Inventory']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create a liability account
     */
    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 3,
            'title' => $this->faker->randomElement(['Accounts Payable', 'Notes Payable', 'Accrued Expenses']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create an equity/capital account
     */
    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 5,
            'title' => $this->faker->randomElement(['Capital', 'Retained Earnings', 'Owner Equity']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create an income account
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 4,
            'title' => $this->faker->randomElement(['Sales Revenue', 'Service Income', 'Interest Income']),
            'financial_statement_placement' => 'income_statement',
        ]);
    }

    /**
     * Create an expense account
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 2,
            'title' => $this->faker->randomElement(['Office Expense', 'Rent Expense', 'Utilities Expense']),
            'financial_statement_placement' => 'income_statement',
        ]);
    }

    /**
     * Create accounts for a complete chart of accounts
     */
    public function chartOfAccounts(): array
    {
        return [
            // Assets
            ['root_type' => 1, 'account_no' => 1000, 'title' => 'Cash'],
            ['root_type' => 1, 'account_no' => 1100, 'title' => 'Accounts Receivable'],
            ['root_type' => 1, 'account_no' => 1200, 'title' => 'Inventory'],
            
            // Liabilities  
            ['root_type' => 3, 'account_no' => 2000, 'title' => 'Accounts Payable'],
            ['root_type' => 3, 'account_no' => 2100, 'title' => 'Notes Payable'],
            
            // Equity
            ['root_type' => 5, 'account_no' => 3000, 'title' => 'Capital'],
            ['root_type' => 5, 'account_no' => 3100, 'title' => 'Retained Earnings'],
            
            // Income
            ['root_type' => 4, 'account_no' => 4000, 'title' => 'Sales Revenue'],
            ['root_type' => 4, 'account_no' => 4100, 'title' => 'Service Income'],
            
            // Expenses
            ['root_type' => 2, 'account_no' => 5000, 'title' => 'Cost of Goods Sold'],
            ['root_type' => 2, 'account_no' => 5100, 'title' => 'Office Expense'],
        ];
    }
}