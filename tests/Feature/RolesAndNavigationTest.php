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

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    public function test_dashboard_redirects_by_canonical_role(): void
    {
        $expected = [
            'admin' => '/admin',
            'kepala_sekolah' => '/backoffice/dashboard',
            'kepala_tu' => '/backoffice/dashboard',
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

        $this->actingAs($this->userWithRole('umum'))->get('/profile')
            ->assertOk()
            ->assertSee(route('onlineportal.service.catalog'), false)
            ->assertDontSee(route('backoffice.tickets.queue'), false);
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
