<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, LogsActivity;

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
        'incoming_category',
        'disposition_category',
        'approval_notes',
        'signature_type',
        'disposition_recipients',
        'survey_sent',
        'survey_sent_at',
        'ready_for_pickup',
        'pickup_notified_at'
    ];

    /** Set while TicketService changes a ticket: it writes the history itself. */
    public static bool $historyManaged = false;

    /** Who TicketService is acting for (also in queues and the console): recorded as the status changer. */
    public static ?int $actingUserId = null;

    protected static function boot()
    {
        parent::boot();

        // Safety net for the riwayat layanan: a status change made outside
        // TicketService (console, imports, future code) still leaves a trace.
        // Who changed the status: read by the tickets_record_status trigger (riwayat status).
        static::saving(function (Ticket $ticket) {
            if ($ticket->isDirty('status') && ! $ticket->isDirty('updated_by') && ($actor = static::$actingUserId ?? auth()->id())) {
                $ticket->updated_by = $actor;
            }
        });

        static::updated(function (Ticket $ticket) {
            if (static::$historyManaged || ! $ticket->wasChanged('status')) {
                return;
            }

            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'status_changed',
                'performed_by' => auth()->id(),
                'from_status' => $ticket->getOriginal('status'),
                'to_status' => $ticket->status,
                'notes' => 'Status diubah di luar alur layanan.',
            ]);
        });

        static::creating(function ($model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = $model->generateTicketNumber();
            }

            // Target date from the service standard (Permen PANRB: jangka waktu penyelesaian)
            if (empty($model->estimated_completion_date) && $model->service_id) {
                $days = Service::find($model->service_id)?->slaWorkingDays();
                if ($days) {
                    $model->estimated_completion_date = now()->addWeekdays($days)->toDateString();
                }
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
        'disposition_recipients' => 'array',
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

    /** Statuses in which a ticket is still being worked on. */
    public const OPEN_STATUSES = ['submitted', 'verified', 'in_process', 'approved'];

    public function scopeOpen($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /** Open tickets past their service-standard target date. */
    /** Riwayat status, oldest first (one row per status entered). */
    public function statusHistories()
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('id'); // insertion order = order of changes
    }

    /** In-app notifications about this request (for staff and the applicant). */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /** The status change that put the ticket in its current status. */
    public function latestStatusHistory()
    {
        return $this->hasOne(TicketStatusHistory::class)->latestOfMany('id');
    }

    /** When the ticket entered its current status. */
    public function statusSince(): \Illuminate\Support\Carbon
    {
        return $this->latestStatusHistory?->changed_at ?? $this->created_at ?? now();
    }

    /** Pencarian kata kunci (number, applicant, WhatsApp, service, officer, text); see TicketSearch. */
    public function scopeSearch($query, ?string $term)
    {
        return \App\Support\TicketSearch::apply($query, $term);
    }

    /** Kategori layanan masuk: disposisi, tembusan, koordinasi or arahan. */
    public function scopeIncomingCategory($query, string $category)
    {
        return \App\Support\IncomingCategory::scope($query, $category);
    }

    /** Submitted between two dates (inclusive, either may be null). */
    public function scopeSubmittedBetween($query, $from = null, $until = null)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('created_at', '<=', $until));
    }

    public function scopeOverdue($query)
    {
        return $query->open()
            ->whereNotNull('estimated_completion_date')
            ->whereDate('estimated_completion_date', '<', today());
    }

    /**
     * Tickets waiting for a leader's decision (Modul 8): the back office has
     * verified the documents, and no leader has decided yet.
     */
    public function scopeAwaitingApproval($query)
    {
        return $query->where('approval_required', true)
            ->where(fn ($q) => $q->whereNull('approval_status')->orWhere('approval_status', 'pending'))
            ->whereIn('status', ['verified', 'in_process']);
    }

    /** Tickets awaiting a decision that this user is allowed to make. */
    public static function approvableBy(User $user): \Illuminate\Support\Collection
    {
        return static::with(['service', 'user:id,name,user_type'])
            ->awaitingApproval()
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Ticket $ticket) => $user->can('approve', $ticket))
            ->values();
    }

    /** Tickets disposed to one of these back-office units. */
    public function scopeForwardedTo($query, array $units)
    {
        return $query->where(function ($q) use ($units) {
            foreach ($units as $unit) {
                $q->orWhereJsonContains('disposition_recipients', $unit);
            }
            if (! $units) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /** Leadership approval (Modul 8) is still outstanding. */
    public function needsApproval(): bool
    {
        return (bool) $this->approval_required && $this->approval_status !== 'approved';
    }

    /**
     * Close the ticket as done (Modul 9). Walk-in tickets and physical
     * products wait at the front desk until they are handed over.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'actual_completion_date' => $this->actual_completion_date ?? now(),
            'ready_for_pickup' => $this->mode === 'offline' || ! $this->service?->is_digital_product,
        ]);
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true)
            && $this->estimated_completion_date
            && $this->estimated_completion_date->lt(today());
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

    // Relationship to get ticket workflow step progress records. There is no
    // direct ticket_id on ticket_workflow_steps — it hangs off ticket_workflows
    // instead, so this has to traverse that intermediate table.
    public function workflowSteps()
    {
        return $this->hasManyThrough(
            TicketWorkflowStep::class,
            TicketWorkflow::class,
            'ticket_id',           // FK on ticket_workflows -> tickets
            'ticket_workflow_id',  // FK on ticket_workflow_steps -> ticket_workflows
            'id',
            'id'
        );
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
    /** @deprecated Never stored: approval is tracked in approval_status. */
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
    /** Same numbering as TicketService (configurable prefix, atomic per month). */
    public function generateTicketNumber(): string
    {
        return \App\Services\TicketService::nextTicketNumber();
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'ticket_number',
                'status',
                'approval_status',
                'priority',
                'assigned_to_id',
                'current_handler_id',
                'is_urgent',
                'notes',
                'estimated_completion_date',
                'actual_completion_date',
                'is_approved',
                'approved_by',
                'approval_notes'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}