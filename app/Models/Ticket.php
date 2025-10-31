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
        'channel',
        'status',
        'current_handler_id',
        'assigned_to_id',
        'priority',
        'notes',
        'estimated_completion_date',
        'actual_completion_date',
        'is_urgent',
        'created_by'
    ];

    protected $casts = [
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'is_urgent' => 'boolean',
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

    // Relationship with ticket logs
    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    // Relationship with ticket files
    public function files()
    {
        return $this->hasMany(TicketFile::class);
    }

    // Relationship with ticket outputs
    public function outputs()
    {
        return $this->hasMany(TicketOutput::class);
    }

    // Relationship with ticket workflows
    public function ticketWorkflows()
    {
        return $this->hasMany(TicketWorkflow::class);
    }

    // Constants for status
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Constants for channel
    const CHANNEL_ONLINE = 'online';
    const CHANNEL_OFFLINE = 'offline';

    // Constants for priority
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
}