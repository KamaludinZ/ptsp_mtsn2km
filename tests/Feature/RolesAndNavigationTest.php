<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use App\Filament\Pages\FrontDesk\RegisterService;
use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Pages\Reports\Performance;
use App\Filament\Portal\Resources\TicketResource as PortalTicketResource;
use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VisitorResource;
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
            'kepala_sekolah' => '/cp',
            'kepala_tu' => '/cp',
            'back_office' => '/cp',
            'front_desk' => '/cp',
            'supervisor' => '/cp',
            'umum' => '/portal',
        ];

        foreach ($expected as $role => $url) {
            $this->actingAs($this->userWithRole($role))->get('/dashboard')->assertRedirect($url);
        }
    }

    /**
     * [role => [menus shown, menus hidden]]. One test per role: Filament
     * registers the navigation once per application instance.
     */
    public static function menus(): array
    {
        return [
            'front_desk' => ['front_desk', ['register', 'guestBook', 'tickets'], ['approvals', 'complaints', 'users']],
            'back_office' => ['back_office', ['tickets', 'performance'], ['register', 'approvals', 'users']],
            'kepala_sekolah' => ['kepala_sekolah', ['approvals', 'tickets', 'complaints', 'performance'], ['register', 'users']],
            'kepala_tu' => ['kepala_tu', ['approvals', 'register', 'guestBook', 'tickets', 'complaints'], ['users']],
            'supervisor' => ['supervisor', ['performance', 'complaints'], ['approvals', 'register', 'users']],
            'admin' => ['admin', ['register', 'guestBook', 'tickets', 'approvals', 'complaints', 'performance', 'users'], []],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('menus')]
    public function test_sidebar_shows_each_role_its_own_menus(string $role, array $shown, array $hidden): void
    {
        $urls = [
            'register' => RegisterService::getUrl(),
            'guestBook' => VisitorResource::getUrl('index'),
            'tickets' => TicketResource::getUrl('index'),
            'approvals' => Approvals::getUrl(),
            'complaints' => ComplaintResource::getUrl('index'),
            'performance' => Performance::getUrl(),
            'users' => UserResource::getUrl('index'),
        ];

        $page = $this->actingAs($this->userWithRole($role))->get('/cp')->assertOk();

        foreach ($shown as $menu) {
            $page->assertSee('href="' . $urls[$menu] . '"', false);
        }
        foreach ($hidden as $menu) {
            $page->assertDontSee('href="' . $urls[$menu] . '"', false);
        }
    }

    public function test_applicants_see_the_portal_menus(): void
    {
        $this->actingAs($this->userWithRole('umum'))->get('/portal')->assertOk()
            ->assertSee(route('onlineportal.service.catalog'), false)
            ->assertSee(PortalTicketResource::getUrl('index', panel: 'portal'), false)
            ->assertDontSee('/cp/', false);
    }

    public function test_former_dashboard_addresses_forward_to_the_panels(): void
    {
        $forwards = [
            '/admin' => '/cp',
            '/pimpinan' => '/cp',
            '/pimpinan/persetujuan' => '/cp/pimpinan/disposisi',
            '/cp/pimpinan/persetujuan' => '/cp/pimpinan/disposisi',
            '/frontdesk/dashboard' => '/cp',
            '/backoffice/dashboard' => '/cp',
            '/supervision/management' => '/cp',
            '/portal/dashboard' => '/portal',
            '/portal/my-tickets' => '/portal/permohonan',
        ];

        foreach ($forwards as $from => $to) {
            $this->get($from)->assertRedirect($to);
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
