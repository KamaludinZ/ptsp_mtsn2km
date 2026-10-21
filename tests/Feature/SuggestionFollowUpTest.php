<?php

namespace Tests\Feature;

use App\Filament\Resources\SuggestionResource\Pages\ListSuggestions;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Saran: the sender may leave a phone number, staff answer it (with a
 * staff-only note), and the sender reads the answer by tracking the
 * suggestion number with the e-mail or phone they gave.
 */
class SuggestionFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Http::fake();
    }

    private function leader(string $role = 'kepala_sekolah'): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    public function test_the_saran_tab_is_green_and_takes_a_phone_number(): void
    {
        $this->get('/complaints?tab=saran')->assertOk()
            ->assertSee('.nav-tabs-visitor .nav-link#saran-alt-tab', false)
            ->assertSee('#198754', false)
            ->assertSee('name="reporter_phone"', false);

        $this->post('/complaints/saran', ['suggestion' => 'Perpanjang jam layanan loket.', 'reporter_phone' => 'nomor saya'])
            ->assertSessionHasErrors('reporter_phone');

        $this->post('/complaints/saran', [
            'suggestion' => 'Perpanjang jam layanan loket.',
            'reporter_name' => 'Rina',
            'reporter_phone' => '0812-3456-7890',
        ])->assertRedirect(route('supervision.complaints.dashboard', ['tab' => 'saran']))
            ->assertSessionHas('suggestion_trackable', true);

        $suggestion = Complaint::where('complaint_type', 'suggestion')->firstOrFail();
        $this->assertSame('0812-3456-7890', $suggestion->reporter_phone);

        $this->get('/complaints?tab=saran')->assertSee($suggestion->complaint_number);
    }

    public function test_staff_answer_and_the_sender_tracks_the_answer_but_never_the_internal_note(): void
    {
        $suggestion = Complaint::create([
            'complaint_type' => 'suggestion',
            'title' => 'Perpanjang jam layanan loket.',
            'description' => 'Perpanjang jam layanan loket.',
            'reporter_name' => 'Rina',
            'reporter_phone' => '0812-3456-7890',
            'status' => 'submitted',
        ]);

        $this->actingAs($this->leader());

        $this->get('/cp/saran')->assertOk()->assertSee('0812-3456-7890');

        Livewire::test(ListSuggestions::class)
            ->callTableAction('answer', $suggestion, data: [
                'response' => 'Mulai bulan depan loket buka sampai pukul 15.00.',
                'resolution_notes' => 'Koordinasi jadwal piket dengan Kepala TU.',
            ])
            ->assertHasNoTableActionErrors();

        $suggestion->refresh();
        $this->assertSame('resolved', $suggestion->status);
        $this->assertSame('Mulai bulan depan loket buka sampai pukul 15.00.', $suggestion->response);
        $this->assertSame('Koordinasi jadwal piket dengan Kepala TU.', $suggestion->resolution_notes);
        $this->assertNotNull($suggestion->resolved_at);

        auth()->logout();

        // The phone matches in another common form (+62 instead of 0).
        $this->post('/complaints/track', ['complaint_number' => $suggestion->complaint_number, 'contact' => '+62 812 3456 7890'])
            ->assertOk()
            ->assertSee('Mulai bulan depan loket buka sampai pukul 15.00.')
            ->assertSee('Ditanggapi')
            ->assertSee('Tahapan Saran')
            ->assertDontSee('Koordinasi jadwal piket dengan Kepala TU.');

        // The number alone, or someone else's contact, reveals nothing.
        $this->post('/complaints/track', ['complaint_number' => $suggestion->complaint_number])
            ->assertSessionHasErrors('contact');
        $this->post('/complaints/track', ['complaint_number' => $suggestion->complaint_number, 'contact' => '0899 1111 2222'])
            ->assertNotFound();
    }

    public function test_a_note_alone_keeps_the_stage_and_other_roles_cannot_answer(): void
    {
        $suggestion = Complaint::create([
            'complaint_type' => 'suggestion',
            'title' => 'Sediakan air minum.',
            'description' => 'Sediakan air minum di ruang tunggu.',
            'reporter_email' => 'warga@example.com',
            'status' => 'submitted',
        ]);

        $this->actingAs($this->leader('kepala_tu'));

        Livewire::test(ListSuggestions::class)
            ->callTableAction('answer', $suggestion, data: ['response' => '', 'resolution_notes' => 'Ajukan anggaran dispenser.'])
            ->assertHasNoTableActionErrors();

        $suggestion->refresh();
        $this->assertSame('closed', $suggestion->status); // opened = read, not answered
        $this->assertNull($suggestion->response);
        $this->assertSame('Ajukan anggaran dispenser.', $suggestion->resolution_notes);

        $this->post('/complaints/track', ['complaint_number' => $suggestion->complaint_number, 'contact' => 'WARGA@example.com'])
            ->assertOk()
            ->assertSee('Dibaca petugas')
            ->assertDontSee('Ajukan anggaran dispenser.');

        $this->actingAs($this->leader('supervisor'));
        $this->get('/cp/saran')->assertForbidden();
    }
}
