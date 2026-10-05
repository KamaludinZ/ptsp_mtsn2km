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

    // Relationship with staff who registered the visitor
    public function staff()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Constants for status
    const STATUS_ACTIVE = 'active';
    const STATUS_CHECKED_OUT = 'checked_out';

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
     * Phone number for public listings: only the last three digits.
     */
    public function maskedPhone(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->phone);

        return $digits === '' ? '-' : str_repeat('*', max(0, strlen($digits) - 3)) . substr($digits, -3);
    }
}
