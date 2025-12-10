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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->integer('step_number'); // Order of the step
            $table->string('name'); // Name of the step
            $table->text('description')->nullable();
            $table->string('required_role')->nullable(); // Role required to complete this step
            $table->integer('estimated_duration_days')->default(1);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
            
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};