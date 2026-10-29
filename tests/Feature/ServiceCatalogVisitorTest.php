<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceRichTextSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** How /services and /services/{slug} fetch services for each kind of visitor. */
class ServiceCatalogVisitorTest extends TestCase
{
    use RefreshDatabase;

    private Service $forStudents;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->seed(ServiceRichTextSeeder::class);
        $this->forStudents = Service::factory()->create(['name' => 'Konseling Siswa', 'mode' => 'online', 'is_active' => true, 'user_types_allowed' => ['siswa', 'walimurid']]);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_staff_see_and_open_every_active_service(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $shown = substr_count($this->get('/services')->assertOk()->getContent(), 'class="service-detail accordion-body"');
        $this->assertSame(Service::where('is_active', true)->count(), $shown);
        $this->get(route('onlineportal.service.detail', $this->forStudents->slug))->assertOk()->assertSee('Konseling Siswa');
    }

    public function test_applicants_and_guests_see_only_what_is_open_to_them(): void
    {
        $this->get('/services')->assertOk()->assertDontSee('Konseling Siswa');
        $this->get(route('onlineportal.service.detail', $this->forStudents->slug))->assertNotFound();

        $this->actingAs($this->user('ahmad.rizki.parent@gmail.com')); // walimurid
        $this->get('/services')->assertOk()->assertSee('Konseling Siswa');
        $this->get(route('onlineportal.service.detail', $this->forStudents->slug))->assertOk();
    }

    public function test_an_inactive_service_page_is_for_staff_only(): void
    {
        $inactive = Service::where('code', 'SKP-021')->firstOrFail();
        $inactive->update(['is_active' => false]);

        $this->get(route('onlineportal.service.detail', $inactive->slug))->assertNotFound();
        $this->actingAs($this->user('ahmad.rizki.parent@gmail.com'))->get(route('onlineportal.service.detail', $inactive->slug))->assertNotFound();

        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))->get(route('onlineportal.service.detail', $inactive->slug))->assertOk()
            ->assertSee('Layanan ini sedang nonaktif')
            ->assertDontSee('Ajukan Layanan Online');
    }

    public function test_a_staff_member_who_is_an_applicant_applies_only_where_their_type_is_open(): void
    {
        $teacher = User::factory()->create(['user_type' => 'guru']);
        $teacher->assignRole('waka_kurikulum', 'guru');
        $this->actingAs($teacher);

        $html = $this->get('/services')->assertOk()->getContent();
        $card = substr($html, strpos($html, 'Konseling Siswa'));
        $card = substr($card, 0, strpos($card, 'class="service-detail accordion-body"') + 4000);
        $this->assertStringNotContainsString('href="' . route('onlineportal.service.apply', $this->forStudents->slug) . '"', $card);
        $this->assertStringContainsString('data-staff-note', $card);
    }

    public function test_the_queries_do_not_grow_with_the_number_of_services(): void
    {
        foreach (['ahmad.rizki.parent@gmail.com', 'staff1@mtsn2malang.sch.id', 'loket1@mtsn2malang.sch.id'] as $email) {
            $this->actingAs($this->user($email));
            $counts = [];
            foreach ([0, 10] as $more) {
                Service::factory()->count($more)->create(['is_active' => true, 'mode' => 'online', 'user_types_allowed' => null]);
                $this->get('/services')->assertOk(); // warm caches
                DB::enableQueryLog();
                DB::flushQueryLog();
                $this->get('/services')->assertOk();
                $counts[] = count(DB::getQueryLog());
                DB::disableQueryLog();
            }

            $this->assertLessThanOrEqual($counts[0], $counts[1], "{$email}: queries {$counts[0]} → {$counts[1]} with 10 more services");
        }
    }
}
