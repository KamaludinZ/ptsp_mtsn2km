<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\ServiceDisposition;
use Illuminate\Database\Seeder;

/**
 * Katalog layanan setup (runs after ServiceSeeder and SchoolServicesSeeder,
 * safe to run again): each service's category, disposition rule
 * (mode, receiving units, signature recommendation) and its requirement
 * rows, parsed from the "1. … 2. …" requirement text.
 */
class ServiceCatalogSeeder extends Seeder
{
    /** code => [category, disposition mode, receiving units, signature recommendation] */
    public const CATALOG = [
        'SKSA-001' => ['Layanan Akademik', 'tu', ['tata_usaha'], 'tte'],
        'LIT-002' => ['Layanan Akademik', 'tu', ['tata_usaha'], 'ttd'],
        'SRB-003' => ['Layanan Akademik', 'kepsek', ['waka_kesiswaan'], 'ttd'],
        'SKBB-004' => ['Layanan Akademik', 'kepsek_tu', ['waka_kesiswaan', 'tata_usaha'], 'ttd'],
        'IAA-005' => ['Layanan Wali Murid', 'none', [], null],
        'ITMS-006' => ['Layanan Wali Murid', 'none', [], null],
        'KBK-007' => ['Layanan Wali Murid', 'none', [], null],
        'SPK-008' => ['Layanan Instansi', 'kepsek', ['waka_humas'], 'ttd'],
        'IKP-009' => ['Layanan Instansi', 'kepsek_tu', ['waka_kurikulum', 'tata_usaha'], 'ttd'],
        'PDS-010' => ['Layanan Instansi', 'tu', ['tata_usaha'], 'tte'],
        'PSB-011' => ['Layanan Umum', 'kepsek_tu', ['waka_kesiswaan', 'tata_usaha'], null],
        'PCS-012' => ['Layanan Umum', 'tu', ['tata_usaha'], null],
        'BKO-013' => ['Layanan Wali Murid', 'none', [], null],
        'PBO-014' => ['Layanan Akademik', 'kepsek', ['waka_kesiswaan'], 'ttd'],
        'SKCK-015' => ['Layanan Akademik', 'kepsek_tu', ['waka_kesiswaan'], 'ttd'],
        'EKS-016' => ['Layanan Akademik', 'none', [], null],
        'ISB-017' => ['Layanan Instansi', 'kepsek', ['waka_humas'], 'ttd'],
        'PERPUS-018' => ['Layanan Umum', 'none', [], null],
        'STG-019' => ['Layanan Umum', 'kepsek', ['tata_usaha'], 'tte'],
        'PKL-020' => ['Layanan Akademik', 'kepsek_tu', ['waka_kurikulum', 'waka_humas'], 'ttd'],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as $code => [$category, $mode, $units, $signature]) {
            $service = Service::where('code', $code)->first();
            if (! $service) {
                continue;
            }

            $categoryId = ServiceCategory::firstOrCreate(['name' => $category])->id;
            $service->categories()->syncWithoutDetaching([$categoryId]);

            // Only services nobody configured yet; an admin's choice is kept.
            if (ServiceDisposition::usesDefault($service) && ! $service->disposition_roles && ! $service->signature_recommendation) {
                ServiceDisposition::apply($service, $mode, $units, $signature);
            }

            if (! $service->requirements()->exists()) {
                foreach (self::requirementsFrom((string) $service->getAttributes()['requirements']) as $name) {
                    $service->requirements()->create(['requirement_name' => $name, 'is_required' => true]);
                }
            }
        }
    }

    /** "1. Formulir 2. Fotokopi KK" => ["Formulir", "Fotokopi KK"] */
    public static function requirementsFrom(string $text): array
    {
        $parts = preg_split('/\s*\d+\.\s+/', strip_tags($text), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(array_map(fn (string $part) => trim($part, " \t\n\r\0\x0B;,"), $parts ?: [])));
    }
}
