<?php

namespace App\Models;

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
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . time();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . time();
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
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
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
}