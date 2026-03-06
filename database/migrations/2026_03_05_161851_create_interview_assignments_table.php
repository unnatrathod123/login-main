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
        Schema::create('interview_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_code')->unique();

            $table->foreignId('interview_batch_id')
                ->constrained('interview_batches')
                ->cascadeOnDelete();

            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();

            // Attendance
            $table->enum('attendance', ['present','absent'])->nullable();

            // Evaluation
            $table->integer('problem_solving')->nullable();
            $table->integer('communication')->nullable();
            $table->decimal('overall_score', 5,2)->nullable();

            $table->text('remarks')->nullable();

            $table->enum('result',['pending','selected','rejected'])
                ->default('pending')
                ->index();

            $table->timestamps();

             // Prevent duplicate assignment
            $table->unique(
                ['interview_batch_id','application_id'],
                'batch_app_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_assignments');
    }
};
