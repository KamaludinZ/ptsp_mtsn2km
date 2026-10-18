<?php

namespace Tests\Feature;

use App\Filament\Pages\FrontDesk\RegisterService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Registrasi layanan loket: petugas memilih status pemohon — belum
 * terdaftar (cari/buat akun lewat email & WhatsApp) atau sudah terdaftar
 * (pilih akun pemohon yang ada).
 */
class WalkInApplicantStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        foreach (['admin', 'front_desk', 'back_office', 'umum', 'siswa'] as $role) {
            Role::findOrCreate($role);
        }
        Permission::findOrCreate('frontdesk.access');

        $officer = User::factory()->create(['user_type' => 'pegawai']);
        $officer->assignRole('front_desk');
        $officer->givePermissionTo('frontdesk.access');
        $this->actingAs($officer);
    }

    public function test_page_starts_with_not_registered_status_and_its_fields(): void
    {
        Livewire::test(RegisterService::class)
            ->assertFormSet(['applicant_status' => 'baru'])
            ->assertFormFieldIsVisible('applicant_status')
            ->assertFormFieldIsVisible('applicant_name')
            ->assertFormFieldIsVisible('applicant_phone')
            ->assertFormFieldIsVisible('applicant_email')
            ->assertFormFieldIsHidden('applicant_id')
            ->assertSee('Belum terdaftar')
            ->assertSee('Sudah terdaftar');
    }

    public function test_registered_status_swaps_the_new_applicant_fields_for_an_account_picker(): void
    {
        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_name' => 'Pak Ahmad', 'applicant_phone' => '081234567890'])
            ->fillForm(['applicant_status' => 'terdaftar'])
            ->assertFormFieldIsVisible('applicant_id')
            ->assertFormFieldIsHidden('applicant_name')
            ->assertFormFieldIsHidden('applicant_phone')
            ->assertFormFieldIsHidden('applicant_email')
            // Isian dari status sebelumnya tidak terbawa.
            ->assertFormSet(['applicant_name' => null, 'applicant_phone' => null]);
    }

    public function test_registered_status_requires_an_account(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        Livewire::test(RegisterService::class)
            ->fillForm([
                'applicant_status' => 'terdaftar',
                'service_id' => $service->id,
                'description' => 'Legalisir ijazah.',
            ])
            ->call('submit')
            ->assertHasFormErrors(['applicant_id' => 'required'])
            ->assertSee('Pilih akun pemohon terdaftar dulu, atau ganti status pemohon ke Belum terdaftar.');

        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('ticket_files', 0);
    }

    public function test_registered_status_files_the_request_under_the_picked_account(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);
        $applicant = User::factory()->create(['name' => 'Bu Sari', 'user_type' => 'umum', 'whatsapp_number' => '085700000001']);
        $applicant->assignRole('umum');
        $users = User::count();

        $page = Livewire::test(RegisterService::class)
            ->fillForm([
                'applicant_status' => 'terdaftar',
                'applicant_id' => $applicant->id,
                'service_id' => $service->id,
                'description' => 'Legalisir ijazah walk-in.',
                'priority' => 'normal',
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tickets', ['service_id' => $service->id, 'user_id' => $applicant->id, 'mode' => 'offline']);
        $this->assertSame($users, User::count(), 'Tidak ada akun baru yang dibuat.');

        // Konfirmasi: nomor permohonan, hasil penautan, tanda terima, lalu halaman detail tiket.
        $ticket = \App\Models\Ticket::where('user_id', $applicant->id)->firstOrFail();
        $notification = collect(session('filament.notifications'))->firstWhere('title', 'Permohonan terdaftar dengan nomor ' . $ticket->ticket_number);
        $this->assertNotNull($notification);
        $this->assertStringStartsWith('Permohonan masuk ke akun terpilih: Bu Sari.', $notification['body']);
        $this->assertSame(route('tickets.receipt', $ticket), $notification['actions'][0]['url']);
        $page->assertRedirect(\App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket]));
    }

    public function test_not_registered_form_finds_an_account_by_whatsapp_and_files_the_request_there(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);
        $existing = User::factory()->create(['name' => 'Pak Budi', 'whatsapp_number' => '081299990000']);
        $users = User::count();

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_phone' => '081299990000'])
            ->assertSee('Hasil pencarian akun')
            ->assertSee('Sudah terdaftar atas nama')
            ->assertSee('Pak Budi')
            ->fillForm([
                'applicant_name' => 'Budi',
                'service_id' => $service->id,
                'description' => 'Surat keterangan.',
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tickets', ['service_id' => $service->id, 'user_id' => $existing->id]);
        $this->assertSame($users, User::count());
    }

    public function test_not_registered_form_creates_an_account_when_nothing_matches(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        Livewire::test(RegisterService::class)
            ->assertDontSee('Hasil pencarian akun')
            ->fillForm(['applicant_email' => 'Baru@Contoh.id'])
            ->assertSee('Akun pemohon baru dibuat')
            ->fillForm([
                'applicant_name' => 'Pemohon Baru',
                'applicant_phone' => '089900001111',
                'service_id' => $service->id,
                'description' => 'Legalisir.',
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['name' => 'Pemohon Baru', 'email' => 'baru@contoh.id', 'whatsapp_number' => '089900001111']);
        // Akun baru loket langsung memegang peran pemohonnya (aturan pemohon resmi).
        $created = User::where('email', 'baru@contoh.id')->firstOrFail();
        $this->assertTrue($created->hasRole('umum'));
        $this->assertTrue($created->isApplicant());
    }

    public function test_legacy_account_without_a_role_is_linked_as_an_official_applicant(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => null]);
        $legacy = User::factory()->create(['user_type' => 'alumni', 'whatsapp_number' => '081277770000']);
        $this->assertTrue($legacy->roles->isEmpty());

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_name' => 'Alumni', 'applicant_phone' => '081277770000', 'service_id' => $service->id, 'description' => 'Legalisir.'])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tickets', ['user_id' => $legacy->id]);
        $this->assertTrue($legacy->fresh()->hasRole('alumni'));
    }

    public function test_counter_never_files_a_request_under_an_unusable_account(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => null]);
        Role::findOrCreate('umum');
        $inactive = User::factory()->create(['email' => 'nonaktif@contoh.id', 'is_active' => false]);
        $staff = User::factory()->create(['email' => 'petugas@contoh.id', 'whatsapp_number' => '081266660000']);
        $staff->assignRole('back_office');
        User::factory()->create(['email' => 'terhapus@contoh.id'])->delete();

        foreach (['nonaktif@contoh.id' => 'dinonaktifkan', 'petugas@contoh.id' => 'bukan akun pemohon', 'terhapus@contoh.id' => 'sudah dihapus'] as $email => $reason) {
            Livewire::test(RegisterService::class)
                ->fillForm(['applicant_name' => 'Tamu', 'applicant_phone' => '089900000000', 'applicant_email' => $email])
                ->assertSee($reason)
                ->fillForm(['service_id' => $service->id, 'description' => 'Legalisir.'])
                ->call('submit')
                ->assertNotified('Permohonan belum terdaftar')
                ->assertNoRedirect();
        }
        $this->assertDatabaseCount('tickets', 0);

        // Nomor WhatsApp petugas tidak menangkap permohonan walk-in: pemohon dibuatkan akun sendiri.
        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_name' => 'Tamu', 'applicant_phone' => '081266660000'])
            ->assertSee('Akun pemohon baru dibuat')
            ->fillForm(['service_id' => $service->id, 'description' => 'Legalisir.'])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertSame(0, \App\Models\Ticket::where('user_id', $staff->id)->count());
        $this->assertDatabaseHas('users', ['email' => '081266660000@walkin.local', 'name' => 'Tamu']);
    }

    public function test_counter_never_creates_a_second_account_for_the_same_person(): void
    {
        $front = app(\App\Services\FrontDeskService::class);
        $data = ['applicant_name' => 'Pak Dedi', 'applicant_type' => 'umum'];

        $first = $front->findOrCreateApplicant($data + ['applicant_phone' => '+62 812-4444-5555']);
        // Email bayangan memakai nomor baku, apa pun cara nomornya ditulis.
        $this->assertSame('081244445555@walkin.local', $first->email);
        $this->assertTrue($first->is($front->findOrCreateApplicant($data + ['applicant_phone' => '0812 4444 5555'])));
        $this->assertTrue($first->is($front->findOrCreateApplicant($data + ['applicant_phone' => '6281244445555'])));

        // Nomornya sempat diubah, tetapi email bayangannya masih nomor itu: tetap akun yang sama.
        $first->forceFill(['whatsapp_number' => '089900000000'])->save();
        $this->assertTrue($first->is($front->findOrCreateApplicant($data + ['applicant_phone' => '081244445555'])));
        $this->assertSame(1, User::where('email', 'like', '%@walkin.local')->count());

        // Akun bayangan yang dinonaktifkan: ditolak dengan alasan, bukan galat basis data.
        $first->forceFill(['is_active' => false])->save();
        try {
            $front->findOrCreateApplicant($data + ['applicant_phone' => '081244445555']);
            $this->fail('Akun nonaktif tidak boleh dipakai.');
        } catch (\App\Exceptions\TicketActionException $e) {
            $this->assertStringContainsString('dinonaktifkan', $e->getMessage());
        }
    }

    public function test_a_failed_submit_says_why_without_a_request_number(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => null]);
        $users = User::count();

        $page = Livewire::test(RegisterService::class)
            ->fillForm(['applicant_name' => 'Tamu Baru', 'applicant_phone' => '089911119999', 'service_id' => $service->id, 'description' => 'Legalisir.']);

        // Layanan dimatikan admin saat form masih terbuka.
        $service->update(['is_active' => false]);
        $page->call('submit')
            ->assertHasFormErrors(['service_id' => 'in'])
            ->assertSee('sedang nonaktif atau tidak terbuka untuk tipe pemohon')
            ->assertNoRedirect()
            ->assertFormSet(['applicant_name' => 'Tamu Baru', 'description' => 'Legalisir.']);

        // Lapis layanan juga menolak (mis. dimatikan tepat setelah validasi): pesan, bukan halaman galat.
        $this->assertSame('Layanan ini sedang tidak menerima permohonan. Pilih layanan lain.', rescue(
            fn () => app(\App\Services\FrontDeskService::class)->registerWalkIn(['service_id' => $service->id, 'applicant_type' => 'umum', 'applicant_name' => 'X', 'applicant_phone' => '0899', 'description' => 'x'], [], auth()->user()),
            fn (\Throwable $e) => $e->getMessage(),
            report: false,
        ));

        $this->assertDatabaseCount('tickets', 0);
        $this->assertSame($users, User::count(), 'Akun baru ikut batal bersama permohonannya.');

        // Gangguan tak terduga: tetap pesan yang jelas, tanpa nomor permohonan.
        $service->update(['is_active' => true]);
        $this->partialMock(\App\Services\FrontDeskService::class, fn ($mock) => $mock->shouldReceive('registerWalkInLinked')->andThrow(new \RuntimeException('db down')));
        $page->call('submit')->assertNotified('Permohonan belum terdaftar')->assertNoRedirect();
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_applicant_lookup_needs_an_email_or_whatsapp_number(): void
    {
        User::factory()->create();

        $this->assertNull(app(\App\Services\FrontDeskService::class)->findApplicant(['applicant_email' => '', 'applicant_phone' => ' ']));
    }

    public function test_user_type_filter_narrows_the_account_picker(): void
    {
        $student = User::factory()->create(['name' => 'Ani Siswa', 'user_type' => 'umum']);
        $student->assignRole('siswa');
        $public = User::factory()->create(['name' => 'Ani Umum', 'user_type' => 'umum']);
        $public->assignRole('umum');
        // Akun walk-in lama: belum punya peran, tipenya dari user_type.
        $alumnus = User::factory()->create(['name' => 'Ani Alumni', 'user_type' => 'alumni']);

        $this->assertSame([$student->id], array_keys(RegisterService::applicantOptions('ani', 'siswa')));
        $this->assertSame([$public->id], array_keys(RegisterService::applicantOptions('ani', 'umum')));
        $this->assertSame([$alumnus->id], array_keys(RegisterService::applicantOptions('', 'alumni')));

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar', 'applicant_type' => 'siswa'])
            ->assertFormFieldExists('applicant_type', fn ($field) => $field->getLabel() === 'Tipe pengguna')
            ->assertFormFieldExists('applicant_id', fn ($field) => array_keys($field->getOptions()) === [$student->id])
            ->fillForm(['applicant_id' => $student->id])
            // Berganti tipe: akun yang sudah dipilih dilepas.
            ->fillForm(['applicant_type' => 'umum'])
            ->assertFormSet(['applicant_id' => null]);
    }

    public function test_picked_account_must_match_the_user_type(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => null]);
        $student = User::factory()->create(['user_type' => 'siswa']);
        $student->assignRole('siswa');

        Livewire::test(RegisterService::class)
            ->fillForm([
                'applicant_status' => 'terdaftar',
                'applicant_type' => 'siswa',
                'service_id' => $service->id,
                'description' => 'Surat aktif.',
            ])
            ->set('data.applicant_type', 'umum')
            ->set('data.applicant_id', $student->id)
            ->call('submit')
            ->assertHasFormErrors(['applicant_id']);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_picked_account_shows_a_read_only_summary(): void
    {
        $applicant = User::factory()->create([
            'name' => 'Bu Sari',
            'user_type' => 'umum',
            'whatsapp_number' => '085700000001',
            'email' => '085700000001@walkin.local',
        ]);
        $applicant->assignRole('umum');
        $ticket = \App\Models\Ticket::factory()->create(['user_id' => $applicant->id]);

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar'])
            ->assertDontSee('Ringkasan pemohon')
            ->fillForm(['applicant_id' => $applicant->id])
            ->assertSee('Ringkasan pemohon')
            ->assertSee('Masyarakat Umum')
            ->assertSee('1 permohonan, terakhir ' . $ticket->ticket_number)
            ->assertSee('tidak bisa diubah di loket')
            // Petugas loket tidak membuka menu Pengguna: hanya diarahkan ke administrator.
            ->assertSee('minta administrator memperbaikinya')
            ->assertDontSee('activeTab=pemohon');
    }

    public function test_picked_account_fills_locked_name_whatsapp_and_email(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => null]);
        $applicant = User::factory()->create(['name' => 'Bu Sari', 'whatsapp_number' => '085700000001', 'email' => 'sari@contoh.id']);
        $applicant->assignRole('umum');
        $walkIn = User::factory()->create(['name' => 'Pak Walk-in', 'whatsapp_number' => '085700000002', 'email' => '085700000002@walkin.local']);
        $walkIn->assignRole('umum');

        $page = Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar'])
            // Belum memilih akun: belum ada isian pemohon.
            ->assertFormFieldIsHidden('applicant_name')
            ->fillForm(['applicant_id' => $applicant->id])
            ->assertFormSet(['applicant_name' => 'Bu Sari', 'applicant_phone' => '085700000001', 'applicant_email' => 'sari@contoh.id'])
            ->assertFormFieldIsDisabled('applicant_name')
            ->assertFormFieldIsDisabled('applicant_phone')
            ->assertFormFieldIsDisabled('applicant_email')
            ->assertSee('Dari akun')
            // Email buatan loket tidak ditampilkan sebagai email pemohon.
            ->fillForm(['applicant_id' => $walkIn->id])
            ->assertFormSet(['applicant_name' => 'Pak Walk-in', 'applicant_phone' => '085700000002', 'applicant_email' => null])
            ->assertSee('Akun ini tidak punya email');

        // Isian terkunci yang diubah lewat peramban tidak mengubah akun maupun pemohon permohonan.
        $page->set('data.applicant_name', 'Nama Palsu')
            ->fillForm(['service_id' => $service->id, 'description' => 'Legalisir.'])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tickets', ['service_id' => $service->id, 'user_id' => $walkIn->id]);
        $this->assertSame('Pak Walk-in', $walkIn->fresh()->name);

        // Berganti tipe pengguna: akun dan salinannya dilepas.
        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar', 'applicant_id' => $applicant->id])
            ->fillForm(['applicant_type' => 'siswa'])
            ->assertFormSet(['applicant_id' => null, 'applicant_name' => null, 'applicant_phone' => null, 'applicant_email' => null]);
    }

    public function test_not_registered_fields_stay_editable_and_required(): void
    {
        Livewire::test(RegisterService::class)
            ->assertFormFieldIsEnabled('applicant_name')
            ->assertFormFieldIsEnabled('applicant_phone')
            ->assertFormFieldIsEnabled('applicant_email')
            ->fillForm(['applicant_type' => 'siswa', 'applicant_name' => 'Ditulis petugas'])
            // Mengganti kategori tidak menghapus isian petugas.
            ->assertFormSet(['applicant_name' => 'Ditulis petugas'])
            ->call('submit')
            ->assertHasFormErrors(['applicant_phone' => 'required']);
    }

    public function test_admin_gets_a_link_to_fix_the_account_in_the_applicant_tab(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('frontdesk.access');
        $applicant = User::factory()->create(['email' => 'sari@contoh.id']);
        $applicant->assignRole('umum');

        $this->actingAs($admin);

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar', 'applicant_id' => $applicant->id])
            ->assertSee('Perbaiki di Pengguna')
            ->assertSee(e(\App\Filament\Resources\UserResource::getUrl('index', ['activeTab' => 'pemohon', 'tableSearch' => 'sari@contoh.id'])), false);
    }

    public function test_switching_status_keeps_the_request_details(): void
    {
        $service = Service::factory()->create(['user_types_allowed' => ['siswa']]);

        Livewire::test(RegisterService::class)
            ->fillForm([
                'applicant_type' => 'siswa',
                'service_id' => $service->id,
                'description' => 'Surat keterangan aktif.',
                'priority' => 'high',
            ])
            ->fillForm(['applicant_status' => 'terdaftar'])
            ->assertFormSet(['applicant_type' => 'siswa', 'service_id' => $service->id, 'description' => 'Surat keterangan aktif.', 'priority' => 'high'])
            ->fillForm(['applicant_status' => 'baru'])
            ->assertFormSet(['applicant_type' => 'siswa', 'service_id' => $service->id, 'description' => 'Surat keterangan aktif.', 'priority' => 'high'])
            ->assertSee('Standar pelayanan');
    }

    public function test_changing_user_type_keeps_the_service_only_while_it_is_offered(): void
    {
        $forStudents = Service::factory()->create(['user_types_allowed' => ['siswa', 'alumni']]);

        Livewire::test(RegisterService::class)
            ->fillForm(['applicant_status' => 'terdaftar', 'applicant_type' => 'siswa', 'service_id' => $forStudents->id, 'description' => 'Legalisir.'])
            ->fillForm(['applicant_type' => 'alumni'])
            ->assertFormSet(['service_id' => $forStudents->id, 'description' => 'Legalisir.'])
            // Layanan khusus siswa/alumni tidak ditawarkan kepada pemohon umum.
            ->fillForm(['applicant_type' => 'umum'])
            ->assertFormSet(['service_id' => null, 'description' => 'Legalisir.'])
            ->assertFormFieldExists('service_id', fn ($field) => ! array_key_exists($forStudents->id, $field->getOptions()))
            ->assertSee('Hanya layanan yang terbuka untuk Masyarakat Umum');
    }

    public function test_account_picker_lists_only_active_applicants(): void
    {
        $applicant = User::factory()->create(['name' => 'Rahma Siswa', 'email' => 'rahma@contoh.id', 'whatsapp_number' => '081111']);
        $applicant->assignRole('siswa');
        $inactive = User::factory()->create(['name' => 'Rahma Nonaktif', 'is_active' => false]);
        $staff = User::factory()->create(['name' => 'Rahma Petugas']);
        $staff->assignRole('back_office');

        $options = RegisterService::applicantOptions('rahma');

        $this->assertSame([$applicant->id], array_keys($options));
        $this->assertSame('Rahma Siswa — 081111 · rahma@contoh.id', $options[$applicant->id]);
        // Pencarian juga lewat email dan nomor WhatsApp.
        $this->assertArrayHasKey($applicant->id, RegisterService::applicantOptions('RAHMA@contoh'));
        $this->assertArrayHasKey($applicant->id, RegisterService::applicantOptions('0811'));
        $this->assertArrayNotHasKey($inactive->id, RegisterService::applicantOptions('nonaktif'));
        $this->assertArrayNotHasKey($staff->id, RegisterService::applicantOptions('petugas'));
    }
}
