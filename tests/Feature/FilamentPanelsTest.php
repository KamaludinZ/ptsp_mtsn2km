<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every role lands in its own panel and sees exactly the pages it may use:
 * staff in /cp (each role its own menus), applicants in /portal.
 */
class FilamentPanelsTest extends TestCase
{
    use RefreshDatabase;

    /** Staff pages and the roles that may open them. */
    private const STAFF_PAGES = [
        '/cp' => ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'],
        '/cp/tiket' => ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'],
        '/cp/pimpinan/persetujuan' => ['admin', 'kepala_sekolah', 'kepala_tu'],
        '/cp/kinerja' => ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'supervisor'],
        '/cp/visitors' => ['admin', 'kepala_tu', 'front_desk'],
        '/cp/visitors/create' => ['admin', 'kepala_tu', 'front_desk'],
        '/cp/loket/registrasi-layanan' => ['admin', 'kepala_tu', 'front_desk'],
        '/cp/pengaduan' => ['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor'],
        '/cp/laporan-survei' => ['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor'],
        '/cp/survei/pertanyaan' => ['admin'],
        '/cp/survei/unsur' => ['admin'],
        '/cp/survei/edisi' => ['admin'],
        '/cp/keamanan' => ['admin'],
        '/cp/users' => ['admin'],
        '/cp/services' => ['admin'],
        '/cp/app-settings' => ['admin'],
        '/cp/roles' => ['admin'],
        '/cp/profile' => ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'],
    ];

    private const STAFF_ROLES = ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'];

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        Role::findOrCreate('pemohon');
    }

    private function user(?string $role = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['user_type' => $role ? 'pegawai' : 'umum', 'email_verified_at' => now()]);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function seedData(): Ticket
    {
        Service::factory()->count(2)->create();
        Complaint::factory()->count(2)->create(['complaint_type' => 'complaint']);
        Complaint::factory()->create(['complaint_type' => 'whistleblowing']);
        Visitor::factory()->count(2)->create();

        return Ticket::factory()->create(['status' => 'submitted']);
    }

    public function test_each_staff_role_opens_only_its_pages(): void
    {
        $ticket = $this->seedData();
        $complaint = Complaint::first();
        $pages = self::STAFF_PAGES + [
            '/cp/tiket/' . $ticket->id => self::STAFF_ROLES,
            '/cp/pengaduan/' . $complaint->id => ['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor'],
            '/buku-tamu/' . Visitor::first()->id . '/cetak' => ['admin', 'kepala_tu', 'front_desk'],
            '/tiket/' . $ticket->id . '/tanda-terima' => self::STAFF_ROLES,
        ];

        $failures = [];
        foreach (self::STAFF_ROLES as $role) {
            $user = $this->user($role);

            foreach ($pages as $uri => $allowed) {
                $expected = in_array($role, $allowed, true) ? 200 : 403;
                $status = $this->actingAs($user)->get($uri)->getStatusCode();

                if ($status !== $expected) {
                    $failures[] = "{$role} GET {$uri} -> {$status} (expected {$expected})";
                }
            }
        }

        $this->assertSame([], $failures);
    }

    public function test_applicants_use_the_portal_and_cannot_open_the_staff_panel(): void
    {
        $applicant = $this->user();

        $this->actingAs($applicant)->get('/cp')->assertForbidden();
        $this->actingAs($applicant)->get('/portal')->assertOk();

        $this->actingAs($this->user('back_office'))->get('/portal')->assertForbidden();
    }

    public function test_guests_are_sent_to_the_site_login(): void
    {
        $this->get('/cp')->assertRedirect('/login');
        $this->get('/portal')->assertRedirect('/login');
    }

    public function test_deactivated_accounts_are_locked_out(): void
    {
        $this->actingAs($this->user('back_office', ['is_active' => false]))->get('/cp')->assertForbidden();
    }
}
