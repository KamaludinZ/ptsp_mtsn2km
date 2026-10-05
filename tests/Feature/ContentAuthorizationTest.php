<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Manajemen Konten: only active administrators manage pengumuman and FAQ. */
class ContentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Pengumuman $draft;

    private Faq $faq;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->draft = Pengumuman::create(['title' => 'Draf', 'content' => 'x', 'publish_date' => today(), 'is_active' => false]);
        $this->faq = Faq::create(['question' => 'Jam layanan?', 'answer' => 'Senin–Jumat.']);
    }

    /** @return array<string, array{0: string, 1: string}> */
    private function endpoints(): array
    {
        $p = $this->draft->id;
        $f = $this->faq->id;

        return [
            ['GET', '/api/pengumuman'], ['POST', '/api/pengumuman'], ['GET', "/api/pengumuman/{$p}"],
            ['PATCH', "/api/pengumuman/{$p}"], ['DELETE', "/api/pengumuman/{$p}"], ['POST', "/api/pengumuman/{$p}/tayangkan"],
            ['GET', '/api/faq'], ['POST', '/api/faq'], ['GET', "/api/faq/{$f}"], ['PATCH', "/api/faq/{$f}"],
            ['DELETE', "/api/faq/{$f}"], ['PUT', '/api/faq/urutan'], ['POST', "/api/faq/{$f}/naik"],
        ];
    }

    public function test_guests_must_sign_in(): void
    {
        foreach ($this->endpoints() as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized();
        }
    }

    /** @dataProvider nonAdmins */
    public function test_other_accounts_are_refused(?string $role, bool $active): void
    {
        $user = User::factory()->create(['is_active' => $active]);
        $role && $user->assignRole(Role::findByName($role, 'web'));
        Sanctum::actingAs($user);

        foreach ($this->endpoints() as [$method, $uri]) {
            $this->json($method, $uri)->assertForbidden();
        }

        $this->assertNotNull($this->draft->fresh());
        $this->assertNotNull($this->faq->fresh());
    }

    public static function nonAdmins(): array
    {
        return [
            'front desk' => ['front_desk', true],
            'kepala madrasah' => ['kepala_sekolah', true],
            'pemohon (no role)' => [null, true],
            'deactivated admin' => ['admin', false],
        ];
    }

    /** The policies hold even without the route middleware (Filament and any future route use them). */
    public function test_policies(): void
    {
        $admin = User::factory()->create()->assignRole(Role::findByName('admin', 'web'));
        $staff = User::factory()->create()->assignRole(Role::findByName('back_office', 'web'));
        $live = Pengumuman::create(['title' => 'Live', 'content' => 'x', 'publish_date' => today(), 'is_active' => true]);

        foreach (['update', 'delete', 'publish'] as $ability) {
            $this->assertTrue(Gate::forUser($admin)->allows($ability, $this->draft));
            $this->assertFalse(Gate::forUser($staff)->allows($ability, $this->draft));
        }
        $this->assertTrue(Gate::forUser($admin)->allows('reorder', Faq::class));
        $this->assertFalse(Gate::forUser($staff)->allows('reorder', Faq::class));
        $this->assertFalse(Gate::forUser($staff)->allows('update', $this->faq));

        // Reading: live ones are public, the rest only for administrators
        $this->assertTrue(Gate::forUser(null)->allows('view', $live));
        $this->assertFalse(Gate::forUser(null)->allows('view', $this->draft));
        $this->assertFalse(Gate::forUser($staff)->allows('view', $this->draft));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $this->draft));
    }

    public function test_public_page_preview_and_view_count(): void
    {
        $this->get(route('pengumuman.show', $this->draft))->assertNotFound();

        $this->actingAs(User::factory()->create()->assignRole(Role::findByName('admin', 'web')));
        $this->get(route('pengumuman.show', $this->draft))->assertOk()->assertSee('Draf');
        $this->assertSame(0, $this->draft->fresh()->view_count);
    }
}
