<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Hilinkz\DEAccounting\Models\DeAccount;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Company extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UsesTenantConnection;

    protected $fillable = [
        'id',
        'name',
        'region',
        'office_address',
        'contact_no',
        'email',
        'status',
    ];

    public function accounts()
    {
        // 'accountable_type' is stored as class_id (e.g., 5 for Store)
        $classId = null;
        foreach (DeAccount::$accountables as $item) {
            if ($item['class'] === self::class) {
                $classId = $item['class_id'];
                break;
            }
        }
        if (!$classId) {
            return collect();
        }

        return DeAccount::where('accountable_id', $this->id)
            ->where('accountable_type', $classId);
    }

    public static function createDB($db_name = NULL)          
    {
        // Store original configurations
        $originalDBName = config('database.connections.mysql.database');
        $originalSessionDriver = config('session.driver');


        // Temporarily disable session database driver
        config(['session.driver' => 'array']);

        $primaryDB = \Config::get('database.connections.mysql.database');

        // Create the database if it doesn't exist
        \DB::statement("CREATE DATABASE IF NOT EXISTS $db_name");

        // Switch to the new database
        $new = \Config::set('database.connections.mysql.database', $db_name);
        \DB::purge('mysql');
        \DB::reconnect('mysql');

        // Check if companies table exists
        $has_db_table = \Schema::hasTable('companies');

        if (!$has_db_table) {
            \Artisan::call('migrate --path=/database/migrations/tenant/');
            \Artisan::call('migrate --path=/packages/Hilinkz/DEAccounting/migrations/');
        }

        // Always restore original configurations
        config(['database.connections.mysql.database' => $originalDBName]);
        \DB::purge('mysql');
        \DB::reconnect('mysql');
        config(['session.driver' => $originalSessionDriver]);
    }

    // public static function createDB($db_name = NULL)   // without disable sessions didnt work
    // {
    //     $primaryDB = \Config::get('database.connections.mysql.database');

    //     // Create the tenant database if it doesn't exist
    //     \DB::statement("CREATE DATABASE IF NOT EXISTS $db_name");

    //     // Define a custom tenant connection for this DB dynamically
    //     \Config::set('database.connections.tenant', array_merge(
    //         \Config::get('database.connections.mysql'),
    //         ['database' => $db_name]
    //     ));

    //     // Purge and reconnect to refresh the new tenant connection
    //     \DB::purge('tenant');
    //     \DB::reconnect('tenant');

    //     \Log::info('Connected to tenant DB: ' . \DB::connection('tenant')->getDatabaseName());

    //     // Check if the companies table exists in tenant DB
    //     $has_db_table = \Schema::connection('tenant')->hasTable('companies');

    //     \Log::info('Has companies table: ' . ($has_db_table ? 'Yes' : 'No'));

    //     if (!$has_db_table) {
    //         // Now run all the tenant migrations using this tenant connection

    //         // \Artisan::call('migrate --path=/database/migrations/tenant/');
    //         // \Artisan::call('migrate --path=/packages/Hilinkz/DEAccounting/migrations/');

    //         \Artisan::call('migrate', [
    //             '--database' => 'tenant',
    //             '--path' => 'database/migrations/tenant',
    //         ]);

    //         $output = \Artisan::output();

    //         \Log::info("Running tenant migration on DB: $db_name");
    //         \Log::info("Migration output:\n" . $output);

    //         \Artisan::call('migrate', [
    //             '--database' => 'tenant',
    //             '--path' => 'packages/Hilinkz/DEAccounting/migrations',
    //         ]);

    //         // \Artisan::call('migrate', [
    //         //     '--database' => 'tenant',
    //         //     '--path' => 'database/migrations/tenant'
    //         // ]);

    //         // \Artisan::call('migrate', [
    //         //     '--database' => 'tenant',
    //         //     '--path' => 'database/migrations/tenant-views'
    //         // ]);

    //         // \Artisan::call('migrate', [
    //         //     '--database' => 'tenant',
    //         //     '--path' => 'database/migrations/tenant-stored-procedures'
    //         // ]);
    //     }
    // }

    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    /**
     * Booted model event for Company.
     * Creates default accounts on company creation.
     */
    protected static function booted()
    {
        static::created(function ($company) {
            // Get class_id for accountable_type
            $classId = null;
            foreach (\Hilinkz\DEAccounting\Models\DeAccount::$accountables as $item) {
                if ($item['class'] === self::class) {
                    $classId = $item['class_id'];
                    break;
                }
            }

            // 1. Create root accounts first and store their IDs
            $rootAccounts = [
                'Assets'      => ['root_type' => '1', 'account_no' => '1000'],
                'Liabilities' => ['root_type' => '3', 'account_no' => '3000'],
                'Income'      => ['root_type' => '4', 'account_no' => '4000'],
                'Expense'     => ['root_type' => '2', 'account_no' => '2000'],
                'Capital'     => ['root_type' => '5', 'account_no' => '5000'],
            ];

            $rootIds = [];
            foreach ($rootAccounts as $title => $info) {
                $account = \Hilinkz\DEAccounting\Models\DeAccount::create([
                    'company_id' => $company->id,
                    'account_no' => $info['account_no'],
                    'title' => $title,
                    'account_type_id' => 1, // default account type
                    'accountable_type' => $classId,
                    'accountable_id' => $company->id,
                    'created_by' => auth()->id(),
                    'root_type' => $info['root_type'],
                    'status' => 'ACTIVE',
                    'parent_id' => null,
                ]);
                $rootIds[$title] = $account->id;
            }

            // 2. Create sub-accounts under root accounts
            $subAccounts = [
                [
                    'title' => 'Cash',
                    'account_no' => '10001',
                    'root_type' => '1', // Assets
                    'parent_id' => $rootIds['Assets'],
                    'account_type_id' => 2, // receivable account type
                ],
                [
                    'title' => 'Purchase Tax',
                    'account_no' => '10002',
                    'root_type' => '1', // Assets
                    'parent_id' => $rootIds['Assets'],
                    'account_type_id' => 1,
                ],
                [
                    'title' => 'Sales Tax Payable',
                    'account_no' => '20001',
                    'root_type' => '3', // Liabilities
                    'parent_id' => $rootIds['Liabilities'],
                    'account_type_id' => 1,
                ]
            ];
            foreach ($subAccounts as $account) {
                \Hilinkz\DEAccounting\Models\DeAccount::create([
                    'company_id' => $company->id,
                    'account_no' => $account['account_no'],
                    'title' => $account['title'],
                    'root_type' => $account['root_type'],
                    'parent_id' => $account['parent_id'],
                    'account_type_id' => $account['account_type_id'],
                    'accountable_type' => $classId,
                    'accountable_id' => $company->id,
                    'created_by' => auth()->id(),
                    'status' => 'ACTIVE',
                ]);
            }
        });
    }

    public static function createDefaultAccounts($companyId, $database = null, $createdBy = null)
    {

        $rootAccounts = [
            'Assets'      => ['root_type' => '1', 'account_no' => '1000'],
            'Liabilities' => ['root_type' => '3', 'account_no' => '3000'],
            'Income'      => ['root_type' => '4', 'account_no' => '4000'],
            'Expense'     => ['root_type' => '2', 'account_no' => '2000'],
            'Capital'     => ['root_type' => '5', 'account_no' => '5000'],
        ];
        $rootIds = [];
        foreach ($rootAccounts as $title => $info) {
            $accountId = DB::table($database . '.accounts')->insertGetId([
                'company_id' => $companyId,
                'account_no' => $info['account_no'],
                'title' => $title,
                'account_type_id' => 1, // default account type
                'accountable_type' => 1,
                'accountable_id' => $companyId,
                'created_by' => $createdBy ?? null,
                'root_type' => $info['root_type'],
                'status' => 'ACTIVE',
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $rootIds[$title] = $accountId;
        }
        $subAccounts = [
            [
                'title' => 'Cash',
                'account_no' => '10001',
                'root_type' => '1', // Assets
                'parent_id' => $rootIds['Assets'], // Assuming Assets root account ID is 1
                'account_type_id' => 2, // receivable account type
            ],
            [
                'title' => 'Purchase Tax',
                'account_no' => '10002',
                'root_type' => '1', // Assets
                'parent_id' => $rootIds['Assets'],
                'account_type_id' => 1,
            ],
            [
                'title' => 'Sales Tax Payable',
                'account_no' => '20001',
                'root_type' => '3', // Liabilities
                'parent_id' => $rootIds['Liabilities'], // Assuming Liabilities root account ID is 2
                'account_type_id' => 1,
            ]
        ];
        foreach ($subAccounts as $account) {
            DB::table($database . '.accounts')->insert([
            'company_id' => $companyId,
            'account_no' => $account['account_no'],
            'title' => $account['title'],
            'root_type' => $account['root_type'],
            'parent_id' => $account['parent_id'],
            'account_type_id' => $account['account_type_id'],
            'accountable_type' => 1,
            'accountable_id' => $companyId,
            'created_by' => $createdBy ?? null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
            ]);
        }
    }
}
