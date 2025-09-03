<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

                    $this->doSeeding($db_name);

                    echo ' => Brands seeded.' . PHP_EOL;
                }
            }
        }
    }

    protected function doSeeding($db_name)
    {
        DB::table($db_name . '.brands')->insertOrIgnore([
            [
                'name' => 'ACI',
                'slug' => Str::slug('ACI'),
                'logo' => null,
                'description' => 'ACI Limited is one of the leading conglomerates in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Square',
                'slug' => Str::slug('Square'),
                'logo' => null,
                'description' => 'Square Group is a renowned Bangladeshi industrial conglomerate.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pran',
                'slug' => Str::slug('Pran'),
                'logo' => null,
                'description' => 'PRAN is a leading food and beverage brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bashundhara',
                'slug' => Str::slug('Bashundhara'),
                'logo' => null,
                'description' => 'Bashundhara Group is a major Bangladeshi industrial conglomerate.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh',
                'slug' => Str::slug('Fresh'),
                'logo' => null,
                'description' => 'Fresh is a popular brand for food and consumer products in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Radhuni',
                'slug' => Str::slug('Radhuni'),
                'logo' => null,
                'description' => 'Radhuni is a well-known spice and food brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teer',
                'slug' => Str::slug('Teer'),
                'logo' => null,
                'description' => 'Teer is a leading edible oil and food brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Olympic',
                'slug' => Str::slug('Olympic'),
                'logo' => null,
                'description' => 'Olympic is a famous biscuit and food brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Parachute',
                'slug' => Str::slug('Parachute'),
                'logo' => null,
                'description' => 'Parachute is a popular coconut oil brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Keya',
                'slug' => Str::slug('Keya'),
                'logo' => null,
                'description' => 'Keya is a well-known soap and consumer goods brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marks',
                'slug' => Str::slug('Marks'),
                'logo' => null,
                'description' => 'Marks is a popular milk powder brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Igloo',
                'slug' => Str::slug('Igloo'),
                'logo' => null,
                'description' => 'Igloo is a leading ice cream and dairy brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Danish',
                'slug' => Str::slug('Danish'),
                'logo' => null,
                'description' => 'Danish is a well-known food and beverage brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aarong',
                'slug' => Str::slug('Aarong'),
                'logo' => null,
                'description' => 'Aarong is a leading lifestyle and fashion brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Walton',
                'slug' => Str::slug('Walton'),
                'logo' => null,
                'description' => 'Walton is a top electronics and appliance brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Savlon',
                'slug' => Str::slug('Savlon'),
                'logo' => null,
                'description' => 'Savlon is a trusted antiseptic and hygiene brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meril',
                'slug' => Str::slug('Meril'),
                'logo' => null,
                'description' => 'Meril is a popular personal care brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lux',
                'slug' => Str::slug('Lux'),
                'logo' => null,
                'description' => 'Lux is a famous soap brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Horlicks',
                'slug' => Str::slug('Horlicks'),
                'logo' => null,
                'description' => 'Horlicks is a popular health drink brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nestle',
                'slug' => Str::slug('Nestle'),
                'logo' => null,
                'description' => 'Nestle is a global food and beverage brand with a strong presence in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unilever',
                'slug' => Str::slug('Unilever'),
                'logo' => null,
                'description' => 'Unilever is a multinational consumer goods company with various brands in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pepsi',
                'slug' => Str::slug('Pepsi'),
                'logo' => null,
                'description' => 'Pepsi is a leading beverage brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Coca-Cola',
                'slug' => Str::slug('Coca-Cola'),
                'logo' => null,
                'description' => 'Coca-Cola is a globally recognized beverage brand with a strong presence in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Telenor',
                'slug' => Str::slug('Telenor'),
                'logo' => null,
                'description' => 'Telenor is a leading telecommunications brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grameenphone',
                'slug' => Str::slug('Grameenphone'),
                'logo' => null,
                'description' => 'Grameenphone is the largest mobile network operator in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Robi',
                'slug' => Str::slug('Robi'),
                'logo' => null,
                'description' => 'Robi is a major telecommunications brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Banglalink',
                'slug' => Str::slug('Banglalink'),
                'logo' => null,
                'description' => 'Banglalink is a popular mobile network operator in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Citycell',
                'slug' => Str::slug('Citycell'),
                'logo' => null,
                'description' => 'Citycell was one of the first mobile network operators in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teletalk',
                'slug' => Str::slug('Teletalk'),
                'logo' => null,
                'description' => 'Teletalk is the state-owned mobile network operator in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bengal Meat',
                'slug' => Str::slug('Bengal Meat'),
                'logo' => null,
                'description' => 'Bengal Meat is a leading meat and poultry brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kazi Farms',
                'slug' => Str::slug('Kazi Farms'),
                'logo' => null,
                'description' => 'Kazi Farms is a well-known poultry and livestock brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paragon',
                'slug' => Str::slug('Paragon'),
                'logo' => null,
                'description' => 'Paragon is a popular footwear brand in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bata',
                'slug' => Str::slug('Bata'),
                'logo' => null,
                'description' => 'Bata is a well-known international footwear brand with a strong presence in Bangladesh.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
