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
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('approval_required')->default(true)->after('whatsapp_number');
            $table->boolean('is_approved')->default(false)->after('approval_required');
            $table->unsignedBigInteger('approved_by')->nullable()->after('is_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            // Add foreign key for approved_by
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_required',
                'is_approved', 
                'approved_by',
                'approved_at',
                'approval_notes'
            ]);
        });
    }
};
