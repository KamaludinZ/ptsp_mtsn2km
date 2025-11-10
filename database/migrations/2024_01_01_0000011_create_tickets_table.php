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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format: LAYANAN-YYYYMM-XXX
            $table->unsignedBigInteger('user_id'); // The applicant
            $table->unsignedBigInteger('service_id');
            $table->enum('channel', ['online', 'offline']);
            $table->enum('status', ['submitted', 'verified', 'in_process', 'approved', 'rejected', 'completed', 'cancelled'])->default('submitted');
            $table->unsignedBigInteger('current_handler_id')->nullable(); // Current person handling the ticket
            $table->unsignedBigInteger('assigned_to_id')->nullable(); // Who the ticket is assigned to
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->unsignedBigInteger('created_by'); // Usually same as user_id, but for offline tickets might be front-desk staff
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('current_handler_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};