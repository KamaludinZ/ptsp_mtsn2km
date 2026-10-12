<?php

namespace Tests\Feature;

use App\Mail\TemplateNotificationMail;
use App\Models\Complaint;
use App\Models\NotificationDelivery;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\ComplaintService;
use App\Support\WhatsAppGateways;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Notifikasi pengaduan, saran & WBS: the reporter and the complaint handlers
 * (admin, kepala madrasah, kepala TU only) hear of a report by e-mail and
 * WhatsApp; staff messages never leak a secret report's subject.
 */
class ComplaintNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, User> */
    private array $staff = [];

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response(['status' => true])]);
        $this->seed(NotificationTemplateSeeder::class);

        foreach (['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor', 'back_office'] as $i => $role) {
            $this->staff[$role] = tap(User::factory()->create([
                'email' => "{$role}@madrasah.test",
                'whatsapp_number' => '62811000000' . $i,
                'is_active' => true,
            ]))->assignRole(Role::findOrCreate($role, 'web'));
        }

        NotificationSetting::store('email', true, ['host' => 'smtp.test', 'port' => 25, 'from_address' => 'ptsp@test']);
        NotificationSetting::store('whatsapp', true, ['api_url' => 'https://wa.test/send', 'api_token' => 't', 'sender_id' => '628000']);
    }

    private function complaint(): Complaint
    {
        return app(ComplaintService::class)->submit('complaint', [
            'reporter_name' => 'Siti Pelapor',
            'reporter_email' => 'siti@warga.test',
            'reporter_phone' => '081234567890',
            'complaint_title' => 'Antrean legalisir lama',
            'complaint_description' => 'Menunggu lebih dari dua jam.',
        ]);
    }

    public function test_a_new_complaint_reaches_the_reporter_and_only_the_complaint_handlers(): void
    {
        $complaint = $this->complaint();

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo('siti@warga.test')
            && str_contains($mail->body, 'Yth. Siti Pelapor')
            && str_contains($mail->body, $complaint->complaint_number));
        Http::assertSent(fn ($request) => $request['number'] === '081234567890' && str_contains($request['message'], $complaint->complaint_number));

        foreach (['admin', 'kepala_sekolah', 'kepala_tu'] as $role) {
            Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo("{$role}@madrasah.test")
                && str_contains($mail->body, 'Antrean legalisir lama')
                && ! str_contains($mail->body, 'Siti Pelapor'));
            Http::assertSent(fn ($request) => $request['number'] === $this->staff[$role]->whatsapp_number);
        }

        foreach (['supervisor', 'back_office'] as $role) {
            Mail::assertNotSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo("{$role}@madrasah.test"));
            Http::assertNotSent(fn ($request) => $request['number'] === $this->staff[$role]->whatsapp_number);
        }

        $this->assertSame(8, NotificationDelivery::where('complaint_id', $complaint->id)->where('status', 'sent')->count());
    }

    public function test_the_reporter_hears_of_the_follow_up_and_the_reply(): void
    {
        $complaint = $this->complaint();
        Mail::fake();

        app(ComplaintService::class)->followUp($complaint, [
            'status' => 'resolved',
            'priority' => 'normal',
            'response' => 'Petugas loket sudah ditambah.',
        ], $this->staff['kepala_tu']);

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo('siti@warga.test')
            && $mail->subjectLine === "Tindak lanjut {$complaint->complaint_number}: Selesai"
            && str_contains($mail->body, 'Tanggapan petugas: Petugas loket sudah ditambah.'));
        Mail::assertSent(TemplateNotificationMail::class, 1);

        // Only the priority changed: nothing new for the reporter.
        Mail::fake();
        app(ComplaintService::class)->followUp($complaint->fresh(), ['status' => 'resolved', 'priority' => 'high'], $this->staff['kepala_tu']);
        Mail::assertNothingSent();
    }

    public function test_whistleblowing_keeps_its_subject_and_anonymous_reporters_out_of_messages(): void
    {
        app(ComplaintService::class)->submit('whistleblowing', [
            'complaint_title' => 'Dugaan pungutan liar',
            'complaint_description' => 'Uraian kejadian.',
            'anonymous' => true,
        ]);

        Mail::assertSent(TemplateNotificationMail::class, 3);
        Mail::assertNotSent(TemplateNotificationMail::class, fn ($mail) => str_contains($mail->body, 'Dugaan pungutan liar'));
        Http::assertNotSent(fn ($request) => str_contains($request['message'], 'Dugaan pungutan liar'));
    }

    public function test_a_suggestion_without_contact_only_notifies_staff(): void
    {
        app(ComplaintService::class)->submit('suggestion', ['suggestion' => 'Tambah kursi ruang tunggu.']);

        Mail::assertSent(TemplateNotificationMail::class, 3);
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo('admin@madrasah.test') && str_contains($mail->body, 'Saran'));
    }

    public function test_balesotomatis_gateway_gets_its_own_request_shape(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['api.balesotomatis.id/*' => Http::sequence()
            ->push(['code' => '200', 'message' => 'The message is in the delivery process', 'type' => 'successfully'])
            ->push(['code' => '403', 'message' => 'Invalid API Key'])]);
        NotificationSetting::store('whatsapp', true, [
            'provider' => 'balesotomatis',
            'api_url' => WhatsAppGateways::DEFAULT_URLS['balesotomatis'],
            'api_token' => 'key-bales',
            'sender_id' => 'NUM-1',
        ]);

        $this->assertTrue(app(\App\Services\WhatsAppService::class)->sendMessage('0812-3456-7890', 'Halo'));
        Http::assertSent(fn ($request) => $request->url() === 'https://api.balesotomatis.id/public/v1/send_personal_message'
            && $request['api_key'] === 'key-bales' && $request['number_id'] === 'NUM-1'
            && $request['phone_no'] === '81234567890' && $request['country_code'] === '62' && $request['message'] === 'Halo');

        $this->assertFalse(app(\App\Services\WhatsAppService::class)->sendMessage('6281234567890', 'Halo'));
        $this->assertTrue(WhatsAppGateways::needsSender('balesotomatis'));
    }
}
