<?php

namespace App\Models;

use App\Support\ComplaintNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Service;
use App\Models\User;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    /** Follow-up stages (Modul 10), in order. */
    public const STATUSES = [
        'submitted' => 'Diterima',
        'in_review' => 'Ditelaah',
        'in_progress' => 'Ditindaklanjuti',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup',
    ];

    public const TYPES = [
        'complaint' => 'Pengaduan',
        'suggestion' => 'Saran',
        'whistleblowing' => 'Whistleblowing',
    ];

    public const PRIORITIES = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->complaint_type] ?? ucfirst((string) $this->complaint_type);
    }

    protected $fillable = [
        'complaint_type',
        'title',
        'subject',
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
        'anonymous',
        'complaint_number',
        'related_ticket_number',
        'type',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'service_id',
        'category',
        'assigned_to_id',
        'response',
        'is_whistleblowing',
        'incident_date',
        'incident_location',
        'involved_parties',
        'is_confidential',
        'evidence_files',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'incident_date' => 'date',
        'anonymous' => 'boolean',
        'is_whistleblowing' => 'boolean',
        'is_confidential' => 'boolean',
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

    // Alternative relationship name to match controller
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relationship with service
    public function service()
    {
        return $this->belongsTo(Service::class);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->complaint_number)) {
                $model->complaint_number = $model->generateComplaintNumber();
            }
        });

        // Riwayat status: the report's first stage.
        static::created(fn (Complaint $complaint) => ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'to_status' => $complaint->status ?: 'submitted',
        ]));
    }

    /** Next report number for this type, e.g. PEM-202609-0001 (see ComplaintNumber). */
    public function generateComplaintNumber(): string
    {
        return ComplaintNumber::next($this->complaint_type ?? $this->type);
    }

    /** Riwayat status, oldest first. */
    public function statusLogs()
    {
        return $this->hasMany(ComplaintStatusLog::class)->orderBy('created_at')->orderBy('id');
    }

    /** Whistleblowing reports and reports whose reporter asked for confidentiality. */
    public function isSecret(): bool
    {
        return $this->complaint_type === 'whistleblowing' || (bool) $this->is_confidential;
    }

    /**
     * Reporter as shown to $viewer: anonymous whistleblowers stay "Anonim";
     * a secret report's identity is only shown to complaint handlers.
     */
    public function reporterLabel(?User $viewer = null): string
    {
        $viewer ??= auth()->user();

        return match (true) {
            $this->complaint_type === 'whistleblowing' && $this->anonymous => 'Anonim',
            $this->isSecret() && ! $viewer?->hasAnyRole(\App\Support\RoleAccess::COMPLAINT_HANDLERS) => 'Identitas dirahasiakan',
            default => $this->reporter_name ?: 'Anonim',
        };
    }
}
