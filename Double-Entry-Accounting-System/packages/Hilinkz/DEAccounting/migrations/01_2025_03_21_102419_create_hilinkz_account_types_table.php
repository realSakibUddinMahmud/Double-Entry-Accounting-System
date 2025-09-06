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
        if (!Schema::hasTable('account_types')) {
            Schema::create('account_types', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->timestamps();
            });
        } else {
            Schema::table('account_types', function (Blueprint $table) {
                if (!Schema::hasColumn('account_types', 'title')) {
                    $table->string('title')->unique();
                }
                if (!Schema::hasColumn('account_types', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('account_types', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $account_types = [
            ['id' => 1, 'title' => 'Default', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'title' => 'Receivable', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'title' => 'Payable', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'title' => 'Wallet', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'title' => 'Bank', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'title' => 'Purchase', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'title' => 'Other_Expense', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'title' => 'Other_Income', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'title' => 'Unspecified', 'created_at' => now(), 'updated_at' => now()]
        ];

        DB::table('account_types')->insertOrIgnore($account_types);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
