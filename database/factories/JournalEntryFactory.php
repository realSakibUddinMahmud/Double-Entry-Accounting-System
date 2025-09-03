<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Hilinkz\DEAccounting\Models\DeJournal;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Hilinkz\DEAccounting\Models\DeJournal>
 */
class JournalEntryFactory extends Factory
{
    protected $model = DeJournal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => null,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'amount' => 0, // Will be set when transactions are created
            'credit_transaction_id' => null,
            'debit_transaction_id' => null,
            'task_id' => null,
            'transaction_type' => $this->faker->randomElement(['sale', 'purchase', 'expense', 'payment', 'adjustment']),
            'created_by' => 1,
            'note' => $this->faker->optional()->sentence(),
            'journalable_type' => null,
            'journalable_id' => null,
        ];
    }

    /**
     * Create a balanced journal entry with specific debit and credit accounts
     */
    public function balanced(DeAccount $debitAccount, DeAccount $creditAccount, float $amount): static
    {
        return $this->afterCreating(function (DeJournal $journal) use ($debitAccount, $creditAccount, $amount) {
            // Create debit transaction
            $debitTransaction = DeAccountTransaction::create([
                'company_id' => $journal->company_id,
                'account_id' => $debitAccount->id,
                'amount' => $amount,
                'date' => $journal->date,
                'debit' => $amount,
                'credit' => 0,
                'type' => 'DEBIT',
                'created_by' => $journal->created_by,
                'note' => $journal->note,
            ]);

            // Create credit transaction
            $creditTransaction = DeAccountTransaction::create([
                'company_id' => $journal->company_id,
                'account_id' => $creditAccount->id,
                'amount' => $amount,
                'date' => $journal->date,
                'debit' => 0,
                'credit' => $amount,
                'type' => 'CREDIT',
                'created_by' => $journal->created_by,
                'note' => $journal->note,
            ]);

            // Link transactions to journal
            $journal->update([
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'amount' => $amount,
            ]);
        });
    }

    /**
     * Create a compound journal entry with multiple debits and/or credits
     * The lines array should contain arrays with 'account_id', 'debit', 'credit' keys
     */
    public function compound(array $lines): static
    {
        return $this->afterCreating(function (DeJournal $journal) use ($lines) {
            $totalDebits = 0;
            $totalCredits = 0;
            $transactions = [];

            foreach ($lines as $line) {
                $debitAmount = $line['debit'] ?? 0;
                $creditAmount = $line['credit'] ?? 0;
                $amount = max($debitAmount, $creditAmount);
                $type = $debitAmount > 0 ? 'DEBIT' : 'CREDIT';

                $transaction = DeAccountTransaction::create([
                    'company_id' => $journal->company_id,
                    'account_id' => $line['account_id'],
                    'amount' => $amount,
                    'date' => $journal->date,
                    'debit' => $debitAmount,
                    'credit' => $creditAmount,
                    'type' => $type,
                    'created_by' => $journal->created_by,
                    'note' => $line['note'] ?? $journal->note,
                ]);

                $transactions[] = $transaction;
                $totalDebits += $debitAmount;
                $totalCredits += $creditAmount;
            }

            // For compound entries, we'll use the first debit and credit transactions
            $firstDebit = collect($transactions)->first(fn($t) => $t->type === 'DEBIT');
            $firstCredit = collect($transactions)->first(fn($t) => $t->type === 'CREDIT');

            $journal->update([
                'debit_transaction_id' => $firstDebit?->id,
                'credit_transaction_id' => $firstCredit?->id,
                'amount' => $totalDebits, // Total amount of the journal entry
            ]);

            // Verify the entry is balanced
            if (abs($totalDebits - $totalCredits) > 0.01) {
                throw new \InvalidArgumentException("Journal entry is not balanced: Debits={$totalDebits}, Credits={$totalCredits}");
            }
        });
    }

    /**
     * Create an unbalanced journal entry (for testing error conditions)
     */
    public function unbalanced(DeAccount $debitAccount, DeAccount $creditAccount, float $debitAmount, float $creditAmount): static
    {
        return $this->compound([
            ['account_id' => $debitAccount->id, 'debit' => $debitAmount, 'credit' => 0],
            ['account_id' => $creditAccount->id, 'debit' => 0, 'credit' => $creditAmount],
        ]);
    }

    /**
     * Set specific date for the journal entry
     */
    public function onDate(Carbon|string $date): static
    {
        $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        return $this->state(fn (array $attributes) => [
            'date' => $dateString,
        ]);
    }

    /**
     * Set transaction type
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => $type,
        ]);
    }

    /**
     * Add reference to source document
     */
    public function referenceTo(string $type, int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'journalable_type' => $type,
            'journalable_id' => $id,
        ]);
    }

    /**
     * Create a simple cash sale journal entry
     */
    public function cashSale(DeAccount $cashAccount, DeAccount $salesAccount, float $amount): static
    {
        return $this->balanced($cashAccount, $salesAccount, $amount)
            ->ofType('sale')
            ->state(['note' => 'Cash sale transaction']);
    }

    /**
     * Create an expense payment journal entry
     */
    public function expensePayment(DeAccount $expenseAccount, DeAccount $cashAccount, float $amount): static
    {
        return $this->balanced($expenseAccount, $cashAccount, $amount)
            ->ofType('expense')
            ->state(['note' => 'Expense payment']);
    }

    /**
     * Create a customer payment journal entry
     */
    public function customerPayment(DeAccount $cashAccount, DeAccount $receivableAccount, float $amount): static
    {
        return $this->balanced($cashAccount, $receivableAccount, $amount)
            ->ofType('payment')
            ->state(['note' => 'Customer payment received']);
    }
}