<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use App\Models\Visitor;
use App\Services\FrontDeskService;
use Illuminate\Database\Seeder;

/**
 * Contoh buku tamu (demo data, never seeded in production): earlier visits
 * for the reports, and today's guests of both kinds (a visiting guest, an
 * applicant come for a service), entered as the counter and the public form
 * enter them; some are still on site, one has left, one gave no phone, and
 * one from yesterday was never checked out.
 */
class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        Visitor::factory()->count(20)->create(['check_in_time' => fn () => fake()->dateTimeBetween(now()->subMonth(), now()->subDay())]);

        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->first();
        if (! $counter) {
            return; // UserSeeder has not run
        }
        $desk = app(FrontDeskService::class);
        $host = User::where('user_type', 'guru')->where('is_active', true)->orderBy('id')->first();
        $service = Service::where('is_active', true)->orderBy('name')->value('name') ?? 'Lainnya';

        $desk->registerGuest(['name' => 'Rina Kartika', 'phone' => '081234500001', 'institution' => 'Dinas Pendidikan Kota Malang', 'institution_category' => 'pemerintah',
            'purpose' => 'Waka Kurikulum', 'notes' => 'Koordinasi asesmen madrasah.', 'is_obscured' => true, 'person_to_meet_id' => $host?->id], $counter);
        $desk->registerGuest(['name' => 'Hendra Wijaya', 'phone' => '081234500002', 'institution' => 'SDN Merdeka', 'institution_category' => 'pendidikan',
            'purpose' => 'Lainnya', 'purpose_other' => 'Studi banding perpustakaan', 'is_obscured' => false], $counter);
        $desk->registerServiceApplicant(['name' => 'Budi Hartono', 'phone' => '081234500003', 'target_service' => $service,
            'notes' => 'Membawa berkas persyaratan.', 'is_obscured' => true], $counter);
        // From the public form: no officer.
        $desk->registerServiceApplicant(['name' => 'Sari Utami', 'phone' => '081234500004', 'institution' => 'PT Sinar Abadi', 'institution_category' => 'umkm',
            'target_service' => 'Lainnya', 'target_service_other' => 'Kerja sama praktik kerja lapangan', 'is_obscured' => true]);

        $left = $desk->registerGuest(['name' => 'Agus Salim', 'phone' => '081234500005', 'institution' => 'Komite Madrasah',
            'purpose' => 'Komite', 'is_obscured' => true], $counter);
        $desk->checkOut($left);

        // Registered without a phone (the API allows it): only the counter can check them out.
        $desk->registerGuest(['name' => 'Dewi Lestari', 'institution' => 'Wali murid kelas 8', 'purpose' => 'Wali Kelas', 'is_obscured' => true], $counter);

        // Yesterday's guest whom nobody checked out.
        Visitor::factory()->create(['name' => 'Joko Prasetyo', 'phone' => '081234500006', 'purpose' => 'Layanan PTSP', 'is_obscured' => true,
            'check_in_time' => today()->subDay()->setTime(10, 0), 'check_out_time' => null]);
    }
}
