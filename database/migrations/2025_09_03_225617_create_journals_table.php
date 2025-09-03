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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('credit_transaction_id');
            $table->unsignedBigInteger('debit_transaction_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('transaction_type');
            $table->unsignedBigInteger('created_by');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index(['company_id']);
            $table->index(['date']);
            $table->index(['credit_transaction_id']);
            $table->index(['debit_transaction_id']);
            $table->index(['task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
