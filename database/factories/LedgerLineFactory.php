<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Hilinkz\DEAccounting\Models\DeAccount;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeAccountTransaction>
 */
class LedgerLineFactory extends Factory
{
    protected $model = DeAccountTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 100, 10000);
        $type = $this->faker->randomElement(['DEBIT', 'CREDIT']);
        
        return [
            'company_id' => null,
            'account_id' => null, // Must be set by caller
            'amount' => $amount,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'debit' => $type === 'DEBIT' ? $amount : 0,
            'credit' => $type === 'CREDIT' ? $amount : 0,
            'type' => $type,
            'created_by' => 1,
            'note' => $this->faker->optional()->sentence(),
            'account_transactionable_type' => null,
            'account_transactionable_id' => null,
        ];
    }

    /**
     * Create a debit transaction
     */
    public function debit(float $amount = null): static
    {
        $amount = $amount ?? $this->faker->randomFloat(2, 100, 10000);
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'debit' => $amount,
            'credit' => 0,
            'type' => 'DEBIT',
        ]);
    }

    /**
     * Create a credit transaction
     */
    public function credit(float $amount = null): static
    {
        $amount = $amount ?? $this->faker->randomFloat(2, 100, 10000);
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'debit' => 0,
            'credit' => $amount,
            'type' => 'CREDIT',
        ]);
    }

    /**
     * Set the account for this transaction
     */
    public function forAccount(DeAccount $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
        ]);
    }

    /**
     * Set specific date for the transaction
     */
    public function onDate(Carbon|string $date): static
    {
        $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        return $this->state(fn (array $attributes) => [
            'date' => $dateString,
        ]);
    }

    /**
     * Add transaction reference (polymorphic relationship)
     */
    public function relatedTo(string $type, int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'account_transactionable_type' => $type,
            'account_transactionable_id' => $id,
        ]);
    }

    /**
     * Create a transaction with a specific amount and automatically determine debit/credit
     * based on the account's root type and whether it's an increase or decrease
     */
    public function increase(DeAccount $account, float $amount): static
    {
        // Determine if this should be a debit or credit based on account type
        $headType = DeAccount::headTypeCheck($account->id, 'INCREASE');
        
        if ($headType === 'DEBIT') {
            return $this->debit($amount)->forAccount($account);
        } else {
            return $this->credit($amount)->forAccount($account);
        }
    }

    /**
     * Create a transaction that decreases the account balance
     */
    public function decrease(DeAccount $account, float $amount): static
    {
        // Determine if this should be a debit or credit based on account type
        $headType = DeAccount::headTypeCheck($account->id, 'DECREASE');
        
        if ($headType === 'DEBIT') {
            return $this->debit($amount)->forAccount($account);
        } else {
            return $this->credit($amount)->forAccount($account);
        }
    }
}