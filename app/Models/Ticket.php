<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'service_id',
        'mode',
        'status',
        'approval_status',
        'current_handler_id',
        'assigned_to_id',
        'priority',
        'notes',
        'estimated_completion_date',
        'actual_completion_date',
        'is_urgent',
        'created_by',
        'updated_by',
        'email',
        'whatsapp_number',
        'approval_required',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'survey_sent',
        'survey_sent_at',
        'ready_for_pickup',
        'pickup_notified_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = $model->generateTicketNumber();
            }
        });
    }

    protected $casts = [
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'is_urgent' => 'boolean',
        'survey_sent' => 'boolean',
        'survey_sent_at' => 'datetime',
        'ready_for_pickup' => 'boolean',
        'pickup_notified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationship with user who applied
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Relationship with current handler
    public function currentHandler()
    {
        return $this->belongsTo(User::class, 'current_handler_id');
    }

    // Relationship with assigned user
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    // Relationship with creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with updater
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relationship with ticket logs
    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    // Relationship with ticket files
    public function files()
    {
        return $this->hasMany(TicketFile::class, 'ticket_id');
    }

    // Relationship with ticket outputs
    public function outputs()
    {
        return $this->hasMany(TicketOutput::class, 'ticket_id');
    }

    // Relationship with main ticket output 
    public function output()
    {
        return $this->hasOne(TicketOutput::class, 'ticket_id')->latestOfMany();
    }

    // Relationship to get workflow steps through ticket workflows
    public function workflowSteps()
    {
        return $this->belongsToMany(
            WorkflowStep::class,
            'ticket_workflow_steps', // intermediate table
            'ticket_id',            // foreign key for current model (Ticket)
            'workflow_step_id'      // foreign key for related model (WorkflowStep)
        )
        ->withPivot(['completed_at', 'notes']) // pivot table columns
        ->withTimestamps();
    }

    // Relationship with ticket workflows
    public function ticketWorkflows()
    {
        return $this->hasMany(TicketWorkflow::class);
    }

    // Relationship with survey responses
    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Relationship with user who approved the ticket
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Check if a survey has been completed for this ticket
    public function hasSurveyCompleted()
    {
        return $this->surveyResponses()->whereNotNull('completed_at')->exists();
    }

    // Get the latest survey response for this ticket
    public function latestSurveyResponse()
    {
        return $this->surveyResponses()->latest()->first();
    }

    // Check if survey can be sent (not sent yet and status is completed)
    public function canSendSurvey()
    {
        return !$this->survey_sent && $this->status === self::STATUS_COMPLETED;
    }

    // Check if document is ready for pickup notification
    public function canNotifyPickup()
    {
        return !$this->ready_for_pickup &&
               $this->approval_status === 'approved' &&
               !$this->service->is_digital_product;
    }

    // Scope to filter by approval status
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeNotApproved($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeApprovalRequired($query)
    {
        return $query->where('approval_required', true);
    }

    // Constants for status
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Constants for mode
    const MODE_ONLINE = 'online';
    const MODE_OFFLINE = 'offline';
    const MODE_HYBRID = 'hybrid';

    // Constants for priority
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    /**
     * Generate ticket number based on mode
     */
    public function generateTicketNumber()
    {
        $prefix = match($this->mode) {
            'online' => 'N',    // N for online
            'offline' => 'F',   // F for offline
            'hybrid' => 'H',    // H for hybrid
            default => 'N'
        };
        
        $yearMonth = now()->format('Ym');
        
        // Get the next sequence number - now with the new format (F-202511-001)
        $lastTicket = static::where('ticket_number', 'LIKE', "{$prefix}-{$yearMonth}-%")
            ->orderByRaw("CAST(SUBSTR(ticket_number, -3) AS INTEGER) DESC")
            ->first();
        
        $sequence = 1;
        if ($lastTicket) {
            $lastSequence = substr($lastTicket->ticket_number, -3);
            if (is_numeric($lastSequence)) {
                $sequence = intval($lastSequence) + 1;
            }
        }
        
        // Format the sequence number to be 3 digits
        $sequenceNumber = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$yearMonth}-{$sequenceNumber}";
    }
}