<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_workflow_id',
        'workflow_step_id',
        'assigned_to',
        'status',
        'started_at',
        'completed_at',
        'completed_by',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationship with ticket workflow
    public function ticketWorkflow()
    {
        return $this->belongsTo(TicketWorkflow::class);
    }

    // Relationship with workflow step
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    // Relationship with assigned user
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relationship with user who completed the step
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Constants for status
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
}