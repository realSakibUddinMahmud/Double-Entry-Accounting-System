<?php

namespace Database\Factories;

use Hilinkz\DEAccounting\Models\DeAccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeAccountType>
 */
class DeAccountTypeFactory extends Factory
{
    protected $model = DeAccountType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }

    /**
     * Create an asset account type
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Current Assets', 'Fixed Assets', 'Cash and Cash Equivalents']),
            'description' => 'Asset account type for balance sheet items',
        ]);
    }

    /**
     * Create a liability account type
     */
    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Current Liabilities', 'Long-term Liabilities', 'Accounts Payable']),
            'description' => 'Liability account type for balance sheet items',
        ]);
    }

    /**
     * Create an equity account type
     */
    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Owner\'s Equity', 'Retained Earnings', 'Capital Stock']),
            'description' => 'Equity account type for balance sheet items',
        ]);
    }

    /**
     * Create an income account type
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Operating Revenue', 'Other Income', 'Sales Revenue']),
            'description' => 'Income account type for income statement items',
        ]);
    }

    /**
     * Create an expense account type
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['Operating Expenses', 'Cost of Goods Sold', 'Administrative Expenses']),
            'description' => 'Expense account type for income statement items',
        ]);
    }
}
