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
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name');
                $table->unsignedBigInteger('taskable_id')->nullable();
                $table->string('taskable_type')->nullable(); // For polymorphic relation
                $table->text('note')->nullable();
                $table->timestamps();

                // Add indexes to foreign key columns
                $table->index('company_id');
                $table->index('taskable_id');
                $table->index('taskable_type'); // Index for the polymorphic relation
            });
        } else {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('tasks', 'taskable_id')) {
                    $table->unsignedBigInteger('taskable_id')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'taskable_type')) {
                    $table->string('taskable_type')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'created_at')) {
                    $table->timestamps();
                }

                // Add indexes to foreign key columns if they do not exist
                // if (!Schema::hasIndex('tasks', 'company_id')) {
                //     $table->index('company_id');
                // }
                // if (!Schema::hasIndex('tasks', 'taskable_id')) {
                //     $table->index('taskable_id');
                // }
                // if (!Schema::hasIndex('tasks', 'taskable_type')) {
                //     $table->index('taskable_type'); // Index for polymorphic relation
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes before dropping the table
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
                $table->dropIndex(['taskable_id']);
                $table->dropIndex(['taskable_type']); // Drop the polymorphic index
            });
        }

        Schema::dropIfExists('tasks');
    }
};
