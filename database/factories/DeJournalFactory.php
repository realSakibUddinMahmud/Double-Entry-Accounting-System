<?php

namespace Database\Factories;

use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeJournal>
 */
class DeJournalFactory extends Factory
{
    protected $model = DeJournal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 100000); // Amount in cents

        // Create balanced debit and credit transactions
        $debitTransaction = DeAccountTransaction::factory()->debit()->amount($amount)->create();
        $creditTransaction = DeAccountTransaction::factory()->credit()->amount($amount)->create();

        return [
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'created_by' => 1,
            'task_id' => null,
            'transaction_type' => $this->faker->randomElement(['sale', 'purchase', 'payment', 'receipt', 'adjustment']),
            'note' => $this->faker->sentence(),
            'amount' => $amount,
            'debit_transaction_id' => $debitTransaction->id,
            'credit_transaction_id' => $creditTransaction->id,
            'journalable_id' => null,
            'journalable_type' => null,
        ];
    }

    /**
     * Create a balanced journal entry with specific amount
     */
    public function amount(int $amount): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            // Create balanced transactions
            $debitTransaction = DeAccountTransaction::factory()->debit()->amount($amount)->create();
            $creditTransaction = DeAccountTransaction::factory()->credit()->amount($amount)->create();

            return [
                'amount' => $amount,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
            ];
        });
    }

    /**
     * Create a journal entry for a specific date
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create a journal entry related to a specific model
     */
    public function relatedTo(string $modelType, int $modelId): static
    {
        return $this->state(fn (array $attributes) => [
            'journalable_type' => $modelType,
            'journalable_id' => $modelId,
        ]);
    }

    /**
     * Create a sales journal entry
     */
    public function sales(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 100000);

            // Create sales-specific transactions
            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($amount)
                ->state(['note' => 'Cash/Accounts Receivable'])
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($amount)
                ->state(['note' => 'Sales Revenue'])
                ->create();

            return [
                'transaction_type' => 'sale',
                'amount' => $amount,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'note' => 'Sales Transaction',
            ];
        });
    }

    /**
     * Create a purchase journal entry
     */
    public function purchase(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 100000);

            // Create purchase-specific transactions
            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($amount)
                ->state(['note' => 'Inventory/Purchase'])
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($amount)
                ->state(['note' => 'Accounts Payable'])
                ->create();

            return [
                'transaction_type' => 'purchase',
                'amount' => $amount,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'note' => 'Purchase Transaction',
            ];
        });
    }

    /**
     * Create a cash receipt journal entry
     */
    public function cashReceipt(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 50000);

            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($amount)
                ->state(['note' => 'Cash'])
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($amount)
                ->state(['note' => 'Revenue/Receivable'])
                ->create();

            return [
                'transaction_type' => 'receipt',
                'amount' => $amount,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'note' => 'Cash Receipt',
            ];
        });
    }

    /**
     * Create a cash payment journal entry
     */
    public function cashPayment(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->numberBetween(1000, 50000);

            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($amount)
                ->state(['note' => 'Expense/Payable'])
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($amount)
                ->state(['note' => 'Cash'])
                ->create();

            return [
                'transaction_type' => 'payment',
                'amount' => $amount,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'note' => 'Cash Payment',
            ];
        });
    }

    /**
     * Create an unbalanced journal entry (for testing validation)
     */
    public function unbalanced(): static
    {
        return $this->state(function (array $attributes) {
            $debitAmount = $this->faker->numberBetween(1000, 10000);
            $creditAmount = $debitAmount + $this->faker->numberBetween(1, 100); // Intentionally unbalanced

            $debitTransaction = DeAccountTransaction::factory()
                ->debit()
                ->amount($debitAmount)
                ->create();

            $creditTransaction = DeAccountTransaction::factory()
                ->credit()
                ->amount($creditAmount)
                ->create();

            return [
                'amount' => $debitAmount, // This will be different from credit amount
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'note' => 'Unbalanced Entry (Test)',
            ];
        });
    }

    /**
     * Create a journal entry with specific transactions
     */
    public function withTransactions(DeAccountTransaction $debitTransaction, DeAccountTransaction $creditTransaction): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $debitTransaction->amount,
            'debit_transaction_id' => $debitTransaction->id,
            'credit_transaction_id' => $creditTransaction->id,
        ]);
    }
}
