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

        // "Dapat diajukan oleh": applicant roles, each once, in the official order;
        // none ticked means every applicant. An unknown role is kept for the
        // database to refuse: dropping it could open the service to everyone.
        static::saving(function (Service $service) {
            $roles = array_values(array_intersect(\App\Support\Applicants::ROLES, (array) $service->user_types_allowed));
            $unknown = array_diff((array) $service->user_types_allowed, \App\Support\Applicants::ROLES);
            $service->user_types_allowed = $unknown ? array_values(array_unique([...$roles, ...$unknown])) : ($roles ?: null);
        });
    }

    /**
     * The applicant roles (spatie roles) the service is open to; every
     * applicant role when "Dapat diajukan oleh" is left empty.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role>
     */
    public function applicantRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return \Spatie\Permission\Models\Role::where('guard_name', 'web')
            ->whereIn('name', $this->user_types_allowed ?: \App\Support\Applicants::ROLES)
            ->get()
            ->sortBy(fn ($role) => array_search($role->name, \App\Support\Applicants::ROLES, true))
            ->values();
    }

    /**
     * Services a given user type may apply for; services open to "umum" are
     * available to everyone. user_types_allowed is a jsonb array.
     */
    public function scopeAvailableFor($query, ?string $userType)
    {
        return $query->where(function ($query) use ($userType) {
            // No applicant type picked means the service is open to everyone
            // (as the /cp form says), like one listing "umum".
            $query->whereNull('user_types_allowed')
                ->orWhereRaw("user_types_allowed::jsonb in ('[]'::jsonb, 'null'::jsonb)")
                ->orWhereJsonContains('user_types_allowed', 'umum');

            if ($userType && $userType !== 'umum') {
                $query->orWhereJsonContains('user_types_allowed', $userType);
            }
        });
    }

    /** scopeAvailableFor() for a loaded service: is it open to $userType? */
    public function isAvailableFor(?string $userType): bool
    {
        $types = $this->user_types_allowed;

        return empty($types) || in_array('umum', $types, true) || ($userType && in_array($userType, $types, true));
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

    /**
     * Jumlah persyaratan layanan: berkas persyaratan terstruktur bila ada,
     * selain itu butir pada teks Persyaratan yang diisi di form /cp.
     */
    public function requirementCount(): int
    {
        $structured = $this->requirements_count ?? $this->requirements()->count();

        return $structured ?: count(static::requirementItemsFrom($this->getAttribute('requirements')));
    }

    /**
     * Butir persyaratan dari teks/HTML: daftar <li>, baris/paragraf,
     * atau penomoran "1. Formulir 2. Fotokopi KK".
     *
     * @return list<string>
     */
    public static function requirementItemsFrom(?string $text): array
    {
        $plain = preg_replace(['~<\s*br\s*/?>~i', '~</\s*(li|p|div|h[1-6]|tr)\s*>~i'], "\n", (string) $text);
        $plain = html_entity_decode(strip_tags($plain), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $items = [];
        foreach (preg_split('/\R/u', $plain) as $line) {
            foreach (preg_split('/(?:^|\s+)\d+[.)]\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                $part = preg_replace('/^[\s\x{00A0};,\-•*]+|[\s\x{00A0};,]+$/u', '', $part);
                if ($part !== '') {
                    $items[] = $part;
                }
            }
        }

        return $items;
    }

    // Relationship with service components
    public function components()
    {
        return $this->hasMany(ServiceComponent::class);
    }

    /**
     * Isi komponen standar pelayanan tambahan (ServiceComponent::EXTRA),
     * berkunci jenis komponen.
     *
     * @return array<string, string|null>
     */
    public function standardComponents(): array
    {
        $saved = $this->components()->whereIn('component_type', array_keys(ServiceComponent::EXTRA))
            ->pluck('content', 'component_type');

        return collect(ServiceComponent::EXTRA)->map(fn ($meta, string $type) => $saved[$type] ?? null)->all();
    }

    /**
     * Simpan komponen tambahan dari form /cp. Isian kosong menghapus
     * komponennya sehingga portal menampilkan "Belum diisi".
     *
     * @param  array<string, string|null>  $values
     */
    public function syncStandardComponents(array $values): void
    {
        foreach (array_keys(ServiceComponent::EXTRA) as $type) {
            $content = \App\Support\RichText::sanitize($values[$type] ?? null);

            if ($content === null) {
                $this->components()->where('component_type', $type)->delete();

                continue;
            }

            $this->components()->updateOrCreate(['component_type' => $type], ['content' => $content]);
        }
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
     * type, or to any of their types (roles). Offline any active service
     * qualifies here; the counter itself narrows it to the applicant's type
     * (availableFor, FrontDeskService).
     */
    public function scopeRequestable($query, string|array|null $userTypes, string $channel = 'online')
    {
        // Several types (an applicant's roles): open to any of them.
        $types = (array) $userTypes ?: [null];

        return $query->where('is_active', true)
            ->when($channel === 'online', fn ($q) => $q->whereIn('mode', ['online', 'hybrid'])
                ->where(fn ($open) => collect($types)->each(fn (?string $type) => $open->orWhere(fn ($one) => $one->availableFor($type)))));
    }

    /**
     * Can $applicant request this service on $channel (read fresh from the
     * database)? Online, by any of the applicant's roles, as the portal's
     * catalogue offers it (PortalServices).
     */
    public function acceptsRequests(?User $applicant, string $channel = 'online'): bool
    {
        $roles = $applicant ? \App\Support\PortalServices::rolesOf($applicant) : null;

        return static::query()->whereKey($this->getKey())->requestable($roles, $channel)->exists();
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

    /** Jenis SK bawaan: the jenis an SK asked for from this service's tickets starts with (Master Jenis SK > Layanan PTSP). */
    public function jenisSk(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PersuratanMaster::class, 'jenis_sk_id');
    }
}
