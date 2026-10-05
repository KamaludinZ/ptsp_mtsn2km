<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/tiket: the staff request list with tabs, search and filters. */
class StaffTicketApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->travelTo(Carbon::parse('2026-10-15 10:00'));
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    public function test_officer_gets_the_queue_by_default_with_tab_counts(): void
    {
        $plain = ['approval_required' => false];
        $queued = Ticket::factory()->create($plain + ['status' => 'submitted', 'estimated_completion_date' => '2026-10-20']);
        $urgent = Ticket::factory()->create($plain + ['status' => 'in_process', 'estimated_completion_date' => '2026-10-16', 'assigned_to_id' => $this->officer->id]);
        $done = Ticket::factory()->create($plain + ['status' => 'completed', 'ready_for_pickup' => true]);
        Sanctum::actingAs($this->officer);

        $response = $this->getJson('/api/tiket')->assertOk()
            ->assertJsonPath('tab', 'antrian')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.nomor_tiket', $urgent->ticket_number)
            ->assertJsonPath('data.0.status_label', 'Diproses')
            ->assertJsonPath('data.0.status_warna', 'warning')
            ->assertJsonPath('data.0.petugas.id', $this->officer->id)
            ->assertJsonPath('data.1.nomor_tiket', $queued->ticket_number);

        $tabs = collect($response->json('tab_tersedia'))->pluck('jumlah', 'kunci');
        $this->assertSame(['antrian' => 2, 'saya' => 1, 'terlambat' => 0, 'persetujuan' => 0, 'siap-diambil' => 1, 'semua' => 3], $tabs->all());

        $this->getJson('/api/tiket?tab=semua&urut=terlama')->assertJsonPath('meta.total', 3)->assertJsonPath('data.2.nomor_tiket', $done->ticket_number);
    }

    public function test_search_and_filters_combine_and_narrow_the_tab_counts(): void
    {
        $legalisir = Service::factory()->create(['name' => 'Legalisir']);
        $siti = User::factory()->create(['name' => 'Siti Aminah', 'whatsapp_number' => '081234567890']);
        $match = Ticket::factory()->create(['service_id' => $legalisir->id, 'user_id' => $siti->id, 'status' => 'submitted', 'mode' => 'online', 'created_at' => '2026-10-14 09:00']);
        Ticket::factory()->create(['service_id' => $legalisir->id, 'status' => 'completed', 'created_at' => '2026-09-01 09:00']);
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-10-14 09:00']);
        Sanctum::actingAs($this->officer);

        $query = http_build_query(['tab' => 'semua', 'q' => '6281234', 'layanan' => [$legalisir->id], 'status' => ['submitted'], 'periode' => 'this_month', 'jalur' => 'online']);
        $response = $this->getJson('/api/tiket?' . $query)->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.nomor_tiket', $match->ticket_number)
            ->assertJsonPath('data.0.pemohon.nama', 'Siti Aminah');
        $this->assertSame(1, collect($response->json('tab_tersedia'))->firstWhere('kunci', 'semua')['jumlah']);

        $this->getJson('/api/tiket?tab=semua&dari=2026-09-01&sampai=2026-09-30')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/tiket?tab=semua&kategori=disposisi&per_halaman=2')->assertJsonPath('meta.per_halaman', 2)->assertJsonPath('meta.halaman_terakhir', 2);
    }

    public function test_invalid_filters_and_unavailable_tabs_are_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole('front_desk'));

        $this->getJson('/api/tiket')->assertOk()->assertJsonPath('tab', 'siap-diambil');
        $this->getJson('/api/tiket?tab=saya')->assertStatus(422)->assertJsonValidationErrors('tab');
        $this->getJson('/api/tiket?status[]=pending_approval&periode=kemarin&dari=2026-10-10&sampai=2026-10-01')
            ->assertStatus(422)->assertJsonValidationErrors(['status.0', 'periode', 'sampai']);
    }

    public function test_applicants_cannot_list_every_request(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/tiket')->assertForbidden();
        auth()->forgetGuards();
        $this->app['auth']->guard('sanctum')->forgetUser();
        $this->getJson('/api/tiket')->assertUnauthorized();
    }

    public function test_detail_returns_progress_history_and_allowed_actions(): void
    {
        $applicant = User::factory()->create(['name' => 'Budi', 'email' => 'budi@walkin.local', 'whatsapp_number' => '0812']);
        $ticket = Ticket::factory()->create(['user_id' => $applicant->id, 'status' => 'submitted', 'approval_required' => false, 'notes' => 'Legalisir 3 lembar', 'estimated_completion_date' => '2026-10-18']);
        $this->actingAs($this->officer);
        $ticket->update(['status' => 'verified']);
        Sanctum::actingAs($this->officer);

        $this->getJson('/api/tiket/' . $ticket->ticket_number)->assertOk()
            ->assertJsonPath('nomor_tiket', $ticket->ticket_number)
            ->assertJsonPath('keterangan', 'Legalisir 3 lembar')
            ->assertJsonPath('pemohon.nama', 'Budi')
            ->assertJsonPath('pemohon.email', null)
            ->assertJsonPath('tenggat.label', 'Sisa 3 hari')
            ->assertJsonPath('tahapan.0.keadaan', 'done')
            ->assertJsonPath('tahapan.2.keadaan', 'current')
            ->assertJsonPath('persetujuan', null)
            ->assertJsonPath('riwayat_status.1.label', 'Diajukan → Diverifikasi')
            ->assertJsonPath('riwayat_status.1.oleh', $this->officer->name)
            ->assertJsonPath('riwayat_kategori.0.ke', 'disposisi')
            ->assertJsonPath('aksi.ubah_status', ['submitted', 'in_process', 'rejected', 'cancelled'])
            ->assertJsonPath('aksi.pilih_kategori', true);
    }

    public function test_detail_is_for_staff_and_unknown_numbers_are_404(): void
    {
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs($ticket->user);
        $this->getJson('/api/tiket/' . $ticket->ticket_number)->assertForbidden();

        Sanctum::actingAs($this->officer);
        $this->getJson('/api/tiket/PTSP-TIDAK-ADA')->assertNotFound();
    }

    public function test_officer_changes_status_through_the_api(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);
        Sanctum::actingAs($this->officer);
        $url = '/api/tiket/' . $ticket->ticket_number . '/status';

        $this->patchJson($url, ['status' => 'verified', 'catatan' => 'Berkas lengkap.'])->assertOk()
            ->assertJsonPath('message', 'Status menjadi Diverifikasi.')
            ->assertJsonPath('data.status', 'verified')
            ->assertJsonPath('data.status_warna', 'info');
        $this->assertSame('verified', $ticket->fresh()->status);
        $this->assertSame($this->officer->id, $ticket->statusHistories()->get()->last()->changed_by);
        $this->assertSame('Berkas lengkap.', $ticket->logs()->latest('id')->value('notes'));

        $this->patchJson($url, ['status' => 'completed', 'catatan' => 'Langsung selesai.'])->assertStatus(422)
            ->assertJsonPath('status_diizinkan', ['submitted', 'in_process', 'rejected', 'cancelled']);
        $this->patchJson($url, ['status' => 'rejected', 'catatan' => 'Tidak.'])->assertStatus(422)
            ->assertJsonPath('message', 'Tuliskan alasan penolakan (minimal 10 karakter).');
        $this->patchJson($url, ['status' => 'approved', 'catatan' => 'x'])->assertStatus(422)->assertJsonValidationErrors(['status', 'catatan']);
        $this->assertSame('verified', $ticket->fresh()->status);
    }

    public function test_completion_waits_for_the_disposition(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => true, 'approval_status' => 'pending']);
        Sanctum::actingAs($this->officer);

        $this->patchJson('/api/tiket/' . $ticket->ticket_number . '/status', ['status' => 'completed', 'catatan' => 'Selesai dikerjakan.'])
            ->assertStatus(422)->assertJsonPath('message', 'Tiket ini belum disetujui pimpinan, sehingga belum bisa diselesaikan.');
    }

    public function test_only_back_office_may_change_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole('front_desk'));

        $this->patchJson('/api/tiket/' . $ticket->ticket_number . '/status', ['status' => 'verified', 'catatan' => 'Oke.'])->assertForbidden();
    }

    public function test_follow_up_notes_through_the_api(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process']);
        Sanctum::actingAs($this->officer);
        $url = '/api/tiket/' . $ticket->ticket_number . '/catatan';

        $this->postJson($url, ['catatan' => 'Dikoordinasikan dengan TU.', 'jenis' => 'coordination', 'tindak_lanjut_berikutnya' => '2026-10-17'])
            ->assertCreated()
            ->assertJsonPath('data.jenis_label', 'Koordinasi dengan unit lain')
            ->assertJsonPath('data.tampil_ke_pemohon', false)
            ->assertJsonPath('data.tindak_lanjut_berikutnya', '2026-10-17')
            ->assertJsonPath('data.oleh', $this->officer->name);
        $this->postJson($url, ['catatan' => 'Mohon unggah fotokopi KK.', 'jenis' => 'request_documents', 'tampilkan_ke_pemohon' => true])->assertCreated();

        $this->getJson($url)->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.catatan', 'Mohon unggah fotokopi KK.')
            ->assertJsonPath('data.0.tampil_ke_pemohon', true)
            ->assertJsonPath('jenis.internal', 'Catatan internal');

        $this->postJson($url, ['catatan' => 'ok', 'jenis' => 'lain', 'tindak_lanjut_berikutnya' => '2026-10-01'])
            ->assertStatus(422)->assertJsonValidationErrors(['catatan', 'jenis', 'tindak_lanjut_berikutnya']);
    }

    public function test_follow_up_notes_are_for_staff(): void
    {
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs($ticket->user);
        $this->getJson('/api/tiket/' . $ticket->ticket_number . '/catatan')->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole('front_desk'));
        $this->postJson('/api/tiket/' . $ticket->ticket_number . '/catatan', ['catatan' => 'Catatan loket.'])->assertForbidden();
    }

    public function test_filter_options_list_every_accepted_value(): void
    {
        $service = Service::factory()->create(['name' => 'Legalisir']);
        Sanctum::actingAs($this->officer);

        $this->getJson('/api/tiket/pilihan-filter')->assertOk()
            ->assertJsonPath('tab_bawaan', 'antrian')
            ->assertJsonPath('tab.0', ['nilai' => 'antrian', 'label' => 'Antrian'])
            ->assertJsonPath('periode.0', ['nilai' => 'today', 'label' => 'Hari ini'])
            ->assertJsonFragment(['nilai' => $service->id, 'label' => 'Legalisir'])
            ->assertJsonFragment(['nilai' => $this->officer->id, 'label' => $this->officer->name])
            ->assertJsonFragment(['nilai' => 'koordinasi', 'label' => 'Koordinasi'])
            ->assertJsonPath('pencarian.minimal_huruf', 2);

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/tiket/pilihan-filter')->assertForbidden();
    }

    public function test_search_indexes_exist(): void
    {
        $indexes = collect(\Illuminate\Support\Facades\DB::select("select indexname from pg_indexes where indexname like 'idx_%_trgm'"))->pluck('indexname');

        foreach (['idx_tickets_ticket_number_trgm', 'idx_tickets_notes_trgm', 'idx_users_name_trgm', 'idx_users_whatsapp_digits_trgm', 'idx_services_name_trgm'] as $index) {
            $this->assertContains($index, $indexes);
        }
    }
}
