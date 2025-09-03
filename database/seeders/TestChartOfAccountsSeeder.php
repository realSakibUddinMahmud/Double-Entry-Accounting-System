<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;

class TestChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create account types if they don't exist using raw queries for better compatibility
        $accountTypes = [
            ['id' => 1, 'name' => 'Cash and Bank'],
            ['id' => 2, 'name' => 'Current Assets'],
            ['id' => 3, 'name' => 'Fixed Assets'],
            ['id' => 4, 'name' => 'Current Liabilities'],
            ['id' => 5, 'name' => 'Long Term Liabilities'],
            ['id' => 6, 'name' => 'Equity'],
            ['id' => 7, 'name' => 'Revenue'],
            ['id' => 8, 'name' => 'Cost of Goods Sold'],
            ['id' => 9, 'name' => 'Operating Expenses'],
        ];

        // Check if account_types table exists and create account types
        if (DB::connection()->getSchemaBuilder()->hasTable('account_types')) {
            foreach ($accountTypes as $type) {
                DB::table('account_types')->updateOrInsert(['id' => $type['id']], [
                    'title' => $type['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create a complete chart of accounts for testing
        $accounts = [
            // ASSETS (root_type = 1)
            [
                'account_no' => 1000,
                'title' => 'Cash',
                'root_type' => 1,
                'account_type_id' => 1,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1010,
                'title' => 'Petty Cash',
                'root_type' => 1,
                'account_type_id' => 1,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1100,
                'title' => 'Bank Account - Checking',
                'root_type' => 1,
                'account_type_id' => 1,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1200,
                'title' => 'Accounts Receivable',
                'root_type' => 1,
                'account_type_id' => 2,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1300,
                'title' => 'Inventory',
                'root_type' => 1,
                'account_type_id' => 2,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1400,
                'title' => 'Prepaid Expenses',
                'root_type' => 1,
                'account_type_id' => 2,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1500,
                'title' => 'Equipment',
                'root_type' => 1,
                'account_type_id' => 3,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 1510,
                'title' => 'Accumulated Depreciation - Equipment',
                'root_type' => 1,
                'account_type_id' => 3,
                'financial_statement_placement' => 'balance_sheet',
            ],

            // LIABILITIES (root_type = 3)
            [
                'account_no' => 2000,
                'title' => 'Accounts Payable',
                'root_type' => 3,
                'account_type_id' => 4,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 2100,
                'title' => 'Accrued Expenses',
                'root_type' => 3,
                'account_type_id' => 4,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 2200,
                'title' => 'Sales Tax Payable',
                'root_type' => 3,
                'account_type_id' => 4,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 2500,
                'title' => 'Notes Payable',
                'root_type' => 3,
                'account_type_id' => 5,
                'financial_statement_placement' => 'balance_sheet',
            ],

            // EQUITY/CAPITAL (root_type = 5)
            [
                'account_no' => 3000,
                'title' => 'Owner Capital',
                'root_type' => 5,
                'account_type_id' => 6,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 3100,
                'title' => 'Retained Earnings',
                'root_type' => 5,
                'account_type_id' => 6,
                'financial_statement_placement' => 'balance_sheet',
            ],
            [
                'account_no' => 3900,
                'title' => 'Owner Drawings',
                'root_type' => 5,
                'account_type_id' => 6,
                'financial_statement_placement' => 'balance_sheet',
            ],

            // INCOME/REVENUE (root_type = 4)
            [
                'account_no' => 4000,
                'title' => 'Sales Revenue',
                'root_type' => 4,
                'account_type_id' => 7,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 4100,
                'title' => 'Service Income',
                'root_type' => 4,
                'account_type_id' => 7,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 4900,
                'title' => 'Other Income',
                'root_type' => 4,
                'account_type_id' => 7,
                'financial_statement_placement' => 'income_statement',
            ],

            // EXPENSES (root_type = 2)
            [
                'account_no' => 5000,
                'title' => 'Cost of Goods Sold',
                'root_type' => 2,
                'account_type_id' => 8,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6000,
                'title' => 'Rent Expense',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6100,
                'title' => 'Office Supplies Expense',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6200,
                'title' => 'Utilities Expense',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6300,
                'title' => 'Advertising Expense',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6400,
                'title' => 'Depreciation Expense',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
            [
                'account_no' => 6500,
                'title' => 'Bank Fees',
                'root_type' => 2,
                'account_type_id' => 9,
                'financial_statement_placement' => 'income_statement',
            ],
        ];

        foreach ($accounts as $accountData) {
            DB::table('accounts')->updateOrInsert(
                ['account_no' => $accountData['account_no']],
                array_merge($accountData, [
                    'company_id' => 1, // Default company
                    'accountable_type' => 1, // Company
                    'accountable_id' => 1, // Company ID
                    'created_by' => 1, // Default user
                    'status' => 'active',
                    '_lft' => 1,
                    '_rgt' => 2,
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Get test accounts by their account numbers for use in tests
     */
    public static function getTestAccounts(): array
    {
        return [
            'cash' => DB::table('accounts')->where('account_no', 1000)->first(),
            'bank' => DB::table('accounts')->where('account_no', 1100)->first(),
            'accounts_receivable' => DB::table('accounts')->where('account_no', 1200)->first(),
            'inventory' => DB::table('accounts')->where('account_no', 1300)->first(),
            'accounts_payable' => DB::table('accounts')->where('account_no', 2000)->first(),
            'capital' => DB::table('accounts')->where('account_no', 3000)->first(),
            'sales_revenue' => DB::table('accounts')->where('account_no', 4000)->first(),
            'cogs' => DB::table('accounts')->where('account_no', 5000)->first(),
            'rent_expense' => DB::table('accounts')->where('account_no', 6000)->first(),
            'office_expense' => DB::table('accounts')->where('account_no', 6100)->first(),
        ];
    }
}