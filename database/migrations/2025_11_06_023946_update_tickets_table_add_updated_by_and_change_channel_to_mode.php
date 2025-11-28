<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Since SQLite doesn't support ALTER TABLE DROP COLUMN, 
        // we need to recreate the table with the new structure
        
        // Get all ticket data
        $tickets = DB::table('tickets')->get();
        
        // Drop foreign key constraints temporarily
        DB::statement('PRAGMA foreign_keys = 0');
        
        // Create new table with updated structure
        Schema::dropIfExists('tickets_new');
        Schema::create('tickets_new', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format: LAYANAN-YYYYMM-XXX
            $table->unsignedBigInteger('user_id'); // The applicant
            $table->unsignedBigInteger('service_id');
            $table->string('mode', 20)->default('online'); // Changed from channel to mode
            $table->enum('status', ['submitted', 'verified', 'in_process', 'approved', 'rejected', 'completed', 'cancelled'])->default('submitted');
            $table->unsignedBigInteger('current_handler_id')->nullable(); // Current person handling the ticket
            $table->unsignedBigInteger('assigned_to_id')->nullable(); // Who the ticket is assigned to
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->unsignedBigInteger('created_by'); // Usually same as user_id, but for offline tickets might be front-desk staff
            $table->unsignedBigInteger('updated_by')->nullable(); // New column for tracking who updated
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('current_handler_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
        
        // Insert old data into new table
        foreach ($tickets as $ticket) {
            DB::table('tickets_new')->insert([
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $ticket->user_id,
                'service_id' => $ticket->service_id,
                'mode' => $ticket->channel,  // Map old channel to new mode
                'status' => $ticket->status,
                'current_handler_id' => $ticket->current_handler_id,
                'assigned_to_id' => $ticket->assigned_to_id,
                'priority' => $ticket->priority,
                'notes' => $ticket->notes,
                'estimated_completion_date' => $ticket->estimated_completion_date,
                'actual_completion_date' => $ticket->actual_completion_date,
                'is_urgent' => $ticket->is_urgent,
                'created_by' => $ticket->created_by,
                'updated_by' => null,  // Set to null initially since this is new
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'deleted_at' => $ticket->deleted_at,
            ]);
        }
        
        // Drop old table and rename new table
        Schema::dropIfExists('tickets');
        Schema::rename('tickets_new', 'tickets');
        
        DB::statement('PRAGMA foreign_keys = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original structure
        $tickets = DB::table('tickets')->get();
        
        DB::statement('PRAGMA foreign_keys = 0');
        
        Schema::dropIfExists('tickets_old');
        Schema::create('tickets_old', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format: LAYANAN-YYYYMM-XXX
            $table->unsignedBigInteger('user_id'); // The applicant
            $table->unsignedBigInteger('service_id');
            $table->enum('channel', ['online', 'offline']);  // Original column name and values
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
        
        // Insert data back, mapping mode back to channel
        foreach ($tickets as $ticket) {
            DB::table('tickets_old')->insert([
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $ticket->user_id,
                'service_id' => $ticket->service_id,
                'channel' => $ticket->mode,  // Map mode back to channel
                'status' => $ticket->status,
                'current_handler_id' => $ticket->current_handler_id,
                'assigned_to_id' => $ticket->assigned_to_id,
                'priority' => $ticket->priority,
                'notes' => $ticket->notes,
                'estimated_completion_date' => $ticket->estimated_completion_date,
                'actual_completion_date' => $ticket->actual_completion_date,
                'is_urgent' => $ticket->is_urgent,
                'created_by' => $ticket->created_by,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'deleted_at' => $ticket->deleted_at,
            ]);
        }
        
        Schema::dropIfExists('tickets');
        Schema::rename('tickets_old', 'tickets');
        
        DB::statement('PRAGMA foreign_keys = 1');
    }
};
