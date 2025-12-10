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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('institution'); // From/representing which institution
            $table->text('purpose'); // Purpose of visit
            $table->string('person_to_meet'); // Who they want to meet
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->string('photo_path')->nullable(); // Path to visitor photo
            $table->string('visitor_card_number')->nullable()->unique();
            $table->enum('status', ['active', 'checked_out'])->default('active');
            $table->unsignedBigInteger('created_by'); // Staff who registered the visitor
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};