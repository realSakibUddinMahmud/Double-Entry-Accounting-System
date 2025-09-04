<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->decimal('conversion_factor', 15, 6)->default(1);
            $table->timestamps();
        });

        // Insert only the most useful/common units
        DB::table('units')->insertOrIgnore([
            ['id' => 1, 'name' => 'Piece', 'symbol' => 'pc', 'parent_id' => null, 'conversion_factor' => 1],
            ['id' => 2, 'name' => 'Kilogram', 'symbol' => 'kg', 'parent_id' => null, 'conversion_factor' => 1],
            ['id' => 3, 'name' => 'Gram', 'symbol' => 'g', 'parent_id' => 2, 'conversion_factor' => 0.001],
            ['id' => 4, 'name' => 'Litre', 'symbol' => 'L', 'parent_id' => null, 'conversion_factor' => 1],
            ['id' => 5, 'name' => 'Millilitre', 'symbol' => 'ml', 'parent_id' => 4, 'conversion_factor' => 0.001],
            ['id' => 6, 'name' => 'Box', 'symbol' => 'box', 'parent_id' => null, 'conversion_factor' => 1],
            ['id' => 7, 'name' => 'Dozen', 'symbol' => 'doz', 'parent_id' => 1, 'conversion_factor' => 12],
            ['id' => 8, 'name' => 'Meter', 'symbol' => 'm', 'parent_id' => null, 'conversion_factor' => 1],
            ['id' => 9, 'name' => 'Centimeter', 'symbol' => 'cm', 'parent_id' => 8, 'conversion_factor' => 0.01],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
