<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Pendaftaran walk-in di loket through the API. */
class WalkInApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    public function test_front_desk_registers_a_walk_in_request_with_documents(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();

        $response = $this->postJson('/api/loket/permohonan', [
            'nama' => 'Pak Budi',
            'kategori' => 'umum',
            'whatsapp' => '6281200001111',
            'layanan' => $service->slug,
            'keterangan' => 'Legalisir ijazah 3 lembar',
            'berkas' => [UploadedFile::fake()->create('ijazah.pdf', 50, 'application/pdf')],
        ])->assertCreated()->assertJsonPath('pemohon', 'Pak Budi')->assertJsonPath('akun_pemohon.status', 'baru');

        // Orang yang sama datang lagi dengan nomor ditulis berbeda: tertaut ke akun tadi.
        $this->postJson('/api/loket/permohonan', [
            'nama' => 'Pak Budi',
            'kategori' => 'umum',
            'whatsapp' => '0812-0000-1111',
            'layanan' => $service->slug,
            'keterangan' => 'Legalisir lagi',
        ])->assertCreated()
            ->assertJsonPath('akun_pemohon.status', 'tertaut')
            ->assertJsonPath('akun_pemohon.dicocokkan_lewat', 'whatsapp')
            ->assertJsonPath('pemohon_id', $response->json('pemohon_id'));

        $ticket = Ticket::where('ticket_number', $response->json('nomor_tiket'))->firstOrFail();
        $this->assertSame('offline', $ticket->mode);
        $this->assertSame($service->id, $ticket->service_id);
        $this->assertSame('6281200001111', $ticket->user->whatsapp_number);
        $this->assertDatabaseHas('ticket_files', ['ticket_id' => $ticket->id, 'file_name' => 'ijazah.pdf']);
    }

    public function test_request_is_validated_and_limited_to_the_front_desk(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/loket/permohonan', ['nama' => 'X', 'kategori' => 'umum', 'whatsapp' => '1', 'layanan' => 'tidak-ada', 'keterangan' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('layanan');
        $this->postJson('/api/loket/permohonan', [])->assertJsonValidationErrors(['nama', 'kategori', 'whatsapp', 'layanan', 'keterangan']);

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/loket/permohonan', [])->assertForbidden();
    }

    public function test_front_desk_registers_a_request_for_a_registered_applicant_without_a_new_account(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('siswa', 'offline')->availableFor('siswa')->firstOrFail();
        $student = User::factory()->create(['name' => 'Rani Siswa', 'whatsapp_number' => '085700009999', 'user_type' => 'siswa']);
        $student->assignRole('siswa');
        $users = User::count();

        $response = $this->postJson('/api/loket/permohonan', [
            'pemohon_id' => $student->id,
            'layanan' => $service->slug,
            'keterangan' => 'Surat keterangan aktif',
        ])->assertCreated()
            ->assertJsonPath('pemohon', 'Rani Siswa')
            ->assertJsonPath('pemohon_id', $student->id)
            ->assertJsonPath('akun_pemohon.status', 'terpilih')
            ->assertJsonPath('akun_pemohon.keterangan', 'Permohonan masuk ke akun terpilih: Rani Siswa.');

        $this->assertSame($users, User::count());
        $this->assertSame($student->id, Ticket::where('ticket_number', $response->json('nomor_tiket'))->value('user_id'));

        // Kategori yang dikirim harus salah satu peran pemohon akun itu.
        $this->postJson('/api/loket/permohonan', ['pemohon_id' => $student->id, 'kategori' => 'alumni', 'layanan' => $service->slug, 'keterangan' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('kategori');
    }

    public function test_counter_requests_get_consecutive_numbers_and_refusals_use_none(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();
        User::factory()->create(['email' => 'nonaktif@contoh.id', 'is_active' => false]);
        $send = fn (array $extra) => $this->postJson('/api/loket/permohonan', $extra + [
            'nama' => 'Tamu', 'kategori' => 'umum', 'whatsapp' => '089922223333', 'layanan' => $service->slug, 'keterangan' => 'Legalisir',
        ]);

        $first = $send([])->assertCreated()->json('nomor_tiket');
        $send(['email' => 'nonaktif@contoh.id', 'whatsapp' => '089900000001'])->assertUnprocessable();
        $second = $send([])->assertCreated()->json('nomor_tiket');

        $this->assertNotSame($first, $second);
        $this->assertSame((int) substr($first, -4) + 1, (int) substr($second, -4), 'Penolakan tidak memakan nomor.');
        $this->assertSame(1, Ticket::where('ticket_number', $second)->count());
    }

    public function test_a_refused_link_answers_422_and_keeps_no_files(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();
        User::factory()->create(['email' => 'nonaktif@contoh.id', 'is_active' => false]);

        $this->postJson('/api/loket/permohonan', [
            'nama' => 'Tamu', 'kategori' => 'umum', 'whatsapp' => '089911112222', 'email' => 'nonaktif@contoh.id',
            'layanan' => $service->slug, 'keterangan' => 'Legalisir',
            'berkas' => [UploadedFile::fake()->create('ijazah.pdf', 50, 'application/pdf')],
        ])->assertUnprocessable()->assertJsonPath('message', fn (string $message) => str_contains($message, 'dinonaktifkan'));

        $this->assertSame([], Storage::disk('local')->allFiles('ticket-files'));
    }

    public function test_counter_services_are_filtered_by_applicant_type_on_the_server(): void
    {
        $front = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        Sanctum::actingAs($front);
        $studentsOnly = Service::factory()->create(['user_types_allowed' => ['siswa'], 'is_active' => true, 'mode' => 'offline']);
        $public = User::factory()->create(['user_type' => 'umum']);
        $public->assignRole('umum');

        $this->postJson('/api/loket/permohonan', ['nama' => 'X', 'kategori' => 'umum', 'whatsapp' => '089911110000', 'layanan' => $studentsOnly->slug, 'keterangan' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('layanan');
        $this->postJson('/api/loket/permohonan', ['pemohon_id' => $public->id, 'layanan' => $studentsOnly->slug, 'keterangan' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('layanan');
        $this->postJson('/api/loket/permohonan', ['nama' => 'Siswa', 'kategori' => 'siswa', 'whatsapp' => '089911110001', 'layanan' => $studentsOnly->slug, 'keterangan' => 'x'])
            ->assertCreated();

        // Lapis layanan menolak juga, apa pun jalurnya.
        try {
            app(\App\Services\FrontDeskService::class)->registerWalkIn(['service_id' => $studentsOnly->id, 'applicant_type' => 'umum', 'description' => 'x'], [], $front, $public);
            $this->fail('Layanan khusus siswa tidak boleh untuk pemohon umum.');
        } catch (\App\Exceptions\TicketActionException $e) {
            $this->assertStringContainsString('tidak tersedia untuk pemohon Masyarakat Umum', $e->getMessage());
        }

        // Form loket: pilihan layanan yang dimanipulasi dari peramban ditolak.
        $this->actingAs($front);
        \Livewire\Livewire::test(\App\Filament\Pages\FrontDesk\RegisterService::class)
            ->fillForm(['applicant_name' => 'X', 'applicant_phone' => '089911110002', 'applicant_type' => 'umum', 'description' => 'x'])
            ->set('data.service_id', $studentsOnly->id)
            ->call('submit')
            ->assertHasFormErrors(['service_id' => 'in']);
    }

    public function test_registered_status_is_refused_without_a_picked_applicant(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();
        $base = ['layanan' => $service->slug, 'keterangan' => 'Legalisir'];
        $tickets = Ticket::count();

        $this->postJson('/api/loket/permohonan', $base + ['status_pemohon' => 'terdaftar', 'nama' => 'X', 'kategori' => 'umum', 'whatsapp' => '0899'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pemohon_id' => 'Pilih akun pemohon terdaftar']);

        $student = User::factory()->create(['user_type' => 'siswa']);
        $student->assignRole('siswa');
        $this->postJson('/api/loket/permohonan', $base + ['status_pemohon' => 'baru', 'pemohon_id' => $student->id, 'nama' => 'X', 'kategori' => 'umum', 'whatsapp' => '0899'])
            ->assertUnprocessable()->assertJsonValidationErrors('pemohon_id');

        $this->postJson('/api/loket/permohonan', $base + ['status_pemohon' => 'lain'])->assertJsonValidationErrors('status_pemohon');
        $this->assertSame($tickets, Ticket::count());

        $this->postJson('/api/loket/permohonan', $base + ['status_pemohon' => 'terdaftar', 'pemohon_id' => $student->id])->assertCreated();
    }

    public function test_registered_applicant_must_be_an_active_applicant_account(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();
        $inactive = User::factory()->create(['user_type' => 'umum', 'is_active' => false]);
        $inactive->assignRole('umum');
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        foreach ([$inactive, $staff] as $account) {
            $this->postJson('/api/loket/permohonan', ['pemohon_id' => $account->id, 'layanan' => $service->slug, 'keterangan' => 'x'])
                ->assertUnprocessable()->assertJsonValidationErrors('pemohon_id');
        }
        $this->assertSame(0, Ticket::whereIn('user_id', [$inactive->id, $staff->id])->count());

        // Lapis layanan juga menolak, apa pun jalurnya.
        $this->expectException(\App\Exceptions\TicketActionException::class);
        app(\App\Services\FrontDeskService::class)->registerWalkIn(
            ['service_id' => $service->id, 'applicant_type' => 'umum', 'description' => 'x'],
            [],
            User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(),
            $staff,
        );
    }

    public function test_front_desk_reads_the_official_applicant_types(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $response = $this->getJson('/api/loket/tipe-pemohon')->assertOk()
            ->assertJsonPath('data.0', ['kode' => 'siswa', 'label' => 'Siswa'])
            ->assertJsonPath('data.3', ['kode' => 'umum', 'label' => 'Masyarakat Umum']);
        $this->assertSame(\App\Support\Applicants::ROLES, array_column($response->json('data'), 'kode'));
        $this->assertContains('kepala_sekolah', array_column($response->json('bukan_pemohon'), 'kode'));
        $this->assertSame([], array_intersect(array_column($response->json('data'), 'kode'), array_column($response->json('bukan_pemohon'), 'kode')));

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/loket/tipe-pemohon')->assertForbidden();
    }

    public function test_front_desk_reads_the_picked_applicant_details(): void
    {
        $student = User::factory()->create(['name' => 'Detail Siswa', 'email' => 'detail@contoh.id', 'user_type' => 'siswa']);
        $student->assignRole('siswa');
        $older = Ticket::factory()->create(['user_id' => $student->id]);
        $latest = Ticket::factory()->create(['user_id' => $student->id]);
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $inactive = User::factory()->create(['user_type' => 'umum', 'is_active' => false]);
        $inactive->assignRole('umum');

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson("/api/loket/pemohon/{$student->id}")->assertOk()
            ->assertJsonPath('nama', 'Detail Siswa')
            ->assertJsonPath('tipe', ['siswa'])
            ->assertJsonPath('jumlah_permohonan', 2)
            ->assertJsonPath('terdaftar_sejak', $student->created_at->toDateString())
            ->assertJsonPath('permohonan_terakhir.0.nomor_tiket', $latest->ticket_number)
            ->assertJsonPath('permohonan_terakhir.1.nomor_tiket', $older->ticket_number)
            // Petugas loket tidak memperbaiki data pemohon sendiri.
            ->assertJsonPath('tautan_perbaikan', null);

        // Akun petugas atau nonaktif tidak dibuka lewat loket.
        $this->getJson("/api/loket/pemohon/{$staff->id}")->assertNotFound();
        $this->getJson("/api/loket/pemohon/{$inactive->id}")->assertNotFound();

        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson("/api/loket/pemohon/{$student->id}")->assertOk()
            ->assertJsonPath('tautan_perbaikan', fn (string $url) => str_contains($url, 'activeTab=pemohon'));

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson("/api/loket/pemohon/{$student->id}")->assertForbidden();
    }

    public function test_front_desk_searches_registered_applicants_by_type(): void
    {
        $student = User::factory()->create(['name' => 'Zahra Pemohon', 'email' => 'zahra@contoh.id', 'whatsapp_number' => '0857-1111-2222', 'user_type' => 'siswa']);
        $student->assignRole('siswa');
        Ticket::factory()->create(['user_id' => $student->id]);
        $walkIn = User::factory()->create(['name' => 'Zahra Walk-in', 'email' => '085733334444@walkin.local', 'whatsapp_number' => '085733334444', 'user_type' => 'umum']);
        $inactive = User::factory()->create(['name' => 'Zahra Nonaktif', 'user_type' => 'umum', 'is_active' => false]);
        $inactive->assignRole('umum');
        $staff = User::factory()->create(['name' => 'Zahra Petugas', 'user_type' => 'pegawai']);
        $staff->assignRole('back_office');

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/loket/pemohon?q=zahra')->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.nama', 'Zahra Pemohon')
            ->assertJsonPath('data.0.email', 'zahra@contoh.id')
            ->assertJsonPath('data.0.tipe', ['siswa'])
            ->assertJsonPath('data.0.tipe_label', 'Siswa')
            ->assertJsonPath('data.0.jumlah_permohonan', 1)
            // Email buatan loket tidak dikirim sebagai email pemohon.
            ->assertJsonPath('data.1.nama', 'Zahra Walk-in')
            ->assertJsonPath('data.1.email', null)
            ->assertJsonPath('data.1.tipe', ['umum']);

        $this->getJson('/api/loket/pemohon?q=zahra&tipe=siswa')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $student->id);
        $this->getJson('/api/loket/pemohon?q=zahra&tipe=umum')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $walkIn->id);
        // Nomor WhatsApp dicari tanpa memedulikan tanda baca.
        $this->getJson('/api/loket/pemohon?q=08571111')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $student->id);
        $this->getJson('/api/loket/pemohon?q=ZAHRA@contoh')->assertJsonPath('data.0.id', $student->id);

        $this->getJson('/api/loket/pemohon?tipe=back_office')->assertUnprocessable()->assertJsonValidationErrors('tipe');

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/loket/pemohon?q=zahra')->assertForbidden();
    }
}
