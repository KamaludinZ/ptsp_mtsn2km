<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The panel works on the finished backend, not on mock data: the register,
 * the access panel and the buttons give the same answers as /api/penomoran-sk.
 * (Livewire steps run before Sanctum ones: Sanctum::actingAs switches the default guard.)
 */
class RegisterSkPanelApiParityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $acting, string ...$roles): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($roles);
        $user->forceFill(['active_role_id' => Role::findByName($acting, 'web')->id])->save();

        return $user->fresh();
    }

    public static function officers(): array
    {
        return [
            'back office' => ['back_office', ['back_office']],
            'admin acting as back office' => ['back_office', ['admin', 'back_office']],
            'admin acting as admin' => ['admin', ['admin', 'back_office']],
        ];
    }

    /** @dataProvider officers */
    public function test_the_panel_offers_what_the_api_says_the_role_may_do(string $acting, array $roles): void
    {
        $user = $this->staff($acting, ...$roles);
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        $this->actingAs($user);
        session([ActiveRoles::SESSION_KEY => $acting]);

        $page = Livewire::test(ListSuratKeputusan::class);
        $panel = [
            'minta_nomor' => $page->instance()->getAction('reserve')?->isVisible() ?? false,
            'ubah' => $page->instance()->getTable()->getAction('edit')->record($sk)->isVisible(),
            'hapus' => $page->instance()->getTable()->getAction('delete')->record($sk)->isVisible(),
        ];
        $listed = collect(RegisterSkAccess::actionsFor($user))->pluck('allowed', 'key')->all();

        Sanctum::actingAs($user, ['*']);
        $api = $this->getJson('/api/penomoran-sk/akses')->assertOk()->json('wewenang');

        $this->assertSame($api, $listed);
        $this->assertSame(array_intersect_key($api, $panel), $panel);
        $this->assertSame($api['hapus'], $this->getJson("/api/penomoran-sk/{$sk->id}")->json('wewenang.hapus'));
    }

    public static function filters(): array
    {
        return [
            'this year' => [[], []],
            'jenis' => [['jenis_sk' => ['Kepanitiaan dan Tim Pelaksana Kegiatan']], ['jenis_sk' => ['Kepanitiaan dan Tim Pelaksana Kegiatan']]],
            'status' => [['status' => 'menunggu_scan'], ['status' => 'menunggu_scan']],
            'every year' => [['tahun' => null], ['tahun' => 'semua']],
        ];
    }

    /** @dataProvider filters */
    public function test_the_register_lists_what_the_api_lists(array $panelFilters, array $apiQuery): void
    {
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($officer);

        $page = Livewire::test(ListSuratKeputusan::class);
        foreach ($panelFilters as $name => $value) {
            $page->filterTable($name, $value);
        }
        $listed = $page->instance()->getTableRecords()->pluck('nomor_sk')->all();

        Sanctum::actingAs($officer, ['*']);
        $api = $this->getJson('/api/penomoran-sk?' . http_build_query($apiQuery))->assertOk();

        $this->assertSame($api->json('total'), $page->instance()->getAllTableRecordsCount());
        $this->assertSame($api->json('data.*.nomor_sk'), $listed);
    }

    public function test_searching_the_register_matches_the_api_search(): void
    {
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($officer);

        $listed = Livewire::test(ListSuratKeputusan::class)->searchTable('panitia')
            ->instance()->getTableRecords()->pluck('nomor_sk')->all();

        Sanctum::actingAs($officer, ['*']);
        $this->assertSame($this->getJson('/api/penomoran-sk?q=panitia')->assertOk()->json('data.*.nomor_sk'), $listed);
    }
}
