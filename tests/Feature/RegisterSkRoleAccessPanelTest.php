<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Panel "Akses untuk peran aktif" on the SK register. */
class RegisterSkRoleAccessPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($roles);

        return $user->fresh();
    }

    private function register(User $user, string $role): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)
            ->withSession([ActiveRoles::SESSION_KEY => $role])
            ->get(SuratKeputusanResource::getUrl('index'))
            ->assertOk();
    }

    public function test_the_register_tells_a_back_office_officer_where_deleting_lives(): void
    {
        $this->register($this->staff('admin', 'back_office'), 'back_office')
            ->assertSee('Akses untuk peran aktif: ' . RoleAccess::roleLabel('back_office'))
            ->assertSee('data-sk-action="minta_nomor" data-allowed="ya"', false)
            ->assertSee('data-sk-action="ubah" data-allowed="ya"', false)
            ->assertSee('data-sk-action="hapus" data-allowed="tidak"', false)
            ->assertSee('Ganti ke ' . RoleAccess::roleLabel('admin'))
            ->assertSee(route('peran-aktif.ganti'), false);
    }

    public function test_acting_as_admin_everything_is_available(): void
    {
        $this->register($this->staff('admin', 'back_office'), 'admin')
            ->assertSee('Akses untuk peran aktif: ' . RoleAccess::roleLabel('admin'))
            ->assertSee('data-sk-action="hapus" data-allowed="ya"', false)
            ->assertDontSee('data-allowed="tidak"', false)
            ->assertDontSee('Ganti ke ');
    }

    public function test_only_roles_the_officer_holds_are_offered(): void
    {
        $user = $this->staff('back_office', 'tata_usaha');

        $this->register($user, 'tata_usaha')
            ->assertSee('data-sk-action="hapus" data-allowed="tidak"', false)
            ->assertDontSee('Ganti ke ');

        $hapus = collect(RegisterSkAccess::actionsFor($user))->firstWhere('key', 'hapus');
        $this->assertSame([RoleAccess::roleLabel('admin')], $hapus['roles']);
        $this->assertNull($hapus['switch_to']);
    }

    public function test_officers_with_one_role_see_the_register_without_the_panel(): void
    {
        $this->register(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail(), 'back_office')
            ->assertDontSee('Akses untuk peran aktif');
    }

    public function test_the_switch_comes_back_to_the_register_in_the_new_role(): void
    {
        $user = $this->staff('admin', 'back_office');
        $url = SuratKeputusanResource::getUrl('index');

        $this->actingAs($user)->withSession([ActiveRoles::SESSION_KEY => 'back_office'])
            ->post(route('peran-aktif.ganti'), ['peran' => 'admin', 'kembali' => $url])
            ->assertRedirect($url);

        $this->get($url)->assertOk()->assertSee('data-sk-action="hapus" data-allowed="ya"', false);
    }
}
