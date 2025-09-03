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
        // Check if the table exists before creating it
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->integer('account_no')->nullable();
                $table->string('title');
                $table->unsignedBigInteger('account_type_id')->nullable();
                $table->string('accountable_type');
                $table->unsignedBigInteger('accountable_id');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('status')->nullable();
                $table->integer('_lft')->nullable();
                $table->integer('_rgt')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('root_type')->nullable()->comment('1-Assets 2-Expenses 3-Liabilities 4-Income 5-Capital');
                $table->string('financial_statement_placement')->nullable();
                $table->timestamps();

                // Add indexes to foreign key columns
                $table->index('company_id');
                $table->index('account_type_id');
                $table->index('accountable_id');
                $table->index('parent_id');
            });
        } else {
            // If the table already exists, check for columns
            Schema::table('accounts', function (Blueprint $table) {
                // Add columns if they do not already exist
                if (!Schema::hasColumn('accounts', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'account_no')) {
                    $table->string('account_no')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'title')) {
                    $table->string('title');
                }
                if (!Schema::hasColumn('accounts', 'account_type_id')) {
                    $table->unsignedBigInteger('account_type_id')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'accountable_type')) {
                    $table->string('accountable_type');
                }
                if (!Schema::hasColumn('accounts', 'accountable_id')) {
                    $table->unsignedBigInteger('accountable_id');
                }
                if (!Schema::hasColumn('accounts', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('accounts', '_lft')) {
                    $table->integer('_lft')->nullable();
                }
                if (!Schema::hasColumn('accounts', '_rgt')) {
                    $table->integer('_rgt')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'root_type')) {
                    $table->string('root_type')->nullable();
                }
                if (!Schema::hasColumn('accounts', 'financial_statement_placement')) {
                    $table->string('financial_statement_placement')->nullable();
                }

                // Add indexes if they don't exist
                // if (!Schema::hasIndex('accounts', 'company_id')) {
                //     $table->index('company_id');
                // }
                // if (!Schema::hasIndex('accounts', 'account_type_id')) {
                //     $table->index('account_type_id');
                // }
                // if (!Schema::hasIndex('accounts', 'accountable_id')) {
                //     $table->index('accountable_id');
                // }
                // if (!Schema::hasIndex('accounts', 'parent_id')) {
                //     $table->index('parent_id');
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
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
                $table->dropIndex(['account_type_id']);
                $table->dropIndex(['accountable_id']);
                $table->dropIndex(['parent_id']);
            });
        }
        
        Schema::dropIfExists('accounts');
    }
};
