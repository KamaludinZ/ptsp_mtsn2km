<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Riwayat notifikasi: every e-mail / WhatsApp message the application tried to send, with its outcome. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('event', 50);
            $table->string('channel', 20); // email | whatsapp | database
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // recipient
            $table->string('recipient')->nullable(); // address or number used
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 20); // sent | failed
            $table->text('error')->nullable();
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
