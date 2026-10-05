<?php

namespace App\Support;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grup kategori layanan masuk: how the back office receives a request
 * once the leadership has disposed it. The disposition instruction sets
 * tickets.disposition_category; staff may choose another one
 * (tickets.incoming_category), which takes precedence. A request without
 * either is a plain "disposisi" to process.
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

    public const ICONS = [
        'disposisi' => 'heroicon-m-arrow-right-circle',
        'tembusan' => 'heroicon-m-eye',
        'koordinasi' => 'heroicon-m-users',
        'arahan' => 'heroicon-m-light-bulb',
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
        if (array_key_exists((string) $ticket->incoming_category, self::CATEGORIES)) {
            return $ticket->incoming_category;
        }

        return self::derived($ticket);
    }

    /** The category the disposition instruction implies (ignoring a staff choice). */
    public static function derived(Ticket $ticket): string
    {
        return array_key_exists((string) $ticket->disposition_category, self::CATEGORIES) ? $ticket->disposition_category : 'disposisi';
    }

    /** Category for a disposition instruction (from Master Persuratan); anything else is "disposisi". */
    public static function forInstruction(?string $instruction): string
    {
        foreach (self::INSTRUCTION_CATEGORIES as $known => $category) {
            if (mb_strtolower(trim((string) $instruction)) === mb_strtolower($known)) {
                return $category;
            }
        }

        return 'disposisi';
    }

    /**
     * Riwayat kategori: the category given by the disposition, then every
     * change made by staff, oldest first.
     *
     * @return array<int, array{from: ?string, to: string, at: ?\Illuminate\Support\Carbon, actor: string, reason: ?string, manual: bool}>
     */
    public static function history(Ticket $ticket): array
    {
        $changes = $ticket->logs()->where('action', 'category_changed')->with('performer:id,name')->oldest()->oldest('id')->get();
        $first = $changes->first()?->metadata['from'] ?? self::of($ticket);

        $history = [[
            'from' => null,
            'to' => $first,
            'at' => $ticket->approved_at ?? $ticket->created_at,
            'actor' => $ticket->approver?->name ?? 'Sistem',
            'reason' => $ticket->approval_required ? 'Ditentukan dari instruksi disposisi pimpinan.' : 'Layanan tanpa disposisi: langsung diproses back office.',
            'manual' => false,
        ]];

        foreach ($changes as $log) {
            $history[] = [
                'from' => $log->metadata['from'] ?? null,
                'to' => $log->metadata['to'] ?? self::of($ticket),
                'at' => $log->created_at,
                'actor' => $log->performer?->name ?? 'Sistem',
                'reason' => $log->metadata['reason'] ?? null,
                'manual' => (bool) ($log->metadata['manual'] ?? false),
            ];
        }

        return $history;
    }

    /** The effective category in SQL (indexed: tickets_effective_category_index). */
    public const EFFECTIVE_SQL = "coalesce(incoming_category, disposition_category, 'disposisi')";

    /** Limit a ticket query to one category. */
    public static function scope(Builder $query, string $category): Builder
    {
        return $query->whereRaw(self::EFFECTIVE_SQL . ' = ?', [$category]);
    }
}
