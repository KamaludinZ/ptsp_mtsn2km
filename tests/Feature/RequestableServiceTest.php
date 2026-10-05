<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Models\Service;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Only active services, for the right applicant and channel, accept requests. */
class RequestableServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail(); // umum
    }

    public function test_scope_combines_active_applicant_type_and_channel(): void
    {
        $online = Service::factory()->create(['mode' => 'online', 'user_types_allowed' => ['umum'], 'is_active' => true]);
        $counterOnly = Service::factory()->create(['mode' => 'offline', 'user_types_allowed' => ['umum'], 'is_active' => true]);
        $inactive = Service::factory()->create(['mode' => 'online', 'user_types_allowed' => ['umum'], 'is_active' => false]);
        $students = Service::factory()->create(['mode' => 'online', 'user_types_allowed' => ['siswa'], 'is_active' => true]);

        $online_ = Service::requestable('umum', 'online')->pluck('id');
        $this->assertContains($online->id, $online_);
        $this->assertNotContains($counterOnly->id, $online_);
        $this->assertNotContains($inactive->id, $online_);
        $this->assertNotContains($students->id, $online_);
        $this->assertContains($counterOnly->id, Service::requestable('umum', 'offline')->pluck('id'));
    }

    public function test_a_service_switched_off_while_the_form_was_open_refuses_the_request(): void
    {
        $service = Service::requestable('umum', 'online')->firstOrFail();
        $stale = Service::findOrFail($service->id); // as loaded when the form opened
        $before = $service->tickets()->count();
        $service->update(['is_active' => false]);

        try {
            app(TicketService::class)->open($stale, $this->applicant, $this->applicant, 'online', 'Permohonan.');
            $this->fail('An inactive service must not accept requests.');
        } catch (TicketActionException $e) {
            $this->assertSame('Layanan ini sedang tidak menerima permohonan.', $e->getMessage());
        }
        $this->assertSame($before, $service->tickets()->count());
    }

    public function test_online_requests_need_an_online_service_but_the_counter_takes_all(): void
    {
        $counterOnly = Service::factory()->create(['mode' => 'offline', 'user_types_allowed' => ['umum'], 'is_active' => true]);
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();

        $this->expectException(TicketActionException::class);
        try {
            app(TicketService::class)->open($counterOnly, $this->applicant, $this->applicant, 'online', 'Online.');
        } finally {
            $ticket = app(TicketService::class)->open($counterOnly, $this->applicant, $counter, 'offline', 'Di loket.');
            $this->assertSame('offline', $ticket->mode);
        }
    }

    public function test_applicants_list_the_services_they_can_request(): void
    {
        $counterOnly = Service::factory()->create(['name' => 'Hanya Loket', 'mode' => 'offline', 'user_types_allowed' => ['umum'], 'is_active' => true]);
        Sanctum::actingAs($this->applicant);

        $response = $this->getJson('/api/permohonan/layanan')->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');

        $this->assertEquals(Service::requestable('umum', 'online')->orderBy('name')->pluck('slug'), $slugs);
        $this->assertNotContains($counterOnly->slug, $slugs);
        $this->assertSame(route('api.publik.layanan.template', $slugs->first()), $response->json('data.0.template'));

        $this->postJson('/api/permohonan', ['layanan' => $counterOnly->slug, 'keterangan' => 'Coba online'])
            ->assertStatus(422)->assertJsonPath('message', 'Layanan tidak tersedia untuk kategori akun Anda.');
    }
}
