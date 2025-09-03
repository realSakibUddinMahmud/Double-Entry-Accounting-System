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
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('path');
                $table->string('fileable_type');
                $table->unsignedBigInteger('fileable_id');
                $table->timestamps();
            });
        } else {
            Schema::table('files', function (Blueprint $table) {
                if (!Schema::hasColumn('files', 'title')) {
                    $table->string('title');
                }
                if (!Schema::hasColumn('files', 'path')) {
                    $table->string('path');
                }
                if (!Schema::hasColumn('files', 'fileable_type')) {
                    $table->string('fileable_type');
                }
                if (!Schema::hasColumn('files', 'fileable_id')) {
                    $table->unsignedBigInteger('fileable_id');
                }
                if (!Schema::hasColumn('files', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
