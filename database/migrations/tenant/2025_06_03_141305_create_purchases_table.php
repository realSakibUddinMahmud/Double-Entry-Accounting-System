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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('u_id')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('store_id'); // Add store_id
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->string('payment_status')->default('pending'); // pending, paid, partial
            $table->unsignedBigInteger('user_id');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade'); // Store foreign key
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
