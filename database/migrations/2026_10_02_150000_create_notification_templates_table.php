<?php

use App\Support\NotificationTemplates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Template pesan notifikasi: one template per event and channel, each with
 * its own trigger switch. Seeded with the default wording.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50);
            $table->string('channel', 20);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'channel']);
        });

        $now = now();
        $rows = [];
        foreach (NotificationTemplates::defaults() as $key => $template) {
            foreach (['email', 'whatsapp'] as $channel) {
                $rows[] = [
                    'key' => $key,
                    'channel' => $channel,
                    'subject' => $channel === 'email' ? $template['subject'] : null,
                    'body' => $template['body'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('notification_templates')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
