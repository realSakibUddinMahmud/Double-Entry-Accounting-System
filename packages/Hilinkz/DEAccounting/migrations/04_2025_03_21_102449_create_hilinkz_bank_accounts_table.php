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
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->unsignedBigInteger('bank_id');
                $table->string('account_no', 50);
                $table->string('account_name', 100);
                $table->string('branch', 100)->nullable();
                $table->string('status')->nullable();
                $table->timestamps();

                // Add indexes to foreign key columns
                $table->index('account_id');
                $table->index('bank_id');
            });
        } else {
            // Add missing columns if they don’t exist
            Schema::table('bank_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('bank_accounts', 'account_id')) {
                    $table->unsignedBigInteger('account_id')->nullable();
                }
                if (!Schema::hasColumn('bank_accounts', 'bank_id')) {
                    $table->unsignedBigInteger('bank_id');
                }
                if (!Schema::hasColumn('bank_accounts', 'account_no')) {
                    $table->string('account_no', 50);
                }
                if (!Schema::hasColumn('bank_accounts', 'account_name')) {
                    $table->string('account_name', 100);
                }
                if (!Schema::hasColumn('bank_accounts', 'branch')) {
                    $table->string('branch', 100)->nullable();
                }
                if (!Schema::hasColumn('bank_accounts', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('bank_accounts', 'created_at')) {
                    $table->timestamps();
                }

                // Add indexes to foreign key columns if not already present
                // if (!Schema::hasIndex('bank_accounts', 'account_id')) {
                //     $table->index('account_id');
                // }
                // if (!Schema::hasIndex('bank_accounts', 'bank_id')) {
                //     $table->index('bank_id');
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the indexes before dropping the table
        if (Schema::hasTable('bank_accounts')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropIndex(['account_id']);
                $table->dropIndex(['bank_id']);
            });
        }

        Schema::dropIfExists('bank_accounts');
    }
};
