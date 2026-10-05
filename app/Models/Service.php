<?php

namespace App\Models;

use App\Support\ServiceDisposition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'mode',
        'requirements',
        'mechanism',
        'processing_time',
        'fee',
        'product',
        'complaint_handling',
        'approval_required',
        'approval_roles',
        'approval_users',
        'approval_instructions',
        'disposition_mode',
        'disposition_roles',
        'signature_recommendation',
        'user_types_allowed',
        'is_digital_product',
        'is_active',
        'created_by',
        'slug'
    ];

    protected $casts = [
        'user_types_allowed' => 'array',
        'approval_roles' => 'array',
        'approval_users' => 'array',
        'disposition_roles' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by ??= auth()->id();

            if (empty($model->slug)) {
                $model->slug = static::uniqueSlug($model->name);
            }
        });

        // A permanently deleted service takes its template files along
        // (the database cascade alone would leave the files behind).
        static::forceDeleting(fn (Service $service) => $service->templates()->get()->each->delete());

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = static::uniqueSlug($model->name, $model->id);
            }
        });
    }

    /**
     * Services a given user type may apply for; services open to "umum" are
     * available to everyone. user_types_allowed is a jsonb array.
     */
    public function scopeAvailableFor($query, ?string $userType)
    {
        return $query->where(function ($query) use ($userType) {
            $query->whereJsonContains('user_types_allowed', 'umum');

            if ($userType && $userType !== 'umum') {
                $query->orWhereJsonContains('user_types_allowed', $userType);
            }
        });
    }

    /**
     * Service standard deadline in working days, taken from the upper bound
     * of processing_time ("2-5 hari kerja" -> 5). Null when not expressed
     * in days (e.g. "Sesuai jadwal").
     */
    public function slaWorkingDays(): ?int
    {
        if (! preg_match_all('/\d+/', (string) $this->processing_time, $matches)
            || ! str_contains(strtolower((string) $this->processing_time), 'hari')) {
            return null;
        }

        return max(array_map('intval', $matches[0])) ?: null;
    }

    // Relationship with users who created the service
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with service requirements
    public function requirements()
    {
        return $this->hasMany(ServiceRequirement::class);
    }

    // Relationship with service components
    public function components()
    {
        return $this->hasMany(ServiceComponent::class);
    }

    // Relationship with tickets
    /** Template berkas the applicant can download. */
    /** Template berkas, in display order. */
    public function templates()
    {
        return $this->hasMany(ServiceTemplate::class)->orderBy('sort')->orderBy('id');
    }

    /** Template berkas offered to applicants (active ones only), in display order. */
    public function activeTemplates()
    {
        return $this->templates()->active();
    }

    /** Templates the applicant must fill in and upload. */
    public function requiredTemplates()
    {
        return $this->activeTemplates()->required();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Services a request can be opened for right now: active, and online
     * only when served online (online/hybrid) and open to the applicant's
     * type. At the counter the officer judges eligibility, so any active
     * service may be registered there.
     */
    public function scopeRequestable($query, ?string $userType, string $channel = 'online')
    {
        return $query->where('is_active', true)
            ->when($channel === 'online', fn ($q) => $q->whereIn('mode', ['online', 'hybrid'])->availableFor($userType));
    }

    /** Can $applicant request this service on $channel (read fresh from the database)? */
    public function acceptsRequests(?User $applicant, string $channel = 'online'): bool
    {
        return static::query()->whereKey($this->getKey())->requestable($applicant?->user_type, $channel)->exists();
    }

    /** "legalisir-ijazah", or "legalisir-ijazah-2" when taken (soft-deleted services included). */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'layanan';
        $slug = $base;
        for ($i = 2; static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    // Relationship with service categories
    public function categories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_category_service');
    }

    // Relationship with workflow
    public function workflow()
    {
        return $this->hasOne(Workflow::class);
    }

    /** Pengaturan disposisi: the approval fields read as one mode (see ServiceDisposition). */
    public function getDispositionModeAttribute(): string
    {
        return ServiceDisposition::modeFor((bool) $this->approval_required, $this->approval_roles, $this->approval_users);
    }

    public function setDispositionModeAttribute(?string $mode): void
    {
        if (! array_key_exists((string) $mode, ServiceDisposition::MODES)) {
            return; // 'custom': keep the hand-made approval setup
        }

        $this->attributes['approval_required'] = $mode !== 'none';
        $this->approval_roles = ServiceDisposition::MODE_ROLES[$mode] ?: null;
        $this->approval_users = null;
    }
}
