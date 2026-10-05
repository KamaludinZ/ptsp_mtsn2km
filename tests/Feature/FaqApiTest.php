<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/faq: questions for administrators, with ordering. */
class FaqApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole(Role::findByName('admin', 'web')));
    }

    private function faq(string $question, array $values = []): Faq
    {
        return Faq::create($values + ['question' => $question, 'answer' => 'Jawaban ' . $question]);
    }

    private function order(): array
    {
        return collect($this->getJson('/api/faq')->assertOk()->json('data'))->pluck('pertanyaan')->all();
    }

    public function test_only_administrators(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole(Role::findByName('back_office', 'web')));

        $this->getJson('/api/faq')->assertForbidden();
        $this->putJson('/api/faq/urutan', ['urutan' => [1]])->assertForbidden();
    }

    public function test_create_update_filter_and_delete(): void
    {
        $this->faq('Lama', ['sort' => 7]);

        $created = $this->postJson('/api/faq', ['pertanyaan' => ' Jam layanan? ', 'jawaban' => '<p>Senin–Jumat</p><img src=x onerror=alert(1)>', 'kelompok' => 'Layanan'])
            ->assertCreated()
            ->assertJsonPath('pertanyaan', 'Jam layanan?')
            ->assertJsonPath('kelompok', 'layanan')
            ->assertJsonPath('kelompok_label', 'Layanan')
            ->assertJsonPath('urutan', 8)
            ->assertJsonPath('aktif', true);
        $this->assertStringNotContainsString('onerror', $created->json('jawaban'));
        $id = $created->json('id');

        $this->patchJson("/api/faq/{$id}", ['aktif' => false, 'kelompok' => null])->assertOk()
            ->assertJsonPath('aktif', false)->assertJsonPath('kelompok_label', 'Umum')->assertJsonPath('pertanyaan', 'Jam layanan?');

        $this->getJson('/api/faq?aktif=0')->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/faq?q=jam')->assertJsonPath('total', 1);
        $this->getJson('/api/faq?kelompok=umum')->assertJsonPath('total', 2);

        $this->postJson('/api/faq', ['pertanyaan' => '   ', 'jawaban' => '<p> </p>'])->assertJsonValidationErrors(['pertanyaan' => 'tidak boleh kosong']);
        $this->postJson('/api/faq', ['pertanyaan' => 'Ok?', 'jawaban' => '<script>x</script>'])->assertJsonValidationErrors(['jawaban' => 'tidak boleh kosong']);
        $this->patchJson("/api/faq/{$id}", ['urutan' => -1])->assertJsonValidationErrors('urutan');

        $this->deleteJson("/api/faq/{$id}")->assertNoContent();
        $this->getJson("/api/faq/{$id}")->assertNotFound();
    }

    public function test_list_is_in_display_order_and_can_be_reordered(): void
    {
        $a = $this->faq('A', ['category' => 'akun', 'sort' => 1]);
        $b = $this->faq('B', ['category' => 'akun', 'sort' => 2]);
        $c = $this->faq('C', ['sort' => 1]);
        $d = $this->faq('D', ['category' => 'akun', 'sort' => 3]);

        $this->assertSame(['A', 'B', 'D', 'C'], $this->order());

        $this->putJson('/api/faq/urutan', ['urutan' => [$d->id, $a->id]])->assertOk()->assertJsonPath('data.0.pertanyaan', 'D');
        $this->assertSame(['D', 'A', 'B', 'C'], $this->order());
        $this->assertSame(['D', 'A', 'B'], Faq::published()->where('category', 'akun')->pluck('question')->all());

        $this->putJson('/api/faq/urutan', ['urutan' => [$a->id, $a->id]])->assertJsonValidationErrors(['urutan.1' => 'dua kali']);
        $this->putJson('/api/faq/urutan', ['urutan' => [999999]])->assertJsonValidationErrors(['urutan.0' => 'tidak ditemukan']);
    }

    public function test_move_one_step_within_its_group(): void
    {
        // Ties from older data still move
        $a = $this->faq('A', ['category' => 'akun', 'sort' => 0]);
        $b = $this->faq('B', ['category' => 'akun', 'sort' => 0]);
        $c = $this->faq('C', ['sort' => 0]);

        $this->postJson("/api/faq/{$b->id}/naik")->assertOk();
        $this->assertSame(['B', 'A', 'C'], $this->order());

        $this->postJson("/api/faq/{$b->id}/naik")->assertUnprocessable()->assertJsonPath('message', 'Pertanyaan ini sudah paling atas.');
        $this->postJson("/api/faq/{$a->id}/turun")->assertUnprocessable()->assertJsonPath('message', 'Pertanyaan ini sudah paling bawah.');
        $this->postJson("/api/faq/{$c->id}/naik")->assertUnprocessable();

        $this->postJson("/api/faq/{$b->id}/turun")->assertOk();
        $this->assertSame(['A', 'B', 'C'], $this->order());
    }
}
