<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create account types
        \DB::table('account_types')->insertOrIgnore([
            ['title' => 'Assets', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Liabilities', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Equity', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Income', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Expenses', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create banks
        \DB::table('banks')->insertOrIgnore([
            ['bank_name' => 'First National Bank', 'short_name' => 'FNB', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Standard Bank', 'short_name' => 'STD', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'ABSA Bank', 'short_name' => 'ABSA', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create basic chart of accounts
        \DB::table('accounts')->insertOrIgnore([
            [
                'company_id' => 1,
                'account_no' => '1000',
                'title' => 'Assets',
                'account_type_id' => 1,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 1,
                '_rgt' => 20,
                'parent_id' => 0,
                'root_type' => 'Assets',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '1100',
                'title' => 'Current Assets',
                'account_type_id' => 1,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 2,
                '_rgt' => 15,
                'parent_id' => 1,
                'root_type' => 'Assets',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '1110',
                'title' => 'Cash and Cash Equivalents',
                'account_type_id' => 1,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 3,
                '_rgt' => 8,
                'parent_id' => 2,
                'root_type' => 'Assets',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '1111',
                'title' => 'Petty Cash',
                'account_type_id' => 1,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 4,
                '_rgt' => 5,
                'parent_id' => 3,
                'root_type' => 'Assets',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '1112',
                'title' => 'Bank Account',
                'account_type_id' => 1,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 6,
                '_rgt' => 7,
                'parent_id' => 3,
                'root_type' => 'Assets',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '2000',
                'title' => 'Liabilities',
                'account_type_id' => 2,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 21,
                '_rgt' => 30,
                'parent_id' => 0,
                'root_type' => 'Liabilities',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '3000',
                'title' => 'Equity',
                'account_type_id' => 3,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 31,
                '_rgt' => 40,
                'parent_id' => 0,
                'root_type' => 'Equity',
                'financial_statement_placement' => 'Balance Sheet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '4000',
                'title' => 'Income',
                'account_type_id' => 4,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 41,
                '_rgt' => 50,
                'parent_id' => 0,
                'root_type' => 'Income',
                'financial_statement_placement' => 'Income Statement',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'company_id' => 1,
                'account_no' => '5000',
                'title' => 'Expenses',
                'account_type_id' => 5,
                'accountable_type' => '',
                'accountable_id' => 0,
                'created_by' => 1,
                'status' => 'active',
                '_lft' => 51,
                '_rgt' => 60,
                'parent_id' => 0,
                'root_type' => 'Expenses',
                'financial_statement_placement' => 'Income Statement',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // Create bank accounts
        \DB::table('bank_accounts')->insertOrIgnore([
            [
                'account_id' => 4, // Bank Account
                'bank_id' => 1, // First National Bank
                'account_no' => '1234567890',
                'account_name' => 'Main Business Account',
                'branch' => 'Main Branch',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // Create taxes
        \DB::table('taxes')->insertOrIgnore([
            ['name' => 'VAT', 'rate' => 15.00, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Income Tax', 'rate' => 25.00, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
