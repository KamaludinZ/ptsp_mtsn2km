<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Pemetaan staf ke unit kerja through the API. */
class UnitStaffApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_staff_list_the_units_with_their_holders(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->getJson('/api/unit-kerja')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.unit', 'waka_humas')
            ->assertJsonStructure(['data' => [['unit', 'label', 'keterangan', 'jumlah_layanan', 'staf']]]);

        $this->putJson('/api/unit-kerja/penjamin_mutu', ['staf' => []])->assertForbidden();
    }

    public function test_admin_replaces_a_units_holders(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        $staff = $this->user('staff2@mtsn2malang.sch.id');
        $frontDesk = $this->user('loket1@mtsn2malang.sch.id');

        $this->putJson('/api/unit-kerja/penjamin_mutu', ['staf' => [$frontDesk->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('staf.0');
        $this->putJson('/api/unit-kerja/kantin', ['staf' => []])->assertNotFound();

        $this->putJson('/api/unit-kerja/penjamin_mutu', ['staf' => [$staff->id]])
            ->assertOk()
            ->assertJsonPath('unit', 'penjamin_mutu')
            ->assertJsonPath('staf.0.id', $staff->id);
        $this->assertTrue($staff->fresh()->hasRole('penjamin_mutu'));

        $this->putJson('/api/unit-kerja/penjamin_mutu', ['staf' => []])->assertOk()->assertJsonCount(0, 'staf');
        $this->assertFalse($staff->fresh()->hasRole('penjamin_mutu'));
    }
}
