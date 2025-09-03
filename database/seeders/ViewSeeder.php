<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Refresh landlord view (no tenant switching needed)
        $this->refreshLandlordView();
        echo 'Landlord view refreshed.' . PHP_EOL;

        // Tenant switching and view refresh
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
                    echo "{$db_name} => exists ";

                    // Tenant switching logic
                    Config::set('database.connections.mysql.database', $db_name);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

                    $this->refreshTenantView();

                    echo ' => View refreshed.' . PHP_EOL;
                }
            }
        }
    }

    protected function refreshLandlordView()
    {
        // Example for landlord view refresh (currently commented out)
        // DB::connection('landlord')->statement("
        //     CREATE OR REPLACE VIEW simple_users_view AS
        //     SELECT id, name, email, created_at
        //     FROM users
        // ");
    }

    protected function refreshTenantView()
    {
        DB::statement("
            CREATE OR REPLACE VIEW product_store_stock_view AS
            SELECT
                ps.store_id,
                s.name AS store_name,
                ps.product_id,
                p.name AS product_name,
                ps.base_unit_id,
                bu.name AS base_unit_name,
                ROUND((
                    IFNULL((
                        SELECT SUM(pi.quantity * u.conversion_factor)
                        FROM purchase_items pi
                        JOIN purchases pu ON pi.purchase_id = pu.id
                        JOIN units u ON pi.unit_id = u.id
                        WHERE pi.product_id = ps.product_id
                          AND pu.store_id = ps.store_id
                    ), 0)
                    +
                    IFNULL((
                        SELECT SUM(psa.quantity)
                        FROM product_stock_adjustments psa
                        JOIN stock_adjustments sa ON psa.stock_adjustment_id = sa.id
                        WHERE psa.product_id = ps.product_id
                          AND sa.store_id = ps.store_id
                          AND psa.action = '+'
                    ), 0)
                    -
                    IFNULL((
                        SELECT SUM(si.quantity * u.conversion_factor)
                        FROM sale_items si
                        JOIN sales sa ON si.sale_id = sa.id
                        JOIN units u ON si.unit_id = u.id
                        WHERE si.product_id = ps.product_id
                          AND sa.store_id = ps.store_id
                    ), 0)
                    -
                    IFNULL((
                        SELECT SUM(psa.quantity)
                        FROM product_stock_adjustments psa
                        JOIN stock_adjustments sa ON psa.stock_adjustment_id = sa.id
                        WHERE psa.product_id = ps.product_id
                          AND sa.store_id = ps.store_id
                          AND psa.action = '-'
                    ), 0)
                ), 2) AS current_stock_qty
            FROM
                product_store ps
                JOIN products p ON ps.product_id = p.id
                JOIN stores s ON ps.store_id = s.id
                JOIN units bu ON ps.base_unit_id = bu.id;
        ");

        DB::statement("
            CREATE OR REPLACE VIEW store_product_current_cogs_avg AS
            SELECT 
                pi.product_id,
                p.store_id,
                ROUND(SUM(pi.quantity * pi.per_unit_cogs) / NULLIF(SUM(pi.quantity), 0), 2) AS cogs_avg
            FROM 
                purchase_items pi
            JOIN 
                purchases p ON pi.purchase_id = p.id
            WHERE 
                p.status = 1
            GROUP BY 
                pi.product_id, p.store_id;
        ");
    }
}
