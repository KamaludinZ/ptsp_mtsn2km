<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Visitor;
use App\Services\FrontDeskService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** visitors.guest_type, applicant_type and service_id: one visitor book for both kinds of guest. */
class VisitorGuestTypeSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_10_28_200000_add_guest_type_to_visitors.php';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function service(): Service
    {
        return Service::where('is_active', true)->orderBy('name')->firstOrFail();
    }

    public function test_the_counter_and_the_public_form_record_the_kind_status_and_service(): void
    {
        $desk = app(FrontDeskService::class);
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();

        $visit = $desk->registerGuest(['name' => 'Tamu', 'phone' => '081200000001', 'purpose' => 'Komite'], $counter);
        $this->assertSame(['kunjungan', null, null], [$visit->fresh()->guest_type, $visit->applicant_type, $visit->service_id]);

        $applicant = $desk->registerServiceApplicant(['name' => 'Pemohon', 'phone' => '081200000002', 'applicant_type' => 'wali_murid', 'target_service' => $this->service()->name], $counter);
        $this->assertSame(['pemohon', 'wali_murid', $this->service()->id], [$applicant->fresh()->guest_type, $applicant->applicant_type, $applicant->service_id]);
        $this->assertTrue($applicant->service->is($this->service()));

        $other = $desk->registerServiceApplicant(['name' => 'Lain', 'phone' => '081200000003', 'target_service' => 'Lainnya', 'target_service_other' => 'Kerja sama PKL']);
        $this->assertSame(['pemohon', null, 'Pemohon Layanan: Kerja sama PKL'], [$other->fresh()->guest_type, $other->service_id, $other->purpose]);

        // The public form keeps the applicant's status now; an unknown one is refused.
        $form = ['name' => 'Publik', 'phone' => '081200000004', 'applicant_type' => 'alumni', 'target_service' => $this->service()->name];
        $this->post(route('public.applicant.submit'), $form)->assertRedirect(route('public.visitor.book'));
        $this->assertSame(['pemohon', 'alumni'], [Visitor::where('name', 'Publik')->value('guest_type'), Visitor::where('name', 'Publik')->value('applicant_type')]);
        $this->post(route('public.applicant.submit'), [...$form, 'name' => 'Asal', 'applicant_type' => 'tamu'])->assertSessionHasErrors('applicant_type');
    }

    public function test_the_database_refuses_an_unknown_kind_or_status(): void
    {
        $visitor = Visitor::firstOrFail();

        foreach ([['guest_type' => 'vip'], ['applicant_type' => 'tamu']] as $value) {
            try {
                DB::transaction(fn () => DB::table('visitors')->where('id', $visitor->id)->update($value));
                $this->fail('Accepted ' . json_encode($value));
            } catch (QueryException $e) {
                $this->assertStringContainsString('check', $e->getMessage());
            }
        }
    }

    public function test_the_migration_recognises_existing_applicants_and_their_service(): void
    {
        $migration = require database_path('migrations/' . self::MIGRATION);
        $migration->down();

        $now = now();
        $row = fn (string $name, string $purpose) => ['name' => $name, 'purpose' => $purpose, 'check_in_time' => $now, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now];
        DB::table('visitors')->insert([
            $row('Lama Pemohon', 'Pemohon Layanan: ' . $this->service()->name),
            $row('Lama Lainnya', 'Pemohon Layanan: Kerja sama PKL'),
            $row('Lama Tamu', 'Koordinasi dengan Waka Humas'),
        ]);
        $migration->up();

        $rows = DB::table('visitors')->whereIn('name', ['Lama Pemohon', 'Lama Lainnya', 'Lama Tamu'])->get()->keyBy('name');
        $this->assertSame(['pemohon', $this->service()->id], [$rows['Lama Pemohon']->guest_type, $rows['Lama Pemohon']->service_id]);
        $this->assertSame(['pemohon', null], [$rows['Lama Lainnya']->guest_type, $rows['Lama Lainnya']->service_id]);
        $this->assertSame(['kunjungan', null], [$rows['Lama Tamu']->guest_type, $rows['Lama Tamu']->service_id]);
        $this->assertSame('Pemohon Layanan: Kerja sama PKL', $rows['Lama Lainnya']->purpose); // the purpose stays as shown
    }
}
