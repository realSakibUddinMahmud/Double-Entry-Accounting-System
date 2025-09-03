<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('tenants')->insertOrIgnore([
            [
                'name' => 'Default Tenant',
                'domain' => 'localhost',
                'database' => json_encode([
                    'driver' => 'mysql',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'database' => 'accounting_system',
                    'username' => 'laravel',
                    'password' => 'password',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
