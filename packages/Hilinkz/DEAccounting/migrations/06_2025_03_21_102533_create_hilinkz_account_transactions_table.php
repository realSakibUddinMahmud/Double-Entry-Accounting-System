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
        if (!Schema::hasTable('account_transactions')) {
            Schema::create('account_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('account_id');
                $table->decimal('amount', 15, 2);
                $table->date('date');
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('type');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('account_transactionable_id')->nullable();
                $table->string('account_transactionable_type')->nullable();
                $table->timestamps();

                // Foreign key indexes for better performance on joins
                $table->index('company_id');
                $table->index('account_id');
                $table->index('created_by');
                $table->index('account_transactionable_id');
                $table->index('account_transactionable_type');
            });
        } else {
            Schema::table('account_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('account_transactions', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable();
                }
                if (!Schema::hasColumn('account_transactions', 'account_id')) {
                    $table->unsignedBigInteger('account_id');
                }
                if (!Schema::hasColumn('account_transactions', 'amount')) {
                    $table->decimal('amount', 15, 2);
                }
                if (!Schema::hasColumn('account_transactions', 'date')) {
                    $table->date('date');
                }
                if (!Schema::hasColumn('account_transactions', 'debit')) {
                    $table->decimal('debit', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('account_transactions', 'credit')) {
                    $table->decimal('credit', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('account_transactions', 'type')) {
                    $table->string('type');
                }
                if (!Schema::hasColumn('account_transactions', 'created_by')) {
                    $table->unsignedBigInteger('created_by');
                }
                if (!Schema::hasColumn('account_transactions', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('account_transactions', 'account_transactionable_id')) {
                    $table->unsignedBigInteger('account_transactionable_id')->nullable();
                }
                if (!Schema::hasColumn('account_transactions', 'account_transactionable_type')) {
                    $table->string('account_transactionable_type')->nullable();
                }
                if (!Schema::hasColumn('account_transactions', 'created_at')) {
                    $table->timestamps();
                }

                // Add foreign key indexes for better performance on joins
                // if (!Schema::hasIndex('account_transactions', 'company_id_index')) {
                //     $table->index('company_id');
                // }
                // if (!Schema::hasIndex('account_transactions', 'account_id_index')) {
                //     $table->index('account_id');
                // }
                // if (!Schema::hasIndex('account_transactions', 'created_by_index')) {
                //     $table->index('created_by');
                // }
                // if (!Schema::hasIndex('account_transactions', 'account_transactionable_id_index')) {
                //     $table->index('account_transactionable_id');
                // }
                // if (!Schema::hasIndex('account_transactions', 'account_transactionable_type_index')) {
                //     $table->index('account_transactionable_type');
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
