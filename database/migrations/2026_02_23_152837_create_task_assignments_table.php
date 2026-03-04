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
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Individual assignment
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Team assignment (optional)
            $table->foreignId('team_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Batch assignment (optional)
            $table->foreignId('batch_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
