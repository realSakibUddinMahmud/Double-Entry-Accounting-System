<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Refresh landlord procedures (no tenant switching needed)
        $this->refreshLandlordProcedures();
        echo 'Landlord procedures refreshed.' . PHP_EOL;

        // Tenant switching and procedure refresh
        $master_db_name = env('DB_DATABASE');
        $tenants = DB::connection('landlord')
            ->table('tenants')
            ->select('database')
            ->where('database', '!=', $master_db_name)
            ->distinct()
            ->get();

        foreach ($tenants as $tenant) {
            if ($tenant->database != null) {
                $db_name = $tenant->database;
                $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
                $db = DB::select($query, [$db_name]);

                if (!empty($db)) {
                    echo ($db_name . " => exists ");

                    // Tenant switching logic
                    Config::set('database.connections.mysql.database', $db_name);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

                    $this->refreshTenantProcedures();

                    echo ' => Procedures refreshed.' . PHP_EOL;
                }
            }
        }
    }

    protected function refreshLandlordProcedures()
    {
        // Currently no landlord-specific procedures
        // DB::connection('landlord')->statement("
        //     CREATE OR REPLACE PROCEDURE ...
        // ");
    }

    protected function refreshTenantProcedures()
    {
        // First procedure: proc_account_prev_balance_fixed_date
        DB::statement("
        CREATE PROCEDURE IF NOT EXISTS `proc_account_prev_balance_fixed_date`(
            IN pdate DATE, IN acc_id BIGINT(20), OUT prev_balance DOUBLE(20,2), OUT prev_date DATE, OUT today_total_debit DOUBLE(20,2), OUT today_total_credit DOUBLE(20,2), OUT today_closing_balance DOUBLE(20,2) 
        )
        BEGIN
            DECLARE st_date DATE;
            DECLARE picked_date DATE;
            DECLARE total_debit_amount DOUBLE(20,2) DEFAULT 0.00;
            DECLARE total_credit_amount DOUBLE(20,2) DEFAULT 0.00;
            DECLARE st_balance DOUBLE(20,2) DEFAULT 0.00;
            DECLARE acc_type INT(20);
            SET picked_date=pdate;
            
            SELECT COALESCE(t.closing_balance,0),t.date INTO st_balance,st_date FROM(
                SELECT account_id,date,closing_balance from account_statements as ast WHERE ast.date < picked_date and ast.account_id=acc_id ORDER by date DESC LIMIT 1
            ) as t;

            IF st_balance IS NULL THEN
                SET st_balance=0.00;
            END IF;  

            IF st_date IS NOT NULL THEN
                SET prev_date= st_date;
                SELECT sum(debit), sum(credit) INTO total_debit_amount,total_credit_amount 
                FROM account_transactions as tnx 
                WHERE tnx.date > st_date AND tnx.date < pdate AND tnx.account_id=acc_id;
            ELSE 
                SELECT date INTO prev_date 
                FROM account_transactions as tnx 
                WHERE tnx.date < pdate AND tnx.account_id=acc_id 
                ORDER BY date DESC LIMIT 1;
                
                SELECT sum(debit), sum(credit) INTO total_debit_amount,total_credit_amount 
                FROM account_transactions as tnx 
                WHERE tnx.date < pdate AND tnx.account_id=acc_id;
            END IF;
            
            IF total_debit_amount IS NULL THEN
                SET total_debit_amount=0.00;
            END IF;   
            
            IF total_credit_amount IS NULL THEN
                SET total_credit_amount=0.00;
            END IF;
            
            -- get account type
            SELECT root_type INTO acc_type from accounts WHERE id=acc_id;
            
            IF acc_type=1 OR acc_type=2 THEN 
                SET prev_balance=st_balance+total_debit_amount-total_credit_amount;
            ELSE 
                SET prev_balance=st_balance-total_debit_amount+total_credit_amount;
            END IF;
            
            -- get closing balance total debit and credit
            SELECT sum(debit), sum(credit) INTO today_total_debit,today_total_credit 
            FROM account_transactions as tnx 
            WHERE tnx.date = pdate AND tnx.account_id=acc_id;
            
            IF today_total_debit IS NULL THEN
                SET today_total_debit=0.00;
            END IF;   

            IF today_total_credit IS NULL THEN
                SET today_total_credit=0.00;
            END IF;
            
            -- calculate todays closing balance
            IF acc_type=1 OR acc_type=2 THEN 
                SET today_closing_balance=prev_balance+today_total_debit-today_total_credit;
            ELSE 
                SET today_closing_balance=prev_balance-today_total_debit+today_total_credit;
            END IF;
            
        END
        ");

        // Second procedure: proc_ledgers_info
        DB::statement("
        CREATE PROCEDURE IF NOT EXISTS `proc_ledgers_info`(
            IN start_date DATE, IN end_date DATE, IN acc_id BIGINT(20) UNSIGNED
        )
        BEGIN
            CALL proc_account_prev_balance_fixed_date(start_date,acc_id,@prev_balance,@prev_date,@today_total_debit,@today_total_credit,@today_closing_balance);

            SELECT t.*, (@cbalance:=@cbalance+t.debit-t.credit) as cbalance FROM (
                SELECT 
                    ROW_NUMBER() OVER(PARTITION BY date) as slno, 
                    acct_tnxs.date, 
                    tnx_id, 
                    acct_tnxs.account_id, 
                    acct_tnxs.debit, 
                    acct_tnxs.credit, 
                    acct_tnxs.balance, 
                    acct_tnxs.other_tnx_id, 
                    acct_tnxs.amount, 
                    act.title, 
                    otnx.note 
                FROM (
                    -- select credit transactions for account id 210 with dates and join with journals
                    SELECT 
                        ctnx.date, 
                        tnx_id, 
                        account_id, 
                        debit, 
                        credit, 
                        0 as balance, 
                        j1.debit_transaction_id as other_tnx_id, 
                        j1.amount  
                    FROM (
                        SELECT 
                            a.date, 
                            a.id as tnx_id, 
                            a.account_id, 
                            a.debit, 
                            a.credit, 
                            0 as balance 
                        FROM `account_transactions` as a 
                        WHERE account_id = acc_id 
                        AND date BETWEEN start_date AND end_date 
                        AND credit > 0
                    ) as ctnx 
                    -- ctnx means credit transactions
                    JOIN de_journals j1 on ctnx.tnx_id=j1.credit_transaction_id

                    UNION

                    -- select debit transactions for account id 210 with dates and join with journals
                    SELECT 
                        dtnx.date, 
                        tnx_id, 
                        account_id, 
                        debit, 
                        credit, 
                        0 as balance, 
                        j2.credit_transaction_id, 
                        j2.amount  
                    FROM (
                        SELECT 
                            a.date, 
                            a.id as tnx_id, 
                            a.account_id, 
                            a.debit, 
                            a.credit, 
                            0 as balance 
                        FROM `account_transactions` as a 
                        WHERE account_id = acc_id 
                        AND date BETWEEN start_date AND end_date 
                        AND debit > 0
                    ) as dtnx
                    -- dtnx means debit transactions
                    JOIN de_journals j2 on dtnx.tnx_id=j2.debit_transaction_id
                ) as acct_tnxs
                -- acct_tnxs is all account transactions, after union of debit and credit transactions
                JOIN account_transactions as otnx  -- for other transactions
                ON acct_tnxs.other_tnx_id = otnx.id 
                JOIN accounts as act 
                ON otnx.account_id = act.id
                ORDER BY acct_tnxs.date, acct_tnxs.tnx_id
            ) as t 
            JOIN (SELECT @cbalance:= @prev_balance) as tt;
        END
        ");
    }
}