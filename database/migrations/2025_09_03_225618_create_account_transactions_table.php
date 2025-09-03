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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('type');
            $table->unsignedBigInteger('created_by');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('account_transactionable_id')->nullable();
            $table->string('account_transactionable_type')->nullable();
            $table->timestamps();
            
            $table->index(['company_id']);
            $table->index(['account_id']);
            $table->index(['date']);
            $table->index(['account_transactionable_type', 'account_transactionable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
