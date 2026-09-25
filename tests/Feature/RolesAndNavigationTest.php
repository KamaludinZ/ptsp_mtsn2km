<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Support\RoleAccess::sync();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    public function test_dashboard_redirects_by_canonical_role(): void
    {
        $expected = [
            'admin' => '/cp',
            'kepala_sekolah' => '/pimpinan',
            'kepala_tu' => '/pimpinan',
            'back_office' => '/backoffice/dashboard',
            'front_desk' => '/frontdesk/dashboard',
            'supervisor' => '/supervision/management',
            'umum' => '/portal/dashboard',
        ];

        foreach ($expected as $role => $url) {
            $this->actingAs($this->userWithRole($role))->get('/dashboard')->assertRedirect($url);
        }
    }

    public function test_sidebar_shows_staff_menus_for_canonical_roles(): void
    {
        $this->actingAs($this->userWithRole('front_desk'))->get('/profile')
            ->assertOk()
            ->assertSee(route('frontdesk.triage'), false);

        $this->actingAs($this->userWithRole('back_office'))->get('/profile')
            ->assertOk()
            ->assertSee(route('backoffice.tickets.queue'), false)
            ->assertDontSee(route('frontdesk.triage'), false);

        $this->actingAs($this->userWithRole('kepala_sekolah'))->get('/profile')
            ->assertOk()
            ->assertSee(route('leadership.approvals'), false)
            ->assertSee(route('backoffice.tickets.queue'), false)
            ->assertSee(route('admin.complaints.index'), false)
            ->assertDontSee(route('frontdesk.triage'), false)
            ->assertDontSee(route('admin.services.index'), false);

        $this->actingAs($this->userWithRole('supervisor'))->get('/profile')
            ->assertOk()
            ->assertSee(route('supervision.performance'), false)
            ->assertSee(route('admin.complaints.index'), false)
            ->assertDontSee(route('leadership.approvals'), false)
            ->assertDontSee(route('backoffice.tickets.queue'), false);

        $this->actingAs($this->userWithRole('umum'))->get('/profile')
            ->assertOk()
            ->assertSee(route('onlineportal.service.catalog'), false)
            ->assertSee(route('survey.form'), false)
            ->assertDontSee(route('backoffice.tickets.queue'), false)
            ->assertDontSee(route('admin.complaints.index'), false);
    }

    public function test_each_area_is_limited_to_its_roles(): void
    {
        $matrix = [
            '/pimpinan' => ['admin', 'kepala_sekolah', 'kepala_tu'],
            '/frontdesk/dashboard' => ['admin', 'kepala_tu', 'front_desk'],
            '/backoffice/dashboard' => ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office'],
            '/supervision/management' => ['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor'],
            '/admin/complaints' => ['admin', 'kepala_sekolah', 'kepala_tu', 'supervisor'],
            '/admin/services' => ['admin'],
        ];
        $roles = ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor', 'umum'];
        $users = collect($roles)->mapWithKeys(fn ($role) => [$role => $this->userWithRole($role)]);

        foreach ($matrix as $url => $allowed) {
            foreach ($users as $role => $user) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                in_array($role, $allowed, true)
                    ? $this->assertSame(200, $status, "{$role} should open {$url}")
                    : $this->assertSame(403, $status, "{$role} must not open {$url}");
            }
        }
    }

    public function test_contact_form_sends_mail_to_school(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'subject' => 'Jam layanan',
            'message' => 'Apakah loket buka hari Sabtu?',
        ])->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(ContactFormMail::class, fn ($mail) => $mail->data['subject'] === 'Jam layanan'
            && $mail->hasTo(config('mail.from.address'))
            && $mail->hasReplyTo('budi@example.com'));

        $html = (new ContactFormMail([
            'name' => 'Budi', 'email' => 'budi@example.com',
            'subject' => 'Jam layanan', 'message' => 'Apakah loket buka hari Sabtu?',
        ]))->render();
        $this->assertStringContainsString('Apakah loket buka hari Sabtu?', $html);
    }
}
