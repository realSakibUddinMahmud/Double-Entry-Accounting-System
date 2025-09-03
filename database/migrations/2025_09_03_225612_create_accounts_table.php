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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('account_no');
            $table->string('title');
            $table->unsignedBigInteger('account_type_id')->nullable();
            $table->string('accountable_type')->nullable();
            $table->unsignedBigInteger('accountable_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('_lft');
            $table->unsignedInteger('_rgt');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('root_type')->nullable();
            $table->string('financial_statement_placement')->nullable();
            $table->timestamps();
            
            $table->index(['company_id']);
            $table->index(['account_type_id']);
            $table->index(['accountable_type', 'accountable_id']);
            $table->index(['parent_id']);
            $table->index(['_lft', '_rgt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
