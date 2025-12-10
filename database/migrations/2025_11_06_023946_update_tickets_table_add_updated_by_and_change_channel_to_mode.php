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
        // Get the database connection driver
        $driver = DB::getDriverName();
        
        // For PostgreSQL, we can't directly rename columns, so we need to recreate the table
        if ($driver === 'pgsql') {
            // Get all ticket data
            $tickets = DB::table('tickets')->get();

            // Store foreign key constraints that need to be recreated
            $foreignKeyConstraints = [];
            
            // Get existing foreign keys for recreation after table recreation
            $existingForeignKeys = DB::select("
                SELECT 
                    tc.table_name, 
                    tc.constraint_name, 
                    tc.table_schema,
                    kcu.column_name,
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                        ON tc.constraint_name = kcu.constraint_name
                        AND tc.table_schema = kcu.table_schema
                    JOIN information_schema.constraint_column_usage AS ccu
                        ON ccu.constraint_name = tc.constraint_name
                        AND ccu.table_schema = tc.table_schema
                WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name = 'tickets'
            ");

            // Drop foreign key constraints temporarily
            foreach ($existingForeignKeys as $fk) {
                try {
                    DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS {$fk->constraint_name}");
                } catch (\Exception $e) {
                    // Ignore errors if constraint doesn't exist
                }
            }

            // Create new table with updated structure
            Schema::dropIfExists('tickets_temp');
            Schema::create('tickets_temp', function (Blueprint $table) {
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

                // Define foreign keys in new table (we'll add them after data transfer)
            });

            // Insert old data into new table with column mapping
            foreach ($tickets as $ticket) {
                DB::table('tickets_temp')->insert([
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_id' => $ticket->user_id,
                    'service_id' => $ticket->service_id,
                    'mode' => $ticket->channel,  // Map old 'channel' to new 'mode'
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

            // Now drop the old table and rename the new one
            Schema::dropIfExists('tickets_backup');
            Schema::rename('tickets', 'tickets_backup');
            Schema::rename('tickets_temp', 'tickets');

            // Recreate foreign key constraints
            foreach ($existingForeignKeys as $fk) {
                try {
                    $foreignTableName = $fk->foreign_table_name;
                    $foreignColumnName = $fk->foreign_column_name;
                    
                    // Add foreign key with proper naming
                    DB::statement("ALTER TABLE tickets ADD CONSTRAINT tickets_{$fk->column_name}_foreign FOREIGN KEY ({$fk->column_name}) REFERENCES {$foreignTableName}({$foreignColumnName}) ON DELETE CASCADE");
                } catch (\Exception $e) {
                    // Log the error but continue
                    \Log::warning("Could not recreate foreign key: " . $e->getMessage());
                }
            }
        } 
        // For SQLite (uses PRAGMA commands)
        else if ($driver === 'sqlite') {
            $tickets = DB::table('tickets')->get();

            DB::statement('PRAGMA foreign_keys = 0');

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

            // Insert old data into new table with column mapping
            foreach ($tickets as $ticket) {
                DB::table('tickets_new')->insert([
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_id' => $ticket->user_id,
                    'service_id' => $ticket->service_id,
                    'mode' => $ticket->channel,  // Map old 'channel' to new 'mode'
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

            Schema::dropIfExists('tickets');
            Schema::rename('tickets_new', 'tickets');

            DB::statement('PRAGMA foreign_keys = 1');
        }
        // For MySQL
        else {
            // Direct approach for MySQL - change column names
            Schema::table('tickets', function (Blueprint $table) {
                $table->renameColumn('channel', 'mode');
                $table->string('mode', 20)->default('online')->change();
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // Get all ticket data
            $tickets = DB::table('tickets')->get();

            // Store foreign keys
            $existingForeignKeys = DB::select("
                SELECT 
                    tc.table_name, 
                    tc.constraint_name, 
                    tc.table_schema,
                    kcu.column_name,
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                        ON tc.constraint_name = kcu.constraint_name
                        AND tc.table_schema = kcu.table_schema
                    JOIN information_schema.constraint_column_usage AS ccu
                        ON ccu.constraint_name = tc.constraint_name
                        AND ccu.table_schema = tc.table_schema
                WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name = 'tickets'
            ");

            // Drop foreign key constraints temporarily
            foreach ($existingForeignKeys as $fk) {
                try {
                    DB::statement("ALTER TABLE tickets DROP CONSTRAINT IF EXISTS {$fk->constraint_name}");
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }

            // Create backup table with old structure
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
                // No updated_by column (removed)
                $table->softDeletes();
                $table->timestamps();

                // Foreign keys will be added after data transfer
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

            // Recreate foreign key constraints
            // (Similar to up method, recreate foreign keys here)
        } 
        else if ($driver === 'sqlite') {
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
                // No updated_by column (removed)
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
        else {
            // MySQL approach
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('updated_by');
                $table->renameColumn('mode', 'channel');
                $table->enum('channel', ['online', 'offline'])->change();
            });
        }
    }
};