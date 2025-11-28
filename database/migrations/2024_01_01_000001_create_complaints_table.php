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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->enum('complaint_type', ['complaint', 'suggestion', 'whistleblowing']);
            $table->string('title');
            $table->text('description');
            $table->string('complainant_name')->nullable(); // Name of person making complaint
            $table->string('complainant_contact')->nullable(); // Contact information
            $table->string('complainant_email')->nullable(); // Email for follow-up
            $table->unsignedBigInteger('user_id')->nullable(); // If logged in user made complaint
            $table->enum('status', ['submitted', 'in_review', 'in_progress', 'resolved', 'closed'])->default('submitted');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Who is handling the complaint
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->boolean('anonymous')->default(false);
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};