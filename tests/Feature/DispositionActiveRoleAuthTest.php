<?php

namespace Tests\Feature;

use App\Enums\SignatureModel;
use App\Filament\Pages\Services\DispositionSettings;
use App\Models\DispositionLog;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Otorisasi disposisi (antrean, lembar TTD, pengaturan) mengikuti peran aktif. */
class DispositionActiveRoleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $active, string ...$roles): User
    {
        $user = User::factory()->create();
        $user->assignRole($roles);
        $user->forceFill(['active_role_id' => Role::findByName($active, 'web')->id])->save();

        return $user->fresh();
    }

    private function actIn(User $user, string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public function test_queue_api_tells_which_role_is_deciding(): void
    {
        Sanctum::actingAs($this->staff('front_desk', 'kepala_tu', 'front_desk'));
        $this->getJson('/api/disposisi/antrean')->assertOk()
            ->assertJsonPath('peran_aktif', 'front_desk')
            ->assertJsonPath('memutuskan_sebagai_pimpinan', false)
            ->assertJsonPath('total', 0);

        $this->putJson('/api/peran-aktif', ['peran' => 'kepala_tu'])->assertOk();
        $this->getJson('/api/disposisi/antrean')->assertOk()
            ->assertJsonPath('peran_aktif', 'kepala_tu')
            ->assertJsonPath('memutuskan_sebagai_pimpinan', true);
    }

    public function test_signed_sheet_is_added_in_the_role_the_disposition_was_made_in(): void
    {
        $user = $this->staff('kepala_tu', 'kepala_tu', 'front_desk');
        $disposition = DispositionLog::create([
            'ticket_id' => Ticket::query()->firstOrFail()->id,
            'actor_id' => $user->id,
            'role' => 'kepala_tu',
            'action' => 'disposisi',
            'signature_model' => SignatureModel::TtdUpload->value,
        ]);

        $this->actIn($user, 'kepala_tu');
        $this->assertTrue($user->can('uploadSignature', $disposition));

        $this->actIn($user, 'front_desk');
        $this->assertFalse($user->can('uploadSignature', $disposition));
    }

    public function test_disposition_settings_need_the_admin_role_active(): void
    {
        $user = $this->staff('front_desk', 'admin', 'front_desk');
        $service = Service::query()->firstOrFail();

        $this->actIn($user, 'front_desk');
        $this->assertFalse(DispositionSettings::canAccess());

        Sanctum::actingAs($user);
        $this->putJson('/api/pengaturan-disposisi/' . $service->slug, ['mode' => 'none'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Pengaturan disposisi diubah dari peran aktif Administrator.');

        $this->actIn($user, 'admin');
        $this->assertTrue(DispositionSettings::canAccess());
    }

    public function test_single_role_admin_is_unaffected(): void
    {
        $this->actingAs(User::role('admin')->firstOrFail());

        $this->assertTrue(DispositionSettings::canAccess());
    }
}
