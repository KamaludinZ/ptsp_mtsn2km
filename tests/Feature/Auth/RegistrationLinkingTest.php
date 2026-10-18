<?php

namespace Tests\Feature\Auth;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Pendaftaran portal menyambung ke akun pemohon yang dicatat di loket (email
 * atau WhatsApp sama, belum pernah dipakai) alih-alih membuat akun ganda.
 */
class RegistrationLinkingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        foreach (['umum', 'siswa', 'back_office'] as $role) {
            Role::findOrCreate($role);
        }
    }

    private function register(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/register', $overrides + [
            'name' => 'Sari Pendaftar',
            'email' => 'sari@contoh.id',
            'whatsapp_number' => '081255550000',
            'user_type' => 'umum',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    private function walkIn(array $attributes = [], ?string $role = 'umum'): User
    {
        $user = User::factory()->unverified()->create($attributes + [
            'name' => 'Sari (loket)',
            'email' => '081255550000@walkin.local',
            'whatsapp_number' => '081255550000',
            'user_type' => 'umum',
        ]);
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function test_counter_account_with_the_same_whatsapp_is_linked_not_duplicated(): void
    {
        $account = $this->walkIn();
        $ticket = Ticket::factory()->create(['user_id' => $account->id]);
        $users = User::count();

        $this->register(['email' => 'Sari@Contoh.id'])->assertSessionHasNoErrors()->assertRedirect(route('verification.notice', absolute: false));

        $this->assertSame($users, User::count(), 'Tidak ada akun ganda.');
        $account->refresh();
        $this->assertAuthenticatedAs($account);
        $this->assertSame('sari@contoh.id', $account->email);
        $this->assertSame('Sari Pendaftar', $account->name);
        $this->assertTrue(Hash::check('password', $account->password));
        $this->assertNull($account->email_verified_at, 'Email tetap harus diverifikasi sebelum portal dibuka.');
        $this->assertTrue($account->hasRole('umum'));
        $this->assertSame($account->id, $ticket->fresh()->user_id);
        $this->assertDatabaseHas('activity_log', ['subject_id' => $account->id, 'description' => 'Pendaftaran portal disambungkan ke akun pemohon dari loket']);

        // Rincian riwayat menunggu di notifikasi portal (terbuka setelah email terverifikasi).
        $notification = $account->notifications()->firstOrFail();
        $this->assertSame('Akun tersambung dengan data dari loket', $notification->title());
        $this->assertSame('1 permohonan yang didaftarkan di loket PTSP kini tampil di Permohonan Saya.', $notification->body());
    }

    public function test_a_fresh_sign_up_gets_no_link_notification(): void
    {
        $this->register()->assertSessionHasNoErrors();

        $this->assertSame(0, User::where('email', 'sari@contoh.id')->firstOrFail()->notifications()->count());
    }

    public function test_sign_up_pages_tell_the_applicant_about_the_link(): void
    {
        $this->get('/register')->assertOk()->assertSee('Pernah dilayani di loket? Pakai email atau nomor WhatsApp yang sama');

        $this->walkIn();
        $this->followingRedirects()->register()
            ->assertOk()
            ->assertSee('Akun Anda tersambung dengan data pemohon yang sudah dicatat di loket PTSP.')
            // Rincian riwayat baru terlihat setelah email terverifikasi.
            ->assertDontSee('permohonan sebelumnya');
    }

    public function test_a_fresh_sign_up_shows_no_link_message(): void
    {
        $this->followingRedirects()->register()
            ->assertOk()
            ->assertSee('Registrasi berhasil!')
            ->assertDontSee('tersambung dengan data pemohon');
    }

    public function test_portal_and_counter_share_one_applicant_service(): void
    {
        $front = app(\App\Services\FrontDeskService::class);
        $account = ['name' => 'Baru', 'email' => 'baru@contoh.id', 'whatsapp_number' => '085711112222', 'password' => Hash::make('password')];

        $created = $front->registerPortalApplicant($account, 'siswa', 'KODE');
        $this->assertSame(\App\Support\ApplicantLink::CREATED, $created->status);
        $this->assertTrue($created->user->hasRole('siswa'));
        $this->assertSame('KODE', $created->user->registration_code);

        // Akun yang dibuat loket lalu didaftarkan di portal: akun yang sama, tertaut lewat WhatsApp.
        $walkIn = $front->resolveApplicant(['applicant_name' => 'Loket', 'applicant_phone' => '085733334444', 'applicant_type' => 'umum']);
        $this->assertSame(\App\Support\ApplicantLink::CREATED, $walkIn->status);
        $linked = $front->registerPortalApplicant(['email' => 'loket@contoh.id', 'whatsapp_number' => '0857-3333-4444'] + $account, 'umum');
        $this->assertTrue($linked->user->is($walkIn->user));
        $this->assertSame(['status' => 'tertaut', 'dicocokkan_lewat' => 'whatsapp', 'pemohon_id' => $walkIn->user->id], $linked->toArray());
    }

    public function test_counter_account_with_the_same_email_is_linked(): void
    {
        $account = $this->walkIn(['email' => 'sari@contoh.id', 'whatsapp_number' => '0899']);

        $this->register()->assertSessionHasNoErrors();

        $account->refresh();
        $this->assertAuthenticatedAs($account);
        $this->assertSame('081255550000', $account->whatsapp_number);
        $this->assertSame(1, User::where('email', 'sari@contoh.id')->count());
    }

    public function test_legacy_account_without_a_role_gets_the_registration_type(): void
    {
        $account = $this->walkIn(['user_type' => 'pegawai'], role: null);

        $this->register()->assertSessionHasNoErrors();

        $account->refresh();
        $this->assertSame('umum', $account->user_type);
        $this->assertTrue($account->hasRole('umum'));
        $this->assertTrue($account->isApplicant());
    }

    public function test_an_email_already_in_use_is_still_refused(): void
    {
        $verified = User::factory()->create(['email' => 'sari@contoh.id']);
        $this->register()->assertSessionHasErrors('email');

        $verified->forceFill(['email' => 'lain@contoh.id'])->save();
        $usedBefore = User::factory()->unverified()->create(['email' => 'sari@contoh.id', 'last_login_at' => now()]);
        $this->register()->assertSessionHasErrors('email');

        $usedBefore->forceFill(['email' => 'lain2@contoh.id'])->save();
        $staff = User::factory()->unverified()->create(['email' => 'sari@contoh.id']);
        $staff->assignRole('back_office');
        $this->register()->assertSessionHasErrors('email');

        $staff->forceFill(['email' => 'lain3@contoh.id'])->save();
        User::factory()->unverified()->create(['email' => 'sari@contoh.id'])->delete();
        $this->register()->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_used_email_is_reported_together_with_the_other_field_errors(): void
    {
        User::factory()->create(['email' => 'sari@contoh.id']);

        $this->post('/register', ['name' => 'Sari', 'email' => 'SARI@contoh.id', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors(['email', 'whatsapp_number', 'user_type']);
        // Email akun loket yang belum diklaim tidak dianggap terpakai.
        $this->walkIn(['email' => 'loket@contoh.id']);
        $this->post('/register', ['name' => 'Sari', 'email' => 'loket@contoh.id', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors(['whatsapp_number'])
            ->assertSessionDoesntHaveErrors(['email']);
    }

    public function test_deactivated_counter_account_is_never_reactivated_by_a_sign_up(): void
    {
        $account = $this->walkIn(['email' => 'sari@contoh.id', 'is_active' => false]);
        $this->register()->assertSessionHasErrors('email');

        $account->forceFill(['email' => '081255550000@walkin.local'])->save();
        $this->register()->assertSessionHasNoErrors();

        $this->assertFalse($account->fresh()->is_active);
        $this->assertNotSame($account->id, auth()->id());
    }

    public function test_a_shared_whatsapp_number_does_not_hand_over_someone_elses_account(): void
    {
        // Akun yang sudah dipakai pemiliknya (mis. anak yang memakai nomor orang tua).
        $claimed = $this->walkIn(['email' => 'anak@contoh.id', 'last_login_at' => now()]);
        // Akun loket dengan email asli: hanya tersambung lewat email itu sendiri.
        $withEmail = $this->walkIn(['email' => 'kakak@contoh.id']);

        $this->register()->assertSessionHasNoErrors();

        $this->assertNotContains(auth()->id(), [$claimed->id, $withEmail->id]);
        $this->assertSame('anak@contoh.id', $claimed->fresh()->email);
        $this->assertSame('kakak@contoh.id', $withEmail->fresh()->email);
    }

    public function test_portal_links_only_what_the_counter_and_applicant_tab_call_an_applicant(): void
    {
        Role::findOrCreate('tamu_khusus');
        Role::findOrCreate('guru');
        // Peran kustom tanpa peran pemohon: bukan pemohon di loket maupun tab Pemohon.
        $custom = $this->walkIn(['email' => 'sari@contoh.id'], role: 'tamu_khusus');
        $this->assertFalse($custom->isApplicant());
        $this->register()->assertSessionHasErrors('email');

        // Guru yang juga petugas: pemohon, tetapi akun petugas tidak pernah disambungkan dari pendaftaran.
        $custom->forceFill(['email' => 'lain@contoh.id'])->save();
        $teacherStaff = $this->walkIn(['email' => 'sari@contoh.id'], role: 'guru');
        $teacherStaff->assignRole('back_office');
        $this->assertTrue($teacherStaff->isApplicant());
        $this->register()->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_two_counter_accounts_on_one_number_are_left_alone(): void
    {
        $first = $this->walkIn(['email' => '0812-a@walkin.local']);
        $second = $this->walkIn(['email' => '0812-b@walkin.local']);

        $this->register()->assertSessionHasNoErrors();

        $this->assertNotContains(auth()->id(), [$first->id, $second->id]);
        $this->assertSame('Sari (loket)', $first->fresh()->name);
        $this->assertSame('Sari (loket)', $second->fresh()->name);
    }
}
