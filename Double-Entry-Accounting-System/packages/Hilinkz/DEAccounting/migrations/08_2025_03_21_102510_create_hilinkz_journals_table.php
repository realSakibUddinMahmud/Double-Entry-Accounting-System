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
        if (!Schema::hasTable('de_journals')) {
            Schema::create('de_journals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->date('date');
                $table->decimal('amount', 15, 2);
                $table->unsignedBigInteger('credit_transaction_id')->nullable();
                $table->unsignedBigInteger('debit_transaction_id')->nullable();
                $table->unsignedBigInteger('task_id')->nullable();
                $table->string('transaction_type')->nullable()->comment('event');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('journalable_id')->nullable();
                $table->string('journalable_type')->nullable();
                $table->timestamps();

                // Add indexes for foreign key columns
                $table->index('company_id');
                $table->index('credit_transaction_id');
                $table->index('debit_transaction_id');
                $table->index('task_id');
            });
        } else {
            // Check and add missing columns if they don’t exist
            Schema::table('de_journals', function (Blueprint $table) {
                if (!Schema::hasColumn('de_journals', 'company_id')) {
                    $table->unsignedBigInteger('company_id');
                }
                if (!Schema::hasColumn('de_journals', 'date')) {
                    $table->date('date');
                }
                if (!Schema::hasColumn('de_journals', 'amount')) {
                    $table->decimal('amount', 15, 2);
                }
                if (!Schema::hasColumn('de_journals', 'credit_transaction_id')) {
                    $table->unsignedBigInteger('credit_transaction_id')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'debit_transaction_id')) {
                    $table->unsignedBigInteger('debit_transaction_id')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'task_id')) {
                    $table->unsignedBigInteger('task_id')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'transaction_type')) {
                    $table->string('transaction_type')->nullable()->comment('event');
                }
                if (!Schema::hasColumn('de_journals', 'created_by')) {
                    $table->unsignedBigInteger('created_by');
                }
                if (!Schema::hasColumn('de_journals', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'journalable_id')) {
                    $table->unsignedBigInteger('journalable_id')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'journalable_type')) {
                    $table->string('journalable_type')->nullable();
                }
                if (!Schema::hasColumn('de_journals', 'created_at')) {
                    $table->timestamps();
                }

                // Add indexes for foreign key columns if they don’t exist
                // if (!Schema::hasIndex('de_journals', 'company_id_index')) {
                //     $table->index('company_id');
                // }
                // if (!Schema::hasIndex('de_journals', 'credit_transaction_id_index')) {
                //     $table->index('credit_transaction_id');
                // }
                // if (!Schema::hasIndex('de_journals', 'debit_transaction_id_index')) {
                //     $table->index('debit_transaction_id');
                // }
                // if (!Schema::hasIndex('de_journals', 'task_id_index')) {
                //     $table->index('task_id');
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('de_journals');
    }
};
