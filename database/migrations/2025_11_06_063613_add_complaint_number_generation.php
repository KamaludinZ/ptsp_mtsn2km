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
        // Update existing complaints to have proper complaint numbers based on type
        $complaints = DB::table('complaints')->whereNull('complaint_number')->get();
        
        foreach ($complaints as $complaint) {
            // Determine prefix based on complaint type
            $prefix = match($complaint->complaint_type ?? $complaint->type) {
                'complaint', 'pengaduan' => 'P',        // P for complaints
                'suggestion', 'saran' => 'S',            // S for suggestions
                'whistleblowing' => 'W',                // W for whistleblowing
                default => 'P'
            };
            
            // Year-month format
            $yearMonth = date('Ym', strtotime($complaint->created_at));
            
            // Get sequence number for this prefix and year-month
            $existingComplaints = DB::table('complaints')
                ->where(function($q) use ($complaint) {
                    $q->where('complaint_type', $complaint->complaint_type)
                      ->orWhere('type', $complaint->type ?? $complaint->complaint_type);
                })
                ->whereYear('created_at', date('Y', strtotime($complaint->created_at)))
                ->whereMonth('created_at', date('m', strtotime($complaint->created_at)))
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            // Find the position of this complaint in the sequence
            $position = 0;
            foreach ($existingComplaints as $index => $existingComplaint) {
                if ($existingComplaint->id == $complaint->id) {
                    $position = $index + 1;
                    break;
                }
            }
            
            if ($position > 0) {
                $sequenceNumber = str_pad($position, 3, '0', STR_PAD_LEFT);
                $newComplaintNumber = "{$prefix}-{$yearMonth}-{$sequenceNumber}";
                
                // Update the complaint
                DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->update(['complaint_number' => $newComplaintNumber]);
            } else {
                // Fallback: use the old method to determine position
                $sequenceNumber = str_pad($complaint->id, 3, '0', STR_PAD_LEFT);
                $newComplaintNumber = "{$prefix}-{$yearMonth}-{$sequenceNumber}";
                
                DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->update(['complaint_number' => $newComplaintNumber]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset complaint numbers
        DB::table('complaints')->update(['complaint_number' => null]);
    }
};
