<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('landlord')->create('user_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('otp', 5)->nullable();
            $table->string('purpose')->default('password_reset');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('landlord')->dropIfExists('user_otps');
    }
}
