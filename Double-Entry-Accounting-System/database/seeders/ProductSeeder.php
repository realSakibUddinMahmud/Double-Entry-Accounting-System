<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $file = public_path('product-data/bangladesh_products_20000.csv');

        if (!File::exists($file)) {
            $this->command->error("CSV file not found: $file");
            return;
        }

        $data = array_map('str_getcsv', file($file));
        $header = array_map('trim', $data[0]);
        unset($data[0]);

        foreach ($data as $row) {
            $row = array_map('trim', $row);
            $record = array_combine($header, $row);
            $record['created_at'] = Carbon::now();
            $record['updated_at'] = Carbon::now();
            DB::connection('landlord')->table('products')->insert($record);
        }

        $this->command->info("Products seeded to landlord database from CSV successfully.");
    }
}
