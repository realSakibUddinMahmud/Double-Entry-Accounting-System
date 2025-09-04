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
        if (!Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->string('bank_name')->unique();
                $table->string('short_name')->unique()->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('banks', function (Blueprint $table) {
                if (!Schema::hasColumn('banks', 'bank_name')) {
                    $table->string('bank_name')->unique();
                }
                if (!Schema::hasColumn('banks', 'short_name')) {
                    $table->string('short_name')->nullable();
                }
                if (!Schema::hasColumn('banks', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('banks', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $banks = [
            ['id' => 1, 'bank_name' => 'Dutch Bangla Bank Limited', 'short_name' => 'DBBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'bank_name' => 'Prime Bank Limited', 'short_name' => 'Prime', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'bank_name' => 'Unknown Bank', 'short_name' => 'N/A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'bank_name' => 'No deposit', 'short_name' => 'N/A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 47, 'bank_name' => 'AB Bank Limited', 'short_name' => 'AB', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 48, 'bank_name' => 'Bangladesh Commerce Bank Limited', 'short_name' => 'BCBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 49, 'bank_name' => 'BRAC Bank Limited', 'short_name' => 'BRAC', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 50, 'bank_name' => 'City Bank Limited', 'short_name' => 'City', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 51, 'bank_name' => 'Community Bank Bangladesh Limited', 'short_name' => 'CBBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 52, 'bank_name' => 'Dhaka Bank Limited', 'short_name' => 'Dhaka', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 53, 'bank_name' => 'Eastern Bank Limited', 'short_name' => 'Eastern', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 54, 'bank_name' => 'IFIC Bank Limited', 'short_name' => 'IFIC', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 55, 'bank_name' => 'Jamuna Bank Limited', 'short_name' => 'Jamuna', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 56, 'bank_name' => 'Meghna Bank Limited', 'short_name' => 'Meghna', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 57, 'bank_name' => 'Mercantile Bank Limited', 'short_name' => 'Mercantile', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 58, 'bank_name' => 'Midland Bank Limited', 'short_name' => 'Midland', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 59, 'bank_name' => 'Modhumoti Bank Limited', 'short_name' => 'Modhumoti', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 60, 'bank_name' => 'Mutual Trust Bank Limited', 'short_name' => 'MTBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 61, 'bank_name' => 'National Bank Limited', 'short_name' => 'NBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 62, 'bank_name' => 'National Credit & Commerce Bank Limited', 'short_name' => 'NCCBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 63, 'bank_name' => 'NRB Bank Limited', 'short_name' => 'NRB', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 64, 'bank_name' => 'NRB Commercial Bank Ltd', 'short_name' => 'NRB Commercial', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 65, 'bank_name' => 'NRB Global Bank Ltd', 'short_name' => 'NRB Global', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 66, 'bank_name' => 'One Bank Limited', 'short_name' => 'One Bank', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 67, 'bank_name' => 'Padma Bank Limited', 'short_name' => 'Padma', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 68, 'bank_name' => 'Premier Bank Limited', 'short_name' => 'Premier', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 69, 'bank_name' => 'Pubali Bank Limited', 'short_name' => 'Pubali', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 70, 'bank_name' => 'Standard Bank Limited', 'short_name' => 'Standard', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 71, 'bank_name' => 'Shimanto Bank Ltd', 'short_name' => 'Shimanto', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 72, 'bank_name' => 'Southeast Bank Limited', 'short_name' => 'Southeast', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 73, 'bank_name' => 'South Bangla Agriculture and Commerce Bank Limited', 'short_name' => 'SBACBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 74, 'bank_name' => 'Trust Bank Limited', 'short_name' => 'Trust', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 75, 'bank_name' => 'United Commercial Bank Ltd', 'short_name' => 'UCBL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 76, 'bank_name' => 'Uttara Bank Limited', 'short_name' => 'Uttara', 'created_at' => now(), 'updated_at' => now()]
        ];

        DB::table('banks')->insertOrIgnore($banks);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
