<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_number',
        'name',
        'description',
        'required_role',
        'estimated_duration_days',
        'is_optional'
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'estimated_duration_days' => 'integer'
    ];

    // Relationship with workflow
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    // Relationship with ticket workflow steps
    public function ticketWorkflowSteps()
    {
        return $this->hasMany(TicketWorkflowStep::class);
    }
}