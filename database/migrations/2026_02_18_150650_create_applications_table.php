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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();


            // Column added on 02/03/2026 
            // ALTER TABLE applications 
            // ADD application_id VARCHAR(20) UNIQUE AFTER id;
             $table->string('application_id')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

              // application fields (initially NULL)
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('college')->nullable();
            $table->string('degree')->nullable();
            $table->string('last_exam_appeared')->nullable(); 
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->string('domain')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['months', 'days', 'hours'])
                        ->default('months')
                        ->nullable();
            $table->enum('source', ['website', 'search', 'social','linkedin','friend','college'])
                        ->default('website')
                        ->nullable();      
            $table->string('skills')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('status')->default('applied');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
