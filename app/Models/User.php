<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type', // guru, pegawai, siswa, walimurid, alumni, instansi, umum
        'registration_code', // for internal users
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * User type constants
     */
    const USER_TYPE_GURU = 'guru';
    const USER_TYPE_PEGAWAI = 'pegawai';
    const USER_TYPE_SISWA = 'siswa';
    const USER_TYPE_WALIMURID = 'walimurid';
    const USER_TYPE_ALUMNI = 'alumni';
    const USER_TYPE_INSTANSI = 'instansi';
    const USER_TYPE_UMUM = 'umum';

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Check if user is of a specific type
     */
    public function isType($type)
    {
        return $this->user_type === $type;
    }

    /**
     * Scope to get users of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Relationship with tickets created by user
     */
    public function tickets()
    {
        return $this->hasMany(\App\Models\Ticket::class);
    }

    /**
     * Relationship with tickets assigned to user
     */
    public function assignedTickets()
    {
        return $this->hasMany(\App\Models\Ticket::class, 'assigned_to_id');
    }

    /**
     * Relationship with tickets currently handled by user
     */
    public function handlingTickets()
    {
        return $this->hasMany(\App\Models\Ticket::class, 'current_handler_id');
    }

    /**
     * Check if user has a specific role (using Spatie)
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is kepala sekolah
     */
    public function isKepalaSekolah()
    {
        return $this->hasRole('kepala_sekolah');
    }

    /**
     * Check if user is kepala TU
     */
    public function isKepalaTU()
    {
        return $this->hasRole('kepala_tu');
    }

    /**
     * Check if user is petugas
     */
    public function isPetugas()
    {
        return $this->hasRole(['petugas_tu', 'petugas_loket']);
    }

    /**
     * Determine if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access for users with admin or petugas roles
        return $this->hasRole(['admin', 'kepala_sekolah', 'kepala_tu', 'petugas_tu', 'petugas_loket']);
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'user_type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}