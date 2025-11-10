<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'user_type',
        'description',
        'is_active',
        'is_single_use',
        'max_uses',
        'used_count',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_single_use' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if code is valid and can be used
     */
    public function canBeUsed(): bool
    {
        // Check if code is active
        if (!$this->is_active) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check usage limit
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');

        // If single use, deactivate
        if ($this->is_single_use) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * Relationship: User who created this code
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Active codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
