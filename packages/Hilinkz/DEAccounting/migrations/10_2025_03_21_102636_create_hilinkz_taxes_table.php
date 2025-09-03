<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('rate', 5, 2); // assuming rate is a percentage (e.g., 15.00 for 15%)
                $table->string('status');
                $table->timestamps();
            });
        } else {
            Schema::table('taxes', function (Blueprint $table) {
                if (!Schema::hasColumn('taxes', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('taxes', 'rate')) {
                    $table->decimal('rate', 5, 2);
                }
                if (!Schema::hasColumn('taxes', 'status')) {
                    $table->string('status');
                }
                if (!Schema::hasColumn('taxes', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
