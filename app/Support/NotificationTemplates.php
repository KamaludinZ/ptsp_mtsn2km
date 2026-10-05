<?php

namespace App\Support;

/**
 * The notifications the application sends and the placeholders their
 * templates may use. The defaults seed notification_templates.
 */
class NotificationTemplates
{
    public const EVENTS = [
        'ticket_created' => 'Permohonan diterima',
        'ticket_status_changed' => 'Status permohonan berubah',
        'disposition_assigned' => 'Disposisi untuk petugas',
        'ticket_completed' => 'Permohonan selesai',
        'ticket_rejected' => 'Permohonan ditolak',
        'survey_reminder' => 'Pengingat survei kepuasan',
    ];

    public const PLACEHOLDERS = [
        'nama' => 'Nama penerima',
        'nomor_tiket' => 'Nomor tiket',
        'layanan' => 'Nama layanan',
        'status' => 'Status terbaru',
        'catatan' => 'Catatan petugas / alasan',
        'tautan' => 'Tautan pelacakan atau detail',
        'instansi' => 'Nama madrasah',
    ];

    /** @return array<string, array{subject: string, body: string}> keyed by event */
    public static function defaults(): array
    {
        return [
            'ticket_created' => [
                'subject' => 'Permohonan {nomor_tiket} diterima',
                'body' => "Yth. {nama},\nPermohonan layanan {layanan} Anda telah kami terima dengan nomor {nomor_tiket}.\nLacak statusnya di {tautan}.\n\n{instansi}",
            ],
            'ticket_status_changed' => [
                'subject' => 'Status permohonan {nomor_tiket}: {status}',
                'body' => "Yth. {nama},\nStatus permohonan {nomor_tiket} ({layanan}) kini: {status}.\n{catatan}\nDetail: {tautan}\n\n{instansi}",
            ],
            'disposition_assigned' => [
                'subject' => 'Disposisi baru: {nomor_tiket}',
                'body' => "Yth. {nama},\nAnda menerima disposisi untuk permohonan {nomor_tiket} ({layanan}).\nInstruksi: {catatan}\nBuka: {tautan}",
            ],
            'ticket_completed' => [
                'subject' => 'Permohonan {nomor_tiket} selesai',
                'body' => "Yth. {nama},\nPermohonan {layanan} dengan nomor {nomor_tiket} telah selesai.\nUnduh atau ambil hasilnya melalui {tautan}.\n\n{instansi}",
            ],
            'ticket_rejected' => [
                'subject' => 'Permohonan {nomor_tiket} tidak dapat diproses',
                'body' => "Yth. {nama},\nMohon maaf, permohonan {nomor_tiket} ({layanan}) tidak dapat diproses.\nAlasan: {catatan}\n\n{instansi}",
            ],
            'survey_reminder' => [
                'subject' => 'Bagaimana layanan {layanan} kami?',
                'body' => "Yth. {nama},\nPermohonan {nomor_tiket} ({layanan}) telah selesai. Mohon luangkan 2 menit untuk menilai layanan kami melalui {tautan}.\nPenilaian Anda membantu kami memperbaiki pelayanan.\n\n{instansi}",
            ],
        ];
    }

    /** Replace {placeholders}; unknown ones are left as typed. */
    public static function render(string $text, array $values): string
    {
        return preg_replace_callback('/\{([a-z_]+)\}/', fn ($m) => array_key_exists($m[1], $values) ? (string) $values[$m[1]] : $m[0], $text);
    }

    /** Placeholders in $text that the app does not fill (likely typos). */
    public static function unknownPlaceholders(?string $text): array
    {
        preg_match_all('/\{([a-z_]+)\}/', (string) $text, $matches);

        return array_values(array_diff(array_unique($matches[1]), array_keys(self::PLACEHOLDERS)));
    }

    /** A validation rule (Laravel or Filament) rejecting unknown placeholders. */
    public static function placeholderRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if ($unknown = self::unknownPlaceholders($value)) {
                $fail('Placeholder tidak dikenal: {' . implode('}, {', $unknown) . '}.');
            }
        };
    }
}
