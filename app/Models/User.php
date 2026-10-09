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
        'whatsapp_number',
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
        'last_login_at' => 'datetime',
        'active_role_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'must_change_password' => 'boolean',
        'preferences' => 'array',
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

    /** Active staff a ticket can be assigned to (the panel's and the API's "Tugaskan petugas"). */
    public function scopeAssignable($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereIn('user_type', ['guru', 'pegawai'])->orWhereHas('roles', fn ($r) => $r->whereIn('name', self::STAFF_ROLES)));
    }

    /**
     * Scope to get users of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /** Peran aktif terakhir yang dipilih di /cp (konteks kerja; peran yang dimiliki tetap di Spatie). */
    public function activeRole()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'active_role_id');
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

    /** Leadership roles (monitoring and approvals). */
    public const LEADERSHIP_ROLES = ['kepala_sekolah', 'kepala_tu', 'supervisor'];

    /** Every internal staff role; everyone else is an applicant (pemohon). */
    public const STAFF_ROLES = [
        'admin', 'front_desk', 'back_office', 'kepala_sekolah', 'kepala_tu', 'supervisor',
        // Back-office units receiving dispositions
        'waka_humas', 'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'tata_usaha', 'penjamin_mutu',
    ];

    /**
     * Check if user is a service officer (front desk or back office)
     */
    public function isPetugas(): bool
    {
        return $this->hasAnyRole(['front_desk', 'back_office']);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(self::STAFF_ROLES);
    }

    /**
     * Staff work in the control panel (/cp), each role seeing its own menus;
     * applicants follow their requests in the portal panel (/portal).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Deactivated accounts are locked out (null = not loaded yet: the column defaults to true)
        if ($this->is_active === false) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->isStaff(),
            'portal' => ! $this->isStaff(),
            default => false,
        };
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

    /** Notifikasi in-app, newest first (App\Models\Notification: linked to their request). */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /** E-mail addresses are case-insensitive: always stored in lower case. */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value === null ? null : mb_strtolower(trim($value));
    }
}
