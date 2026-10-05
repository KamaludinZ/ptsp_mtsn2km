<?php

namespace App\Enums;

/** How a disposition is signed (disposition_logs.signature_model). */
enum SignatureModel: string
{
    case TtdUpload = 'ttd_upload';
    case TteUpload = 'tte_upload';
    case AcknowledgedBy = 'acknowledged_by';

    public function label(): string
    {
        return match ($this) {
            self::TtdUpload => 'TTD — cetak, tanda tangani, lalu unggah',
            self::TteUpload => 'TTE — unduh, tanda tangani elektronik, lalu unggah',
            self::AcknowledgedBy => 'Tanda "telah didisposisi oleh" (tanpa berkas)',
        };
    }

    /** The short code kept on tickets.signature_type. */
    public function ticketCode(): string
    {
        return match ($this) {
            self::TtdUpload => 'ttd',
            self::TteUpload => 'tte',
            self::AcknowledgedBy => 'ack',
        };
    }

    /** Models that expect a signed file to be uploaded. */
    public function needsFile(): bool
    {
        return $this !== self::AcknowledgedBy;
    }

    /** @return array<string, string> value => label, for forms */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $model) => [$model->value => $model->label()])->all();
    }
}
