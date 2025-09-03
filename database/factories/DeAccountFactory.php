<?php

namespace Database\Factories;

use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeAccount>
 */
class DeAccountFactory extends Factory
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
            'company_id' => 1,
            'account_no' => $this->faker->unique()->numerify('####'),
            'title' => $this->faker->words(2, true),
            'account_type_id' => DeAccountType::factory(),
            'accountable_type' => null,
            'accountable_id' => null,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => $this->faker->numberBetween(1, 5), // 1=Assets, 2=Expenses, 3=Liabilities, 4=Income, 5=Capital
            'financial_statement_placement' => $this->faker->randomElement(['balance_sheet', 'income_statement']),
        ];
    }

    /**
     * Create an asset account
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 1, // Assets
            'title' => $this->faker->randomElement(['Cash', 'Bank', 'Accounts Receivable', 'Inventory', 'Equipment']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create a liability account
     */
    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 3, // Liabilities
            'title' => $this->faker->randomElement(['Accounts Payable', 'Loans Payable', 'Accrued Expenses']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create an equity/capital account
     */
    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 5, // Capital
            'title' => $this->faker->randomElement(['Owner\'s Equity', 'Retained Earnings', 'Capital Stock']),
            'financial_statement_placement' => 'balance_sheet',
        ]);
    }

    /**
     * Create an income account
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 4, // Income
            'title' => $this->faker->randomElement(['Sales Revenue', 'Service Revenue', 'Interest Income']),
            'financial_statement_placement' => 'income_statement',
        ]);
    }

    /**
     * Create an expense account
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'root_type' => 2, // Expenses
            'title' => $this->faker->randomElement(['Cost of Goods Sold', 'Operating Expenses', 'Interest Expense']),
            'financial_statement_placement' => 'income_statement',
        ]);
    }

    /**
     * Create a cash account
     */
    public function cash(): static
    {
        return $this->asset()->state(fn (array $attributes) => [
            'title' => 'Cash',
            'account_no' => '1000',
        ]);
    }

    /**
     * Create a bank account
     */
    public function bank(): static
    {
        return $this->asset()->state(fn (array $attributes) => [
            'title' => 'Bank Account',
            'account_no' => '1100',
        ]);
    }

    /**
     * Create an accounts receivable account
     */
    public function accountsReceivable(): static
    {
        return $this->asset()->state(fn (array $attributes) => [
            'title' => 'Accounts Receivable',
            'account_no' => '1200',
        ]);
    }

    /**
     * Create an inventory account
     */
    public function inventory(): static
    {
        return $this->asset()->state(fn (array $attributes) => [
            'title' => 'Inventory',
            'account_no' => '1300',
        ]);
    }

    /**
     * Create an accounts payable account
     */
    public function accountsPayable(): static
    {
        return $this->liability()->state(fn (array $attributes) => [
            'title' => 'Accounts Payable',
            'account_no' => '2000',
        ]);
    }

    /**
     * Create a sales revenue account
     */
    public function salesRevenue(): static
    {
        return $this->income()->state(fn (array $attributes) => [
            'title' => 'Sales Revenue',
            'account_no' => '4000',
        ]);
    }

    /**
     * Create a COGS account
     */
    public function costOfGoodsSold(): static
    {
        return $this->expense()->state(fn (array $attributes) => [
            'title' => 'Cost of Goods Sold',
            'account_no' => '5000',
        ]);
    }
}
