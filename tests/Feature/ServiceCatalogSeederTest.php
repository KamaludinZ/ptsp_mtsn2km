<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Seeded katalog layanan: categories, disposition rules and requirement rows. */
class ServiceCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_service_is_categorised_and_configured(): void
    {
        $this->seed();

        foreach (ServiceCatalogSeeder::CATALOG as $code => [$category, $mode, $units, $signature]) {
            $service = Service::where('code', $code)->firstOrFail();
            $this->assertContains($category, $service->categories->pluck('name')->all(), $code);
            $this->assertSame($mode, $service->disposition_mode, $code);
            $this->assertSame($mode === 'none' ? null : ($units ?: null), $service->disposition_roles, $code);
            $this->assertGreaterThan(0, $service->requirements()->count(), $code);
        }

        $this->assertSame(['Formulir permohonan', 'Fotokopi Kartu Pelajar', 'Surat permohonan dari orang tua/wali'],
            Service::where('code', 'SKSA-001')->firstOrFail()->requirements()->orderBy('id')->pluck('requirement_name')->all());
    }

    public function test_seeding_again_keeps_admin_choices_and_creates_no_duplicates(): void
    {
        $this->seed();
        $service = Service::where('code', 'SKSA-001')->firstOrFail();
        \App\Support\ServiceDisposition::apply($service, 'kepsek', ['waka_humas'], 'ttd');
        $categories = ServiceCategory::count();
        $requirements = $service->requirements()->count();

        $this->seed([\Database\Seeders\ServiceSeeder::class, ServiceCatalogSeeder::class]);

        $service->refresh();
        $this->assertSame(['kepsek', ['waka_humas'], 'ttd'], [$service->disposition_mode, $service->disposition_roles, $service->signature_recommendation]);
        $this->assertSame($categories, ServiceCategory::count());
        $this->assertSame($requirements, $service->requirements()->count());
    }

    public function test_requirement_text_is_split_into_items(): void
    {
        $this->assertSame(['Formulir', 'Fotokopi KK', 'Pas foto 3x4'], ServiceCatalogSeeder::requirementsFrom('1. Formulir 2. Fotokopi KK; 3. Pas foto 3x4'));
        $this->assertSame([], ServiceCatalogSeeder::requirementsFrom(''));
    }
}
