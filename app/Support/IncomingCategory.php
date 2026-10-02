<?php

namespace App\Support;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grup kategori layanan masuk: how the back office receives a request
 * once the leadership has disposed it. Read from the disposition
 * instruction stored in tickets.approval_notes ("Instruksi: … ."); a
 * request without a matching instruction (or without disposition) is a
 * plain "disposisi" to process.
 */
class IncomingCategory
{
    public const CATEGORIES = [
        'disposisi' => 'Disposisi',
        'tembusan' => 'Tembusan',
        'koordinasi' => 'Koordinasi',
        'arahan' => 'Arahan',
    ];

    public const DESCRIPTIONS = [
        'disposisi' => 'Untuk diproses atau ditindaklanjuti unit penerima.',
        'tembusan' => 'Untuk diketahui atau diarsipkan, tanpa tindakan.',
        'koordinasi' => 'Perlu dikoordinasikan antarunit sebelum diproses.',
        'arahan' => 'Pimpinan meminta saran atau pertimbangan.',
    ];

    public const COLORS = [
        'disposisi' => 'primary',
        'tembusan' => 'gray',
        'koordinasi' => 'warning',
        'arahan' => 'info',
    ];

    /** Instruction (from Master Persuratan) => category; anything else is "disposisi". */
    public const INSTRUCTION_CATEGORIES = [
        'Untuk diketahui' => 'tembusan',
        'Untuk diarsipkan' => 'tembusan',
        'Untuk dikoordinasikan' => 'koordinasi',
        'Mohon saran/pertimbangan' => 'arahan',
    ];

    public static function label(?string $category): string
    {
        return self::CATEGORIES[$category] ?? self::CATEGORIES['disposisi'];
    }

    public static function of(Ticket $ticket): string
    {
        foreach (self::INSTRUCTION_CATEGORIES as $instruction => $category) {
            if (str_contains(mb_strtolower((string) $ticket->approval_notes), mb_strtolower('Instruksi: ' . $instruction . '.'))) {
                return $category;
            }
        }

        return 'disposisi';
    }

    /** Limit a ticket query to one category. */
    public static function scope(Builder $query, string $category): Builder
    {
        $patterns = fn (string $only = null) => collect(self::INSTRUCTION_CATEGORIES)
            ->filter(fn (string $c) => $only === null || $c === $only)
            ->keys()
            ->map(fn (string $instruction) => '%' . mb_strtolower('Instruksi: ' . $instruction . '.') . '%');

        if ($category === 'disposisi') {
            return $query->where(fn (Builder $q) => $q->whereNull('approval_notes')
                ->orWhere(fn (Builder $q) => $patterns()->each(fn (string $p) => $q->whereRaw('lower(approval_notes) not like ?', [$p]))));
        }

        return $query->where(fn (Builder $q) => $patterns($category)->each(fn (string $p) => $q->orWhereRaw('lower(approval_notes) like ?', [$p])));
    }
}
