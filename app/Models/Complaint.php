<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_type',
        'title',
        'description',
        'complainant_name',
        'complainant_contact',
        'complainant_email',
        'user_id',
        'status',
        'priority',
        'assigned_to',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'anonymous'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'anonymous' => 'boolean',
    ];

    // Relationship with user who made the complaint
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with user who resolved the complaint
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Relationship with user to whom the complaint is assigned
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Constants for complaint types
    const TYPE_COMPLAINT = 'complaint';
    const TYPE_SUGGESTION = 'suggestion';
    const TYPE_WHISTLEBLOWING = 'whistleblowing';

    // Constants for status
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    // Constants for priority
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
}