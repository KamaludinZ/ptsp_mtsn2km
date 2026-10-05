<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian kata kunci on the request list: every word must match one of
 * ticket number, request text, applicant (name, e-mail, WhatsApp), service
 * or assigned officer. Case-insensitive; WhatsApp numbers match whether
 * typed as 0812…, 62812… or +62 812-….
 */
class TicketSearch
{
    public const MIN_LENGTH = 2;

    public static function apply(Builder $query, ?string $search): Builder
    {
        foreach (self::words($search) as $word) {
            $like = '%' . addcslashes(mb_strtolower($word), '%_\\') . '%';
            $phones = self::phoneVariants($word);

            $query->where(function (Builder $q) use ($like, $phones) {
                $q->whereRaw('lower(ticket_number) like ?', [$like])
                    ->orWhereRaw('lower(coalesce(notes, \'\')) like ?', [$like])
                    ->orWhereHas('service', fn (Builder $s) => $s->whereRaw('lower(name) like ?', [$like]))
                    ->orWhereHas('assignedTo', fn (Builder $u) => $u->whereRaw('lower(name) like ?', [$like]))
                    ->orWhereHas('user', function (Builder $u) use ($like, $phones) {
                        $u->where(function (Builder $u) use ($like, $phones) {
                            $u->whereRaw('lower(name) like ?', [$like])
                                ->orWhereRaw('lower(email) like ?', [$like]);
                            foreach ($phones as $phone) {
                                $u->orWhereRaw("regexp_replace(coalesce(whatsapp_number, ''), '[^0-9]', '', 'g') like ?", ['%' . $phone . '%']);
                            }
                        });
                    });
            });
        }

        return $query;
    }

    /** @return array<int, string> */
    public static function words(?string $search): array
    {
        return collect(preg_split('/\s+/', trim((string) $search)) ?: [])
            ->filter(fn (string $word) => mb_strlen($word) >= self::MIN_LENGTH)
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    /** "0812-3" -> ["08123", "628123"]; non-numbers (fewer than 4 digits) -> []. */
    public static function phoneVariants(string $word): array
    {
        $digits = preg_replace('/\D/', '', $word);
        if (strlen($digits) < 4 || preg_match('/[A-Za-z]/', $word)) {
            return [];
        }

        return array_values(array_unique(array_filter([
            $digits,
            str_starts_with($digits, '0') ? '62' . substr($digits, 1) : null,
            str_starts_with($digits, '62') ? '0' . substr($digits, 2) : null,
        ])));
    }
}
