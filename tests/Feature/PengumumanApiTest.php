<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/pengumuman: announcements for administrators. */
class PengumumanApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create(['name' => 'Admin PTSP', 'user_type' => 'pegawai'])->assignRole(Role::findByName('admin', 'web'));
        Sanctum::actingAs($this->admin);
    }

    private function item(array $values = []): Pengumuman
    {
        return Pengumuman::create($values + ['title' => 'Pengumuman', 'content' => 'Isi', 'publish_date' => today(), 'is_active' => true]);
    }

    public function test_only_administrators(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole(Role::findByName('front_desk', 'web')));

        $this->getJson('/api/pengumuman')->assertForbidden();
        $this->postJson('/api/pengumuman', ['judul' => 'X'])->assertForbidden();
    }

    public function test_lists_every_status_with_filters(): void
    {
        $this->item(['title' => 'Libur semester', 'category' => 'akademik', 'publish_date' => today()->subDays(3)]);
        $this->item(['title' => 'Rapat wali murid', 'category' => 'kegiatan', 'publish_date' => today()->addWeek()]);
        $this->item(['title' => 'Draf jadwal', 'is_active' => false]);
        $this->item(['title' => 'PPDB lama', 'publish_date' => today()->subMonth(), 'end_date' => today()->subWeek(), 'view_count' => 40]);

        $this->getJson('/api/pengumuman')->assertOk()
            ->assertJsonPath('total', 4)
            ->assertJsonPath('jumlah_per_status', ['tayang' => 1, 'dijadwalkan' => 1, 'draf' => 1, 'berakhir' => 1])
            ->assertJsonPath('kategori', ['akademik', 'kegiatan']);

        $this->getJson('/api/pengumuman?status=dijadwalkan')->assertJsonPath('data.0.judul', 'Rapat wali murid')->assertJsonPath('data.0.halaman_publik', null);
        $this->getJson('/api/pengumuman?status=tayang')->assertJsonPath('data.0.status_label', 'Tayang')->assertJsonPath('total', 1);
        $this->getJson('/api/pengumuman?kategori=Akademik')->assertJsonPath('data.0.judul', 'Libur semester');
        $this->getJson('/api/pengumuman?q=wali')->assertJsonPath('total', 1);
        $this->getJson('/api/pengumuman?dari=' . today()->subMonths(2)->toDateString() . '&sampai=' . today()->subDays(2)->toDateString())->assertJsonPath('total', 2);
        $this->getJson('/api/pengumuman?urut=dilihat')->assertJsonPath('data.0.judul', 'PPDB lama')->assertJsonPath('data.0.dilihat', 40);
        $this->getJson('/api/pengumuman?status=arsip')->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->getJson('/api/pengumuman?dari=2026-05-02&sampai=2026-05-01')->assertJsonValidationErrors(['sampai' => 'tidak boleh sebelum']);
    }

    public function test_create_update_and_delete(): void
    {
        Storage::fake('public');

        $id = $this->post('/api/pengumuman', [
            'judul' => 'Libur Idul Adha',
            'isi' => '<p>Layanan tutup.</p><script>alert(1)</script>',
            'kategori' => ' Umum ',
            'tanggal_tayang' => today()->toDateString(),
            'tanggal_berakhir' => today()->addDays(3)->toDateString(),
            'lampiran' => UploadedFile::fake()->create('jadwal.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('status', 'tayang')
            ->assertJsonPath('kategori', 'umum')
            ->assertJsonPath('penulis', 'Admin PTSP')
            ->json('id');

        $item = Pengumuman::findOrFail($id);
        $this->assertSame($this->admin->id, $item->user_id);
        $this->assertStringNotContainsString('<script', $item->content);
        Storage::disk('public')->assertExists($first = $item->attachment);

        $this->patch("/api/pengumuman/{$id}", ['lampiran' => UploadedFile::fake()->image('poster.png')], ['Accept' => 'application/json'])->assertOk();
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second = $item->fresh()->attachment);

        $this->patchJson("/api/pengumuman/{$id}", ['judul' => 'Libur Idul Adha 1447 H', 'hapus_lampiran' => true])->assertOk()
            ->assertJsonPath('judul', 'Libur Idul Adha 1447 H')->assertJsonPath('lampiran', null)->assertJsonPath('kategori', 'umum');
        Storage::disk('public')->assertMissing($second);

        $this->patchJson("/api/pengumuman/{$id}", ['tanggal_tayang' => today()->addDays(10)->toDateString()])
            ->assertJsonValidationErrors(['tanggal_tayang' => 'tidak boleh setelah tanggal berakhir']);
        $this->patchJson("/api/pengumuman/{$id}", ['tanggal_berakhir' => today()->subDay()->toDateString()])
            ->assertJsonValidationErrors(['tanggal_berakhir' => 'tidak boleh sebelum tanggal tayang']);

        $this->deleteJson("/api/pengumuman/{$id}")->assertNoContent();
        $this->assertNull(Pengumuman::find($id));
        $this->getJson("/api/pengumuman/{$id}")->assertNotFound();
    }

    public function test_validation_messages(): void
    {
        $this->postJson('/api/pengumuman', [])->assertJsonValidationErrors(['judul', 'isi', 'tanggal_tayang']);
        $this->post('/api/pengumuman', [
            'judul' => 'X', 'isi' => 'Y', 'tanggal_tayang' => today()->toDateString(),
            'lampiran' => UploadedFile::fake()->create('virus.exe', 10), 'tautan' => 'javascript:alert(1)',
        ], ['Accept' => 'application/json'])->assertJsonValidationErrors(['lampiran' => 'PDF, Word, atau gambar', 'tautan']);
    }

    public function test_publish_draft_and_end_actions(): void
    {
        $item = $this->item(['is_active' => false, 'publish_date' => today()->addWeek()]);

        $this->postJson("/api/pengumuman/{$item->id}/tayangkan")->assertOk()
            ->assertJsonPath('message', 'Status pengumuman: Tayang.')
            ->assertJsonPath('data.tanggal_tayang', today()->toDateString());

        $this->postJson("/api/pengumuman/{$item->id}/akhiri")->assertOk()->assertJsonPath('data.status', 'berakhir');
        $this->postJson("/api/pengumuman/{$item->id}/akhiri")->assertUnprocessable();
        $this->postJson("/api/pengumuman/{$item->id}/draf")->assertOk()->assertJsonPath('data.status', 'draf');
        $this->postJson("/api/pengumuman/{$item->id}/arsipkan")->assertNotFound();
    }
}
