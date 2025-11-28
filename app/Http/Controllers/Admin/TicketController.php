<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query();
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }
        
        // Apply approval status filter
        if ($request->filled('approval_status')) {
            switch ($request->approval_status) {
                case 'approved':
                    $query->where('is_approved', true);
                    break;
                case 'not_approved':
                    $query->where('is_approved', false)->where('approval_required', true);
                    break;
                case 'pending_approval':
                    $query->where('is_approved', false)
                          ->where('approval_required', true)
                          ->whereNull('approved_at');
                    break;
                case 'approval_not_required':
                    $query->where('approval_required', false);
                    break;
            }
        }

        $tickets = $query->with(['user', 'service'])->latest()->paginate(15);
        $services = Service::all();
        
        return view('admin.tickets.index', compact('tickets', 'services'));
    }

    public function create()
    {
        $users = User::all();
        $services = Service::all();
        
        return view('admin.tickets.create', compact('users', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id', // Allow null for manual entry
            'applicant_name' => 'required_without:user_id|string|max:255', // Require when user_id is not provided
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:pending,in_process,pending_approval,approved,completed,rejected',
            'priority' => 'required|in:low,normal,high,urgent',
            'mode' => 'required|in:online,offline,hybrid',
            'email' => 'nullable|email|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'requirement_files' => 'array',
            'requirement_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB per file
        ]);

        // If no user_id is provided, create or find a default user or use guest user concept
        if (empty($request->user_id)) {
            // Create a temporary user or use a system guest user
            // For now, let's create a guest user or try to find an existing user with same name/email
            $user = User::where('name', $request->applicant_name)
                        ->when($request->email, function($query) use ($request) {
                            return $query->where('email', $request->email);
                        })
                        ->first();
            
            if (!$user) {
                // Create a temporary user
                $user = User::create([
                    'name' => $request->applicant_name,
                    'email' => $request->email ?? 'guest_' . time() . '@example.com',
                    'password' => bcrypt('temporary_password_' . time()), // Temporary password
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            $user = User::findOrFail($request->user_id);
        }

        // Auto-detect mode based on service
        $service = Service::findOrFail($request->service_id);
        $mode = $request->mode ?? $service->mode;

        // Check if approval is required
        $approvalRequired = $service->approval_required ?? true;

        // Set default status to 'in_process' and priority to 'normal' for admin/agent created tickets
        $status = $request->status;
        $priority = $request->priority;

        // For admin/agent created tickets, set defaults if not provided
        if (empty($status)) {
            if ($approvalRequired) {
                $status = Ticket::STATUS_PENDING_APPROVAL; // Default to pending approval if approval required
            } else {
                $status = Ticket::STATUS_IN_PROCESS; // Default to in_process
            }
        }
        if (empty($priority)) {
            $priority = Ticket::PRIORITY_NORMAL; // Default to normal
        }

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'mode' => $mode,
            'status' => $status,
            'priority' => $priority,
            'notes' => $request->notes,
            'approval_required' => $approvalRequired,
            'is_approved' => false, // Not approved initially
            'created_by' => auth()->id(),
            'email' => $request->email, // Add email field
            'whatsapp_number' => $request->whatsapp_number, // Add whatsapp number field
        ]);

        // Handle file uploads if any
        if ($request->hasFile('requirement_files')) {
            foreach ($request->file('requirement_files') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->store('ticket_requirements/' . $ticket->id, 'public');

                    // Create ticket file record
                    $ticket->files()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        return redirect()->route('suadmin.tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'user', 
            'service', 
            'assignedTo',
            'files',  // Load ticket files
            'output', // Load latest ticket output
            'outputs' // Load all ticket outputs
        ]);
        
        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $users = User::all();
        $services = Service::all();
        
        return view('admin.tickets.edit', compact('ticket', 'users', 'services'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:pending,in_process,pending_approval,approved,completed,rejected',
            'priority' => 'required|in:low,normal,high,urgent',
            'mode' => 'required|in:online,offline,hybrid',
            'email' => 'nullable|email|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $ticket->update([
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'mode' => $request->mode,
            'status' => $request->status,
            'priority' => $request->priority,
            'notes' => $request->notes,
            'email' => $request->email,
            'whatsapp_number' => $request->whatsapp_number,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('suadmin.tickets.index')->with('success', 'Ticket updated successfully.');
    }

    /**
     * Upload requirement file for the ticket
     */
    public function uploadRequirement(Request $request, Ticket $ticket)
    {
        $request->validate([
            'requirement_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
        ]);

        $file = $request->file('requirement_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->store('ticket_requirements/' . $ticket->id, 'public');

        // Create new ticket file record
        $ticket->files()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Berkas persyaratan berhasil diupload.');
    }

    /**
     * Upload output document for the ticket
     */
    public function uploadOutput(Request $request, Ticket $ticket)
    {
        $request->validate([
            'output_type' => 'required|in:digital,physical',
            'output_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
            'output_description' => 'nullable|string|max:500',
        ]);

        $outputData = [
            'output_type' => $request->output_type,
            'output_description' => $request->output_description,
        ];

        if ($request->hasFile('output_file')) {
            $file = $request->file('output_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->store('ticket_outputs/' . $ticket->id, 'public');
            $outputData['file_path'] = $path;
        }

        $ticket->outputs()->create($outputData);

        return redirect()->back()->with('success', 'Dokumen hasil berhasil diupload.');
    }

    /**
     * Edit output document
     */
    public function editOutput(Ticket $ticket, $outputId)
    {
        $output = $ticket->outputs()->findOrFail($outputId);
        
        return view('admin.tickets.edit_output', compact('ticket', 'output'));
    }

    /**
     * Update output document
     */
    public function updateOutput(Request $request, Ticket $ticket, $outputId)
    {
        $output = $ticket->outputs()->findOrFail($outputId);
        
        $request->validate([
            'output_type' => 'required|in:digital,physical',
            'output_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'output_description' => 'nullable|string|max:500',
            'is_delivered' => 'boolean',
            'delivery_date' => 'nullable|date',
            'delivered_to' => 'nullable|exists:users,id',
        ]);

        $outputData = [
            'output_type' => $request->output_type,
            'output_description' => $request->output_description,
            'is_delivered' => $request->is_delivered ?? false,
            'delivery_date' => $request->delivery_date,
            'delivered_to' => $request->delivered_to,
        ];

        if ($request->hasFile('output_file')) {
            $file = $request->file('output_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->store('ticket_outputs/' . $ticket->id, 'public');
            $outputData['file_path'] = $path;
        }

        $output->update($outputData);

        return redirect()->route('suadmin.tickets.show', $ticket)->with('success', 'Dokumen hasil berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('suadmin.tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Send ticket information via email
     */
    public function sendTicketInfo(Request $request, Ticket $ticket)
    {
        // For now, returning a success response
        // In a real implementation, you would send an email here
        return response()->json([
            'success' => true,
            'message' => 'Email info tiket berhasil dikirim'
        ]);
    }

    /**
     * Send survey information via email
     */
    public function sendSurveyInfo(Request $request, Ticket $ticket)
    {
        // For now, returning a success response
        // In a real implementation, you would send an email here
        return response()->json([
            'success' => true,
            'message' => 'Email info survei berhasil dikirim'
        ]);
    }

    /**
     * Approve a ticket
     */
    public function approveTicket(Request $request, Ticket $ticket)
    {
        // Authorize the action using the policy
        $this->authorize('approve', $ticket);

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        // Update ticket approval status
        $ticket->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $request->notes,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen persyaratan berhasil disetujui. Anda dapat melanjutkan untuk upload hasil atau tandai siap diambil.'
        ]);
    }

    /**
     * Reject a ticket
     */
    public function rejectTicket(Request $request, Ticket $ticket)
    {
        // Authorize the action using the policy
        $this->authorize('approve', $ticket);

        $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        // Update ticket approval status
        $ticket->update([
            'approval_status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $request->notes,
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen persyaratan ditolak. Alasan: ' . $request->notes
        ]);
    }

    /**
     * Upload hasil untuk produk digital
     */
    public function uploadResult(Request $request, Ticket $ticket)
    {
        $request->validate([
            'output_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if approved
        if ($ticket->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket harus di-approve terlebih dahulu sebelum upload hasil'
            ], 422);
        }

        // Upload file
        $file = $request->file('output_file');
        $path = $file->store('ticket_outputs/' . $ticket->id, 'public');

        // Create output record
        $ticket->outputs()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Update ticket status to completed
        $ticket->update([
            'status' => Ticket::STATUS_COMPLETED,
            'actual_completion_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil berhasil diupload dan tiket diselesaikan'
        ]);
    }

    /**
     * Mark ready for pickup (non-digital products)
     */
    public function markReadyForPickup(Request $request, Ticket $ticket)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if approved
        if ($ticket->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket harus di-approve terlebih dahulu'
            ], 422);
        }

        // Mark ready for pickup
        $ticket->update([
            'ready_for_pickup' => true,
            'pickup_notified_at' => now(),
            'status' => Ticket::STATUS_COMPLETED,
            'actual_completion_date' => now(),
        ]);

        // TODO: Send email/SMS notification to user

        return response()->json([
            'success' => true,
            'message' => 'Dokumen ditandai siap diambil di PTSP. Notifikasi telah dikirim kepada pemohon.'
        ]);
    }

    /**
     * Send survey link (auto-disable if already filled)
     */
    public function sendSurvey(Request $request, Ticket $ticket)
    {
        // Check if ticket is completed
        if ($ticket->status !== Ticket::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Survei hanya dapat dikirim untuk tiket yang sudah selesai'
            ], 422);
        }

        // Check if survey already sent
        if ($ticket->survey_sent) {
            return response()->json([
                'success' => false,
                'message' => 'Survei sudah pernah dikirim untuk tiket ini'
            ], 422);
        }

        // TODO: Send email with survey link

        // Mark survey as sent
        $ticket->update([
            'survey_sent' => true,
            'survey_sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Link survei berhasil dikirim ke ' . ($ticket->email ?? $ticket->user->email)
        ]);
    }
}