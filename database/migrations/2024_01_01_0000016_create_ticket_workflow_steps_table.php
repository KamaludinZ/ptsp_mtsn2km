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
        Schema::create('ticket_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_workflow_id');
            $table->unsignedBigInteger('workflow_step_id');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Who is assigned to this step
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_workflow_id')->references('id')->on('ticket_workflows')->onDelete('cascade');
            $table->foreign('workflow_step_id')->references('id')->on('workflow_steps')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_workflow_steps');
    }
};