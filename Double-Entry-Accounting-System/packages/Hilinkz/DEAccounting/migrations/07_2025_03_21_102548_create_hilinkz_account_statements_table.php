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
        if (!Schema::hasTable('account_statements')) {
            Schema::create('account_statements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('account_id');
                $table->date('date');
                $table->decimal('closing_balance', 15, 2);
                $table->text('note')->nullable();
                $table->timestamps();

                // Add indexes for foreign key columns
                $table->index('company_id');
                $table->index('account_id');
            });
        } else {
            Schema::table('account_statements', function (Blueprint $table) {
                if (!Schema::hasColumn('account_statements', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable();
                }
                if (!Schema::hasColumn('account_statements', 'account_id')) {
                    $table->unsignedBigInteger('account_id');
                }
                if (!Schema::hasColumn('account_statements', 'date')) {
                    $table->date('date');
                }
                if (!Schema::hasColumn('account_statements', 'closing_balance')) {
                    $table->decimal('closing_balance', 15, 2);
                }
                if (!Schema::hasColumn('account_statements', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('account_statements', 'created_at')) {
                    $table->timestamps();
                }

                // Add indexes for foreign key columns
                // if (!Schema::hasIndex('account_statements', 'company_id_index')) {
                //     $table->index('company_id');
                // }
                // if (!Schema::hasIndex('account_statements', 'account_id_index')) {
                //     $table->index('account_id');
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_statements');
    }
};
