<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guest_type',
        'applicant_type',
        'service_id',
        'phone',
        'email',
        'institution',
        'institution_category',
        'purpose',
        'notes',
        'person_to_meet',
        'check_in_time',
        'check_out_time',
        'photo_path',
        'visitor_card_number',
        'status',
        'created_by',
        'is_obscured'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    protected static function booted(): void
    {
        // The status follows the checkout time, whoever sets it (the database checks both agree).
        static::saving(function (Visitor $visitor) {
            $visitor->status = $visitor->check_out_time ? self::STATUS_CHECKED_OUT : self::STATUS_ACTIVE;
        });
    }

    // Relationship with staff who registered the visitor
    public function staff()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Riwayat checkout: how the guest left (officer or themselves). */
    public function checkouts()
    {
        return $this->hasMany(VisitorCheckout::class)->latest('checked_out_at');
    }

    /** The service an applicant (guest_type pemohon) came for; none for "Lainnya". */
    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    // Constants for status
    const STATUS_ACTIVE = 'active';
    const STATUS_CHECKED_OUT = 'checked_out';

    /** The two ways a guest is registered, as on the public visitor book's two forms. */
    const GUEST_TYPES = [
        'kunjungan' => 'Tamu Kunjungan',
        'pemohon' => 'Pemohon Layanan Offline',
    ];

    /** "Status pemohon" of the public form for service applicants; an institution names itself. */
    const APPLICANT_STATUSES = [
        'siswa' => 'Siswa',
        'wali_murid' => 'Wali Murid',
        'alumni' => 'Alumni',
        'pegawai' => 'Pegawai',
        'umum' => 'Masyarakat Umum',
        'instansi_perusahaan' => 'Instansi/Perusahaan',
    ];

    /** "Jenis instansi/perusahaan" of the public visitor book, grouped as there. */
    const INSTITUTION_CATEGORIES = [
        'Instansi' => ['pemerintah' => 'Pemerintah', 'swasta' => 'Swasta', 'pendidikan' => 'Pendidikan'],
        'Perusahaan' => ['umkm' => 'UMKM', 'menengah' => 'Menengah', 'besar' => 'Besar', 'multinasional' => 'Multinasional'],
        'lainnya' => 'Lainnya',
    ];

    /** The codes of INSTITUTION_CATEGORIES, for validation. */
    public static function institutionCategoryKeys(): array
    {
        return collect(self::INSTITUTION_CATEGORIES)->flatMap(fn ($group, string $key) => is_array($group) ? array_keys($group) : [$key])->all();
    }

    // Choices for "Tujuan Kunjungan" on the public visitor book; "Lainnya" asks for free text.
    /** Initial content of the 'tujuan' list in visitor_masters (see VisitorMaster). */
    const VISIT_PURPOSES = [
        'Kepala Madrasah',
        'Kepala TU',
        'Waka Humas',
        'Waka Kurikulum',
        'Waka Kesiswaan',
        'Waka Sarpras',
        'Komite',
        'Unit Tatib',
        'Unit UKS',
        'Unit BK',
        'Mahad/Asrama',
        'Wali Kelas',
        'Layanan PTSP',
        'Lainnya',
    ];

    /**
     * Name for the public visitor book: "Budi Santoso" -> "B**i S*****o"
     * when the visitor asked to hide it. Done server-side so the real name
     * never reaches the page.
     */
    public function publicName(): string
    {
        if (!$this->is_obscured) {
            return (string) $this->name;
        }

        return collect(preg_split('/\s+/u', trim((string) $this->name)))
            ->map(function ($part) {
                $length = mb_strlen($part);

                return $length <= 2
                    ? $part
                    : mb_substr($part, 0, 1) . str_repeat('*', $length - 2) . mb_substr($part, -1);
            })
            ->implode(' ');
    }

    /**
     * What a guest sees right after registering (web confirmation card and the
     * public API). Shown on a public, often shared screen, so the name follows
     * the guest's own "samarkan" choice and the phone number is masked.
     *
     * @return array<string, string|null>
     */
    public function confirmationSummary(): array
    {
        return [
            'Nama' => $this->publicName(),
            'No. Telepon/HP' => $this->maskedPhone(),
            'Instansi' => $this->institution ?: '–',
            'Tujuan' => $this->purpose,
            'Waktu check-in' => $this->check_in_time?->translatedFormat('l, j F Y H:i'),
            'Daftar tamu publik' => $this->is_obscured ? 'Nama disamarkan' : 'Nama ditampilkan',
        ];
    }

    /**
     * May the guest check themselves out from the public visitor book? Only
     * on the day they came, while still on site, and with a phone number to
     * prove it is them; anyone else is checked out at the counter.
     */
    public function canSelfCheckOut(): bool
    {
        return $this->check_out_time === null
            && filled($this->phone)
            && (bool) $this->check_in_time?->isToday();
    }

    /**
     * Is $typed the phone this guest gave on arrival? Written either way:
     * punctuation and spaces aside, 62, +62 or 0062 the same as a leading 0
     * (as accounts are matched, Applicants::normalizePhone). A guest who gave
     * no phone matches nothing.
     */
    public function phoneMatches(?string $typed): bool
    {
        return \App\Support\Applicants::samePhone($this->phone, $typed);
    }

    /**
     * Phone number for public listings: only the last three digits.
     */
    public function maskedPhone(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->phone);

        return $digits === '' ? '-' : str_repeat('*', max(0, strlen($digits) - 3)) . substr($digits, -3);
    }
}
