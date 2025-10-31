<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

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
}