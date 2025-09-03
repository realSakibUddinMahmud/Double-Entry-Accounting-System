<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;

class TestChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create account types
        $assetType = DeAccountType::create([
            'name' => 'Assets',
            'description' => 'Asset accounts',
            'status' => 'active',
        ]);

        $liabilityType = DeAccountType::create([
            'name' => 'Liabilities',
            'description' => 'Liability accounts',
            'status' => 'active',
        ]);

        $equityType = DeAccountType::create([
            'name' => 'Equity',
            'description' => 'Equity accounts',
            'status' => 'active',
        ]);

        $incomeType = DeAccountType::create([
            'name' => 'Income',
            'description' => 'Income accounts',
            'status' => 'active',
        ]);

        $expenseType = DeAccountType::create([
            'name' => 'Expenses',
            'description' => 'Expense accounts',
            'status' => 'active',
        ]);

        // Create root accounts
        $assets = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1000',
            'title' => 'Assets',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1, // Assets
            'financial_statement_placement' => 'balance_sheet',
        ]);

        $liabilities = DeAccount::create([
            'company_id' => 1,
            'account_no' => '2000',
            'title' => 'Liabilities',
            'account_type_id' => $liabilityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 3, // Liabilities
            'financial_statement_placement' => 'balance_sheet',
        ]);

        $equity = DeAccount::create([
            'company_id' => 1,
            'account_no' => '3000',
            'title' => 'Equity',
            'account_type_id' => $equityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 5, // Capital
            'financial_statement_placement' => 'balance_sheet',
        ]);

        $income = DeAccount::create([
            'company_id' => 1,
            'account_no' => '4000',
            'title' => 'Income',
            'account_type_id' => $incomeType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 4, // Income
            'financial_statement_placement' => 'income_statement',
        ]);

        $expenses = DeAccount::create([
            'company_id' => 1,
            'account_no' => '5000',
            'title' => 'Expenses',
            'account_type_id' => $expenseType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 2, // Expenses
            'financial_statement_placement' => 'income_statement',
        ]);

        // Create asset sub-accounts
        $currentAssets = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1100',
            'title' => 'Current Assets',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $assets->id,
        ]);

        $cash = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1110',
            'title' => 'Cash',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $currentAssets->id,
        ]);

        $bank = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1120',
            'title' => 'Bank Account',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $currentAssets->id,
        ]);

        $accountsReceivable = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1130',
            'title' => 'Accounts Receivable',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $currentAssets->id,
        ]);

        $inventory = DeAccount::create([
            'company_id' => 1,
            'account_no' => '1140',
            'title' => 'Inventory',
            'account_type_id' => $assetType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 1,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $currentAssets->id,
        ]);

        // Create liability sub-accounts
        $currentLiabilities = DeAccount::create([
            'company_id' => 1,
            'account_no' => '2100',
            'title' => 'Current Liabilities',
            'account_type_id' => $liabilityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 3,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $liabilities->id,
        ]);

        $accountsPayable = DeAccount::create([
            'company_id' => 1,
            'account_no' => '2110',
            'title' => 'Accounts Payable',
            'account_type_id' => $liabilityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 3,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $currentLiabilities->id,
        ]);

        // Create equity sub-accounts
        $ownersEquity = DeAccount::create([
            'company_id' => 1,
            'account_no' => '3100',
            'title' => 'Owner\'s Equity',
            'account_type_id' => $equityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 5,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $equity->id,
        ]);

        $retainedEarnings = DeAccount::create([
            'company_id' => 1,
            'account_no' => '3200',
            'title' => 'Retained Earnings',
            'account_type_id' => $equityType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 5,
            'financial_statement_placement' => 'balance_sheet',
            'parent_id' => $equity->id,
        ]);

        // Create income sub-accounts
        $salesRevenue = DeAccount::create([
            'company_id' => 1,
            'account_no' => '4100',
            'title' => 'Sales Revenue',
            'account_type_id' => $incomeType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 4,
            'financial_statement_placement' => 'income_statement',
            'parent_id' => $income->id,
        ]);

        $serviceRevenue = DeAccount::create([
            'company_id' => 1,
            'account_no' => '4200',
            'title' => 'Service Revenue',
            'account_type_id' => $incomeType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 4,
            'financial_statement_placement' => 'income_statement',
            'parent_id' => $income->id,
        ]);

        // Create expense sub-accounts
        $costOfGoodsSold = DeAccount::create([
            'company_id' => 1,
            'account_no' => '5100',
            'title' => 'Cost of Goods Sold',
            'account_type_id' => $expenseType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 2,
            'financial_statement_placement' => 'income_statement',
            'parent_id' => $expenses->id,
        ]);

        $operatingExpenses = DeAccount::create([
            'company_id' => 1,
            'account_no' => '5200',
            'title' => 'Operating Expenses',
            'account_type_id' => $expenseType->id,
            'created_by' => 1,
            'status' => 'active',
            'root_type' => 2,
            'financial_statement_placement' => 'income_statement',
            'parent_id' => $expenses->id,
        ]);

        $this->command->info('Test Chart of Accounts created successfully!');
    }
}
