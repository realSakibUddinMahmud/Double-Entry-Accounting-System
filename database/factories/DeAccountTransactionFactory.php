<?php

namespace Database\Factories;

use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeAccountTransaction>
 */
class DeAccountTransactionFactory extends Factory
{
    protected $model = DeAccountTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(100, 10000); // Amount in cents
        $type = $this->faker->randomElement(['DEBIT', 'CREDIT']);

        return [
            'account_id' => DeAccount::factory(),
            'amount' => $amount,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'type' => $type,
            'debit' => $type === 'DEBIT' ? $amount : 0,
            'credit' => $type === 'CREDIT' ? $amount : 0,
            'created_by' => 1,
            'note' => $this->faker->sentence(),
            'account_transactionable_type' => null,
            'account_transactionable_id' => null,
        ];
    }

    /**
     * Create a debit transaction
     */
    public function debit(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(100, 10000);
            return [
                'type' => 'DEBIT',
                'debit' => $amount,
                'credit' => 0,
            ];
        });
    }

    /**
     * Create a credit transaction
     */
    public function credit(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(100, 10000);
            return [
                'type' => 'CREDIT',
                'debit' => 0,
                'credit' => $amount,
            ];
        });
    }

    /**
     * Create a transaction with specific amount
     */
    public function amount(int $amount): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $type = $attributes['type'] ?? $this->faker->randomElement(['DEBIT', 'CREDIT']);
            return [
                'amount' => $amount,
                'debit' => $type === 'DEBIT' ? $amount : 0,
                'credit' => $type === 'CREDIT' ? $amount : 0,
            ];
        });
    }

    /**
     * Create a transaction for a specific account
     */
    public function forAccount(DeAccount $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
        ]);
    }

    /**
     * Create a transaction with specific date
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create a transaction related to a specific model
     */
    public function relatedTo(string $modelType, int $modelId): static
    {
        return $this->state(fn (array $attributes) => [
            'account_transactionable_type' => $modelType,
            'account_transactionable_id' => $modelId,
        ]);
    }

    /**
     * Create a cash receipt transaction
     */
    public function cashReceipt(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 50000);
            return [
                'type' => 'DEBIT',
                'debit' => $amount,
                'credit' => 0,
                'note' => 'Cash Receipt',
            ];
        });
    }

    /**
     * Create a cash payment transaction
     */
    public function cashPayment(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 50000);
            return [
                'type' => 'CREDIT',
                'debit' => 0,
                'credit' => $amount,
                'note' => 'Cash Payment',
            ];
        });
    }

    /**
     * Create a sales transaction
     */
    public function sales(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 100000);
            return [
                'type' => 'CREDIT',
                'debit' => 0,
                'credit' => $amount,
                'note' => 'Sales Revenue',
            ];
        });
    }

    /**
     * Create a purchase transaction
     */
    public function purchase(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 100000);
            return [
                'type' => 'DEBIT',
                'debit' => $amount,
                'credit' => 0,
                'note' => 'Purchase',
            ];
        });
    }
}
