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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

             $table->string('title');
            $table->text('description');
             // 👇 THIS is where intern_id belongs
            $table->foreignId('intern_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->date('deadline');
            $table->enum('status', ['assigned', 'submitted', 'approved', 'rejected'])
                ->default('assigned');
            $table->string('submission_file')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('feedback')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
