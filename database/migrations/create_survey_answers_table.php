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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_response_id');
            $table->unsignedBigInteger('survey_question_id');
            $table->text('answer_text')->nullable(); // For text responses
            $table->integer('rating_value')->nullable(); // For rating responses (1-5 or 1-10)
            $table->string('selected_option')->nullable(); // For multiple choice
            $table->timestamps();
            
            $table->foreign('survey_response_id')->references('id')->on('survey_responses')->onDelete('cascade');
            $table->foreign('survey_question_id')->references('id')->on('survey_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};