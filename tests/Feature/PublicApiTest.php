<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Public API: no sign-in needed, never staff or applicant data. */
class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_contact_details_and_hours(): void
    {
        AppSetting::set('contact_phone', '(0341) 123456');
        AppSetting::set('operating_hours_weekday', '07.00 - 15.00 WIB');

        $this->getJson('/api/publik/profil')
            ->assertOk()
            ->assertJsonPath('nama', app_brand_name())
            ->assertJsonPath('kontak.telepon', '(0341) 123456')
            ->assertJsonPath('jam_layanan.senin_kamis', '07.00 - 15.00 WIB');
    }

    public function test_catalog_lists_public_services_with_details(): void
    {
        $this->seed();
        $public = Service::where('is_active', true)->availableFor('umum')->firstOrFail();
        $civitasOnly = Service::where('is_active', true)->whereJsonDoesntContain('user_types_allowed', 'umum')->first();
        $public->templates()->create(['nama' => 'Formulir A', 'file_path' => 'service-templates/a.pdf', 'file_name' => 'a.pdf', 'is_required' => true]);

        $slugs = collect($this->getJson('/api/publik/layanan')->assertOk()->json('data'))->pluck('slug');
        $this->assertTrue($slugs->contains($public->slug));
        if ($civitasOnly) {
            $this->assertFalse($slugs->contains($civitasOnly->slug));
            $this->getJson('/api/publik/layanan/' . $civitasOnly->slug)->assertNotFound();
        }

        $this->getJson('/api/publik/layanan/' . $public->slug)
            ->assertOk()
            ->assertJsonPath('nama', $public->name)
            ->assertJsonPath('template_berkas.0.nama', 'Formulir A')
            ->assertJsonPath('template_berkas.0.wajib', true);

        $this->getJson('/api/publik/layanan?q=' . urlencode(mb_substr($public->name, 0, 5)))->assertOk()->assertJsonFragment(['slug' => $public->slug]);
    }

    public function test_only_published_announcements_are_listed(): void
    {
        $live = Pengumuman::create(['title' => 'Libur semester', 'content' => 'Layanan tutup 1-5 Januari.', 'category' => 'umum', 'publish_date' => today()->subDay(), 'is_active' => true]);
        $draft = Pengumuman::create(['title' => 'Draf internal', 'content' => 'x', 'category' => 'umum', 'publish_date' => today()->subDay(), 'is_active' => false]);
        $future = Pengumuman::create(['title' => 'Belum terbit', 'content' => 'x', 'category' => 'umum', 'publish_date' => today()->addWeek(), 'is_active' => true]);
        $expired = Pengumuman::create(['title' => 'Sudah lewat', 'content' => 'x', 'category' => 'umum', 'publish_date' => today()->subMonth(), 'end_date' => today()->subWeek(), 'is_active' => true]);

        $titles = collect($this->getJson('/api/publik/pengumuman')->assertOk()->json('data'))->pluck('judul');
        $this->assertSame(['Libur semester'], $titles->all());

        $this->getJson('/api/publik/pengumuman/' . $live->id)->assertOk()->assertJsonPath('isi', 'Layanan tutup 1-5 Januari.');
        foreach ([$draft, $future, $expired] as $hidden) {
            $this->getJson('/api/publik/pengumuman/' . $hidden->id)->assertNotFound();
        }
        $this->getJson('/api/publik/pengumuman?q=semester')->assertJsonPath('total', 1);
    }

    public function test_faq_lists_active_questions_with_safe_answers(): void
    {
        Faq::forceCreate(['question' => 'Berapa biaya legalisir?', 'answer' => '<p>Gratis.</p><script>alert(1)</script>', 'is_active' => true]);
        Faq::forceCreate(['question' => 'Tersembunyi', 'answer' => 'x', 'is_active' => false]);

        $data = $this->getJson('/api/publik/faq')->assertOk()->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Gratis.', $data[0]['jawaban']);
        $this->assertStringNotContainsString('<script', $data[0]['jawaban']);

        $this->getJson('/api/publik/faq?q=biaya')->assertJsonCount(1, 'data');
        $this->getJson('/api/publik/faq?q=mutasi')->assertJsonCount(0, 'data');
    }

    public function test_tracking_shows_progress_to_anyone_and_details_only_to_the_applicant(): void
    {
        $this->seed();
        $ticket = Ticket::with('user')->whereHas('user')->firstOrFail();
        $ticket->update(['notes' => 'Keperluan pribadi pemohon']);
        TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note_added', 'performed_by' => User::where('email', 'staff1@mtsn2malang.sch.id')->value('id'), 'notes' => 'Catatan petugas internal']);
        $url = '/api/publik/lacak/' . $ticket->ticket_number;

        $anyone = $this->getJson($url)->assertOk()->assertJsonPath('verified_owner', false)->assertJsonPath('description', null);
        $this->assertStringNotContainsString('Catatan petugas internal', $anyone->getContent());
        $this->assertStringNotContainsString('Keperluan pribadi pemohon', $anyone->getContent());
        $this->assertNotContains('Catatan', collect($anyone->json('logs'))->pluck('action_label')->all());

        $owner = $this->getJson($url . '?email=' . urlencode(strtoupper($ticket->user->email)))
            ->assertOk()->assertJsonPath('verified_owner', true)->assertJsonPath('description', 'Keperluan pribadi pemohon');
        // Internal notes stay with staff even for the applicant.
        $this->assertStringNotContainsString('Catatan petugas internal', $owner->getContent());

        $this->getJson($url . '?email=orang.lain@example.test')->assertNotFound();
        $this->getJson('/api/publik/lacak/TIDAK-ADA')->assertNotFound();

        // The tracking page uses the same rules.
        $this->postJson(route('onlineportal.track.ticket.result'), ['ticket_number' => $ticket->ticket_number])
            ->assertOk()->assertJsonPath('description', null);
        $this->postJson(route('onlineportal.track.ticket.result'), ['ticket_number' => $ticket->ticket_number, 'email' => $ticket->user->email])
            ->assertOk()->assertJsonPath('description', 'Keperluan pribadi pemohon');
    }
}
