<?php

namespace Tests\Feature;

use App\Filament\Portal\Resources\TicketResource as PortalTicketResource;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\TicketDocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Downloading a finished request's product from public tracking (/tracking). */
class PublicOutputDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function demo(): void
    {
        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->seed(TicketDocumentSeeder::class);
    }

    private function track(Ticket $ticket, ?string $email = null)
    {
        return $this->postJson(route('onlineportal.track.ticket.result'), ['ticket_number' => $ticket->ticket_number, 'email' => $email])->assertOk();
    }

    public function test_tracking_tells_whether_and_how_the_product_file_is_reached(): void
    {
        $this->demo();
        $done = Ticket::where('status', 'completed')->whereHas('output')->oldest('id')->firstOrFail();
        $open = Ticket::where('status', 'submitted')->firstOrFail();

        $this->track($done)
            ->assertJsonPath('hasil.berkas', true)
            ->assertJsonPath('hasil.akun', null) // not told to someone who only knows the number
            ->assertJsonPath('hasil.masuk_url', PortalTicketResource::getUrl('view', ['record' => $done], panel: 'portal'));
        $this->track($open)->assertJsonPath('hasil.berkas', false);

        // The verified owner learns whether to sign in.
        $done->user->forceFill(['last_login_at' => now()])->save();
        $this->track($done, $done->user->email)->assertJsonPath('hasil.akun', true);

        // A file no longer on the disk is nothing to download.
        Storage::disk('local')->delete($done->output->file_path);
        $this->track($done)->assertJsonPath('hasil.berkas', false);
    }

    public function test_who_signs_in(): void
    {
        $this->assertTrue((new User(['email' => 'rina@contoh.id']))->forceFill(['last_login_at' => now(), 'is_active' => true])->signsIn());
        $this->assertNull((new User(['email' => 'rina@contoh.id']))->forceFill(['last_login_at' => null, 'is_active' => true])->signsIn());
        $this->assertFalse((new User(['email' => '081234567890@walkin.local']))->forceFill(['last_login_at' => now(), 'is_active' => true])->signsIn());
        $this->assertFalse((new User(['email' => 'rina@contoh.id']))->forceFill(['last_login_at' => now(), 'is_active' => false])->signsIn());
    }

    public function test_tracking_has_a_hasil_layanan_panel_shown_for_a_finished_request(): void
    {
        $this->get(route('onlineportal.track.ticket.form'))->assertOk()
            ->assertSeeInOrder(['id="outputPanel"', 'hidden', 'Hasil layanan'], false)
            ->assertSee('id="outputSignInLink"', false)
            ->assertSee('Masuk untuk mengunduh')
            ->assertSee('id="outputVerifyButton"', false)
            ->assertSee('Verifikasi lalu unduh')
            ->assertSee('id="outputPickup"', false)
            ->assertDontSee('id="downloadButton"', false) // the old "online only" button
            // Before a request is finished, and when it ends without a product:
            ->assertSee('Permohonan masih diproses.')
            ->assertSee('Permohonan ditolak, sehingga tidak ada hasil layanan.')
            ->assertSee('Permohonan dibatalkan, sehingga tidak ada hasil layanan.');
    }

    public function test_tracking_has_the_verification_form_for_an_applicant_without_an_account(): void
    {
        $this->get(route('onlineportal.track.ticket.form'))->assertOk()
            ->assertSee('id="verifyModal"', false)
            ->assertSee('Email atau nomor WhatsApp saat mengajukan')
            ->assertSee('name="kontak"', false)
            ->assertSee(url('/tracking/unduh'), false)
            // Checked in the browser before sending:
            ->assertSee('Alamat email belum lengkap.')
            ->assertSee('Nomor WhatsApp terlalu pendek.')
            // Wrong tries left, and the lock with its countdown:
            ->assertSee('id="verifyAttempts"', false)
            ->assertSee('Sisa percobaan: ${left} dari ${VERIFY_ATTEMPTS}.', false)
            ->assertSee('id="verifyLock"', false)
            ->assertSee('id="verifyCountdown"', false);
    }
}
