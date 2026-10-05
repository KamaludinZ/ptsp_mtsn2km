<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Riwayat perubahan template notifikasi: the wording before each change. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_template_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_template_id')->constrained()->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_active');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['notification_template_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_template_revisions');
    }
};
