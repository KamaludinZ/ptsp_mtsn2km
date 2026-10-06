<?php

namespace Tests\Feature;

use App\Filament\Pages\System\Impersonation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Halaman Ganti Akun Sementara (panel admin). */
class ImpersonationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::role('admin')->firstOrFail();
    }

    public function test_admin_opens_the_page_with_every_user_listed(): void
    {
        $this->actingAs($this->admin())
            ->get(Impersonation::getUrl())
            ->assertOk()
            ->assertSee('Ganti Akun Sementara')
            ->assertSee('Masuk sebagai');

        Livewire::test(Impersonation::class)
            ->assertCanSeeTableRecords(User::query()->orderBy('name')->limit(5)->get());
    }

    public function test_other_staff_cannot_open_it(): void
    {
        $this->actingAs(User::role('front_desk')->firstOrFail());

        $this->assertFalse(Impersonation::canAccess());
        $this->get(Impersonation::getUrl())->assertForbidden();
    }

    public function test_own_and_inactive_accounts_cannot_be_chosen(): void
    {
        $admin = $this->admin();
        $inactive = User::factory()->create(['is_active' => false]);
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(Impersonation::class)
            ->assertTableActionDisabled('impersonate', $admin)
            ->assertTableActionDisabled('impersonate', $inactive)
            ->assertTableActionEnabled('impersonate', $applicant);
    }

    public function test_user_list_and_user_page_offer_the_same_action(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $this->actingAs($this->admin());

        Livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
            ->assertTableActionVisible('impersonate', $applicant);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $this->admin()->getRouteKey()])
            ->assertActionDisabled('impersonate');

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $applicant->getRouteKey()])
            ->assertActionVisible('impersonate')
            ->callAction('impersonate', ['reason' => 'Meninjau keluhan unggah berkas'])
            ->assertNotified('Anda memakai akun ' . $applicant->name)
            ->assertRedirect('/portal');

        $this->assertTrue(auth()->user()->is($applicant));
    }

    public function test_a_reason_is_required(): void
    {
        $this->actingAs($this->admin());
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        Livewire::test(Impersonation::class)
            ->callTableAction('impersonate', $applicant, ['reason' => 'pendek'])
            ->assertHasTableActionErrors(['reason' => 'min']);
        $this->assertTrue(auth()->user()->is($this->admin()));

        Livewire::test(Impersonation::class)
            ->callTableAction('impersonate', $applicant, ['reason' => 'Meninjau keluhan unggah berkas'])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Anda memakai akun ' . $applicant->name);
    }
}
