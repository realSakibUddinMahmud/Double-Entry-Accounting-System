<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;
use Database\Factories\DeAccountFactory;
use Database\Factories\DeAccountTypeFactory;

class DeAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test account type rules for different root types
     */
    public function test_account_type_rules_for_different_root_types(): void
    {
        $testCases = [
            ['root_type' => 1, 'name' => 'Assets', 'increase_type' => 'DEBIT', 'decrease_type' => 'CREDIT'],
            ['root_type' => 2, 'name' => 'Expenses', 'increase_type' => 'DEBIT', 'decrease_type' => 'CREDIT'],
            ['root_type' => 3, 'name' => 'Liabilities', 'increase_type' => 'CREDIT', 'decrease_type' => 'DEBIT'],
            ['root_type' => 4, 'name' => 'Income', 'increase_type' => 'CREDIT', 'decrease_type' => 'DEBIT'],
            ['root_type' => 5, 'name' => 'Capital', 'increase_type' => 'CREDIT', 'decrease_type' => 'DEBIT'],
        ];

        foreach ($testCases as $testCase) {
            $account = DeAccount::factory()->create(['root_type' => $testCase['root_type']]);

            // Test increase type
            $increaseType = DeAccount::headTypeCheck($account->id, 'INCREASE');
            $this->assertEquals($testCase['increase_type'], $increaseType,
                "Account type {$testCase['name']} should have increase type {$testCase['increase_type']}");

            // Test decrease type
            $decreaseType = DeAccount::headTypeCheck($account->id, 'DECREASE');
            $this->assertEquals($testCase['decrease_type'], $decreaseType,
                "Account type {$testCase['name']} should have decrease type {$testCase['decrease_type']}");
        }
    }

    /**
     * Test account hierarchy relationships
     */
    public function test_account_hierarchy_relationships(): void
    {
        // Create parent account
        $parentAccount = DeAccount::factory()->create([
            'title' => 'Assets',
            'account_no' => '1000',
        ]);

        // Create child accounts
        $childAccount1 = DeAccount::factory()->create([
            'title' => 'Current Assets',
            'account_no' => '1100',
            'parent_id' => $parentAccount->id,
        ]);

        $childAccount2 = DeAccount::factory()->create([
            'title' => 'Fixed Assets',
            'account_no' => '1200',
            'parent_id' => $parentAccount->id,
        ]);

        // Test parent relationship
        $this->assertEquals($parentAccount->id, $childAccount1->parent_id);
        $this->assertEquals($parentAccount->id, $childAccount2->parent_id);

        // Test parent access
        $this->assertEquals($parentAccount->id, $childAccount1->parent->id);
        $this->assertEquals($parentAccount->id, $childAccount2->parent->id);
    }

    /**
     * Test account number uniqueness
     */
    public function test_account_number_uniqueness(): void
    {
        $account1 = DeAccount::factory()->create(['account_no' => '1000']);

        // Attempt to create another account with same number should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        DeAccount::factory()->create(['account_no' => '1000']);
    }

    /**
     * Test account status validation
     */
    public function test_account_status_validation(): void
    {
        $account = DeAccount::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $account->status);

        // Test status change
        $account->status = 'inactive';
        $account->save();
        $this->assertEquals('inactive', $account->status);
    }

    /**
     * Test financial statement placement
     */
    public function test_financial_statement_placement(): void
    {
        $balanceSheetAccount = DeAccount::factory()->create([
            'financial_statement_placement' => 'balance_sheet',
            'root_type' => 1, // Assets
        ]);

        $incomeStatementAccount = DeAccount::factory()->create([
            'financial_statement_placement' => 'income_statement',
            'root_type' => 4, // Income
        ]);

        $this->assertEquals('balance_sheet', $balanceSheetAccount->financial_statement_placement);
        $this->assertEquals('income_statement', $incomeStatementAccount->financial_statement_placement);
    }

    /**
     * Test accountable relationships
     */
    public function test_accountable_relationships(): void
    {
        // Test with different accountable types
        $accountables = DeAccount::$accountables;

        foreach ($accountables as $accountable) {
            $account = DeAccount::factory()->create([
                'accountable_type' => $accountable['class_id'],
                'accountable_id' => 1, // Mock ID
            ]);

            $this->assertEquals($accountable['class_id'], $account->accountable_type);
            $this->assertEquals($accountable['alias'], $account->accountable_alias);
        }
    }

    /**
     * Test account type relationship
     */
    public function test_account_type_relationship(): void
    {
        $accountType = DeAccountType::factory()->create();
        $account = DeAccount::factory()->create(['account_type_id' => $accountType->id]);

        $this->assertEquals($accountType->id, $account->account_type_id);
        $this->assertEquals($accountType->id, $account->accountType->id);
    }

    /**
     * Test root types constants
     */
    public function test_root_types_constants(): void
    {
        $expectedRootTypes = [
            ['id' => 1, 'name' => 'Assets'],
            ['id' => 2, 'name' => 'Expenses'],
            ['id' => 3, 'name' => 'Liabilities'],
            ['id' => 4, 'name' => 'Income'],
            ['id' => 5, 'name' => 'Capital'],
        ];

        $this->assertEquals($expectedRootTypes, DeAccount::$rootTypes);
    }

    /**
     * Test account creation with required fields
     */
    public function test_account_creation_with_required_fields(): void
    {
        $accountData = [
            'company_id' => 1,
            'account_no' => '1000',
            'title' => 'Test Account',
            'account_type_id' => DeAccountType::factory()->create()->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
        ];

        $account = DeAccount::create($accountData);

        $this->assertDatabaseHas('accounts', $accountData);
        $this->assertEquals($accountData['title'], $account->title);
        $this->assertEquals($accountData['account_no'], $account->account_no);
    }

    /**
     * Test account validation rules
     */
    public function test_account_validation_rules(): void
    {
        // Test that required fields are enforced
        $this->expectException(\Illuminate\Database\QueryException::class);

        DeAccount::create([
            'title' => 'Test Account',
            // Missing required fields
        ]);
    }

    /**
     * Test account soft delete (if implemented)
     */
    public function test_account_soft_delete(): void
    {
        $account = DeAccount::factory()->create();
        $accountId = $account->id;

        // If soft deletes are implemented, test them
        if (method_exists($account, 'delete')) {
            $account->delete();

            // Check if account is soft deleted
            $this->assertSoftDeleted('accounts', ['id' => $accountId]);
        }
    }

    /**
     * Test account transactions relationship
     */
    public function test_account_transactions_relationship(): void
    {
        $account = DeAccount::factory()->create();

        // Create some transactions for this account
        $transactions = \Hilinkz\DEAccounting\Models\DeAccountTransaction::factory()
            ->count(3)
            ->forAccount($account)
            ->create();

        $this->assertCount(3, $account->transactions);

        foreach ($transactions as $transaction) {
            $this->assertEquals($account->id, $transaction->account_id);
        }
    }

    /**
     * Test account balance calculation
     */
    public function test_account_balance_calculation(): void
    {
        $account = DeAccount::factory()->create(['root_type' => 1]); // Asset account

        // Create debit and credit transactions
        $debitTransaction = \Hilinkz\DEAccounting\Models\DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($account)
            ->create();

        $creditTransaction = \Hilinkz\DEAccounting\Models\DeAccountTransaction::factory()
            ->credit()
            ->amount(3000)
            ->forAccount($account)
            ->create();

        // For asset accounts: balance = debits - credits
        $expectedBalance = 10000 - 3000; // 7000

        // Calculate balance from transactions
        $debits = $account->transactions()->sum('debit');
        $credits = $account->transactions()->sum('credit');
        $balance = $debits - $credits;

        $this->assertEquals($expectedBalance, $balance);
    }
}
