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
        Schema::create('product_store', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('base_unit_id');
            $table->unsignedBigInteger('purchase_unit_id');
            $table->unsignedBigInteger('sales_unit_id');
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('cogs', 15, 2)->default(0);
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->enum('tax_method', ['exclusive', 'inclusive'])->default('exclusive');
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('base_unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('purchase_unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('sales_unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_store');
    }
};
