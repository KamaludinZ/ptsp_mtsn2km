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
        Schema::create('ticket_outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->enum('output_type', ['digital', 'physical']);
            $table->string('file_path')->nullable(); // Path to output file if digital
            $table->text('output_description')->nullable(); // Description of output
            $table->boolean('is_delivered')->default(false);
            $table->date('delivery_date')->nullable();
            $table->enum('delivery_method', ['email', 'whatsapp', 'physical_collection'])->nullable();
            $table->unsignedBigInteger('delivered_to')->nullable(); // Who received the output
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('delivered_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_outputs');
    }
};