<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'workflow_id',
        'current_step_id',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with workflow
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    // Relationship with current step
    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    // Relationship with ticket workflow steps
    public function ticketWorkflowSteps()
    {
        return $this->hasMany(TicketWorkflowStep::class);
    }
}