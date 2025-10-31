<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketWorkflowStep;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackOfficeController extends Controller
{
    // Show dashboard with tasks assigned to the user
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get tickets assigned to the user or tickets in workflow steps the user is responsible for
        $tickets = Ticket::where(function ($query) use ($user) {
            // Tickets assigned directly to the user
            $query->where('assigned_to_id', $user->id)
                  ->orWhere('current_handler_id', $user->id);
        })
        ->orWhereHas('ticketWorkflows.currentStep', function ($query) use ($user) {
            // Tickets in workflow steps where the user is assigned
            $query->where('assigned_to', $user->id);
        })
        ->with(['service', 'user', 'logs', 'ticketWorkflows.currentStep'])
        ->latest()
        ->get();

        // Get statistics
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'submitted')->count(),
            'in_progress_tickets' => Ticket::where('status', 'in_process')->count(),
            'completed_tickets' => Ticket::where('status', 'completed')->count(),
        ];

        return view('backoffice.dashboard', compact('tickets', 'stats'));
    }

    // Show all tickets in the system
    public function allTickets()
    {
        $tickets = Ticket::with(['service', 'user', 'currentHandler', 'logs'])
            ->latest()
            ->get();

        return view('backoffice.all-tickets', compact('tickets'));
    }

    // Process a ticket (verify documents, etc.)
    public function processTicket($id)
    {
        $ticket = Ticket::with(['service', 'user', 'creator', 'files'])->findOrFail($id);
        
        // Authorization: Only users assigned to the ticket or with right permissions can process
        $user = Auth::user();
        if ($ticket->assigned_to_id !== $user->id && $ticket->current_handler_id !== $user->id) {
            abort(403, 'You are not authorized to process this ticket.');
        }

        return view('backoffice.process-ticket', compact('ticket'));
    }

    // Update ticket status after processing
    public function updateTicket(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,in_process,approved,rejected,completed',
            'notes' => 'nullable|string',
        ]);

        $ticket = Ticket::findOrFail($id);
        
        // Authorization
        $user = Auth::user();
        if ($ticket->assigned_to_id !== $user->id && $ticket->current_handler_id !== $user->id) {
            abort(403, 'You are not authorized to update this ticket.');
        }

        // Update ticket status
        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => $request->status,
            'current_handler_id' => $user->id,
            'notes' => $request->notes,
        ]);

        // Log the action
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'Status updated to ' . ucfirst(str_replace('_', ' ', $request->status)),
            'performed_by' => $user->id,
            'from_status' => $oldStatus,
            'to_status' => $request->status,
            'notes' => $request->notes,
        ]);

        // If the ticket is approved, check if there's a workflow to follow
        if ($request->status === 'approved') {
            $this->handleWorkflow($ticket);
        }

        return redirect()->route('backoffice.dashboard')
            ->with('success', 'Ticket status updated successfully!');
    }

    // Handle workflow for approved tickets
    private function handleWorkflow($ticket)
    {
        // Check if this service has a workflow
        $workflow = $ticket->service->workflow;
        
        if (!$workflow) {
            // If no workflow, mark ticket as completed
            $ticket->update(['status' => 'completed']);
            
            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'Service completed (no workflow)',
                'performed_by' => Auth::id(),
                'from_status' => 'approved',
                'to_status' => 'completed',
            ]);
            
            return;
        }

        // Create or get the ticket workflow
        $ticketWorkflow = $ticket->ticketWorkflows()->firstOrCreate([
            'workflow_id' => $workflow->id,
        ], [
            'current_step_id' => $workflow->steps->first()->id ?? null,
        ]);

        // Process the first step if it exists
        if ($workflow->steps->first()) {
            $this->processWorkflowStep($ticket, $workflow->steps->first());
        }
    }

    // Process a workflow step
    private function processWorkflowStep($ticket, $step)
    {
        $user = Auth::user();
        
        // Create ticket workflow step
        $ticketWorkflowStep = $ticket->ticketWorkflows->first()->ticketWorkflowSteps()
            ->create([
                'workflow_step_id' => $step->id,
                'status' => 'in_progress',
                'assigned_to' => $this->getStepAssignee($step),
                'started_at' => now(),
            ]);

        // Update the ticket's current workflow step
        $ticket->ticketWorkflows->first()->update([
            'current_step_id' => $step->id,
        ]);

        // Log the action
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => "Started workflow step: {$step->name}",
            'performed_by' => $user->id,
        ]);
    }

    // Get the appropriate user to assign a workflow step
    private function getStepAssignee($step)
    {
        // In a real application, this would be more sophisticated
        // For now, we'll return a default user or look up by role
        if ($step->required_role) {
            // Find a user with the required role
            $user = \App\Models\User::role($step->required_role)->first();
            return $user ? $user->id : null;
        }
        
        return null; // No specific assignee
    }

    // Complete a workflow step
    public function completeWorkflowStep(Request $request, $ticketId, $stepId)
    {
        $request->validate([
            'status' => 'required|in:completed,rejected',
            'notes' => 'nullable|string',
        ]);

        $ticketWorkflowStep = TicketWorkflowStep::where('ticket_workflow_id', function ($query) use ($ticketId) {
                $query->select('id')
                      ->from('ticket_workflows')
                      ->where('ticket_id', $ticketId);
            })
            ->where('workflow_step_id', $stepId)
            ->firstOrFail();

        $user = Auth::user();
        
        // Authorization
        if ($ticketWorkflowStep->assigned_to !== $user->id) {
            abort(403, 'You are not authorized to complete this step.');
        }

        // Update the step
        $ticketWorkflowStep->update([
            'status' => $request->status,
            'completed_at' => now(),
            'completed_by' => $user->id,
            'notes' => $request->notes,
        ]);

        $ticket = $ticketWorkflowStep->ticketWorkflow->ticket;

        // If the step was rejected, update the ticket status
        if ($request->status === 'rejected') {
            $ticket->update(['status' => 'rejected']);
            
            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => "Workflow step rejected: {$ticketWorkflowStep->workflowStep->name}",
                'performed_by' => $user->id,
                'from_status' => 'in_process',
                'to_status' => 'rejected',
            ]);
        } else {
            // If completed, move to the next step or complete the ticket
            $this->moveToNextStep($ticket, $ticketWorkflowStep);
        }

        return redirect()->route('backoffice.dashboard')
            ->with('success', 'Workflow step completed successfully!');
    }

    // Move to the next step in the workflow
    private function moveToNextStep($ticket, $currentStep)
    {
        $workflow = $ticket->service->workflow;
        $currentStepOrder = $currentStep->workflowStep->step_number;
        $nextStep = $workflow->steps()->where('step_number', '>', $currentStepOrder)->orderBy('step_number')->first();

        if ($nextStep) {
            // Process the next step
            $this->processWorkflowStep($ticket, $nextStep);
        } else {
            // All steps completed, mark ticket as completed
            $ticket->update(['status' => 'completed']);
            
            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'Service workflow completed',
                'performed_by' => Auth::id(),
                'from_status' => 'in_process',
                'to_status' => 'completed',
            ]);
            
            // Create output if needed
            $this->createTicketOutput($ticket);
        }
    }

    // Create ticket output after completion
    private function createTicketOutput($ticket)
    {
        // In a real application, this would generate the actual output document
        // For now, we'll just create a record indicating the output exists
        $ticket->outputs()->create([
            'output_type' => 'digital',
            'output_description' => "Output for {$ticket->service->name}",
            'is_delivered' => false,
            'delivery_method' => 'email',
        ]);
    }

    // Assign a ticket to another user
    public function assignTicket(Request $request, $id)
    {
        $request->validate([
            'assigned_to_id' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        
        // Authorization: Only authorized users can reassign tickets
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'tu', 'kepala-tu', 'kepala-sekolah'])) {
            abort(403, 'You are not authorized to reassign tickets.');
        }

        $oldAssignee = $ticket->assignedTo;
        $newAssignee = \App\Models\User::find($request->assigned_to_id);

        $ticket->update(['assigned_to_id' => $request->assigned_to_id]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'Ticket reassigned',
            'performed_by' => $user->id,
            'notes' => "Reassigned from " . ($oldAssignee ? $oldAssignee->name : 'Unassigned') . " to {$newAssignee->name}",
        ]);

        return redirect()->back()
            ->with('success', 'Ticket reassigned successfully!');
    }

    // Escalate/dispatch ticket to another department
    public function dispatchTicket(Request $request, $id)
    {
        $request->validate([
            'dispatch_to_id' => 'required|exists:users,id',
            'notes' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();
        
        // Authorization
        if (!$user->hasRole(['admin', 'tu', 'kepala-tu', 'kepala-sekolah', 'waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas'])) {
            abort(403, 'You are not authorized to dispatch tickets.');
        }

        // Update ticket
        $ticket->update([
            'current_handler_id' => $request->dispatch_to_id,
            'notes' => $request->notes . ' [Dispatched]',
        ]);

        $dispatchedTo = \App\Models\User::find($request->dispatch_to_id);

        // Log the action
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'Ticket dispatched',
            'performed_by' => $user->id,
            'notes' => "Dispatched to {$dispatchedTo->name}: {$request->notes}",
        ]);

        return redirect()->back()
            ->with('success', 'Ticket dispatched successfully!');
    }
}