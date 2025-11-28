<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We'll update all existing tickets to follow the new format based on their mode
        $tickets = DB::table('tickets')->get();
        
        foreach ($tickets as $ticket) {
            // Convert the existing ticket number to the new format based on mode
            $prefix = match($ticket->mode) {
                'online' => 'N',    // N for online
                'offline' => 'F',   // F for offline
                'hybrid' => 'H',    // H for hybrid
                default => 'N'
            };
            
            $yearMonth = date('Ym', strtotime($ticket->created_at));
            
            // Get all tickets for this mode and year-month to determine sequence
            $existingTickets = DB::table('tickets')
                ->where('mode', $ticket->mode)
                ->whereYear('created_at', date('Y', strtotime($ticket->created_at)))
                ->whereMonth('created_at', date('m', strtotime($ticket->created_at)))
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            // Find the position of this ticket in the sequence
            $position = 0;
            foreach ($existingTickets as $index => $existingTicket) {
                if ($existingTicket->id == $ticket->id) {
                    $position = $index + 1;
                    break;
                }
            }
            
            if ($position > 0) {
                $sequenceNumber = str_pad($position, 3, '0', STR_PAD_LEFT);
                $newTicketNumber = "{$prefix}-{$yearMonth}-{$sequenceNumber}";
                
                // Update the ticket
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['ticket_number' => $newTicketNumber]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old format (this is a simplified revert which may not be accurate)
        // In a real scenario, you might want to backup original numbers before updating
        $tickets = DB::table('tickets')->get();
        
        foreach ($tickets as $ticket) {
            // For the revert, we'll keep the same format for simplicity since we're not storing the original number
            // In a real scenario, you'd want to store the original number before migration
            $oldPrefix = match($ticket->mode) {
                'online' => 'N',
                'offline' => 'F', 
                'hybrid' => 'H',
                default => 'N'
            };
            
            // For demonstration purposes, this is a simplified revert
            // A proper revert would require storing original numbers before migration
        }
    }
};
