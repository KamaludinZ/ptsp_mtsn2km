<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Log & Audit for the SK register: every create, change and delete leaves a trail. */
class RegisterSkAuditTest extends TestCase
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

    private function lastLog(): Activity
    {
        return Activity::inLog('surat_keputusan')->latest('id')->firstOrFail();
    }

    public function test_asking_for_a_number_is_logged_with_the_sk_data_and_active_role(): void
    {
        Sanctum::actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $before = Activity::inLog('surat_keputusan')->count();

        $id = $this->postJson('/api/penomoran-sk', [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia pentas seni',
            'nama_peminta' => 'Waka Kesiswaan',
        ])->assertCreated()->json('id');

        $this->assertSame($before + 1, Activity::inLog('surat_keputusan')->count());
        $log = $this->lastLog();
        $sk = SuratKeputusan::findOrFail($id);
        $this->assertSame('created', $log->event);
        $this->assertSame('Meminta nomor SK ' . $sk->nomor_sk, $log->description);
        $this->assertTrue($log->subject->is($sk));
        $this->assertSame($officer->id, $log->causer_id);
        $this->assertSame('back_office', $log->properties['peran_aktif']);
        $this->assertSame('Panitia pentas seni', $log->properties['attributes']['perihal']);
        $this->assertSame(now()->toDateString(), $log->properties['attributes']['tanggal_surat']);
    }

    public function test_changes_log_only_the_fields_that_changed(): void
    {
        Sanctum::actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = SuratKeputusan::where('nomor_urut', 3)->firstOrFail();
        $before = Activity::inLog('surat_keputusan')->count();

        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Panitia PPDB (revisi)', 'nama_peminta' => $sk->nama_peminta])->assertOk();

        $log = $this->lastLog();
        $this->assertSame('updated', $log->event);
        $this->assertSame('Mengubah data SK ' . $sk->nomor_sk, $log->description);
        $this->assertSame($officer->id, $log->causer_id);
        $this->assertSame(['perihal' => 'Panitia PPDB (revisi)'], $log->properties['attributes']);
        $this->assertSame(['perihal' => $sk->perihal], $log->properties['old']);

        // Saving the same values again leaves no empty entry.
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Panitia PPDB (revisi)'])->assertOk();
        $this->assertSame($before + 1, Activity::inLog('surat_keputusan')->count());
    }

    public function test_changes_from_the_panel_are_logged_too(): void
    {
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['keterangan' => 'Ditandatangani kepala madrasah.'])
            ->assertHasNoTableActionErrors();

        $log = $this->lastLog();
        $this->assertSame('updated', $log->event);
        $this->assertSame($officer->id, $log->causer_id);
        $this->assertSame('Ditandatangani kepala madrasah.', $log->properties['attributes']['keterangan']);
        $this->assertArrayNotHasKey('perihal', $log->properties['attributes']);
    }

    public function test_deleting_keeps_the_reason_and_the_old_data(): void
    {
        Sanctum::actingAs($admin = $this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $sk = SuratKeputusan::where('nomor_urut', 4)->firstOrFail();

        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => '  Ganda   dengan SK lain. '])->assertNoContent();

        $log = $this->lastLog();
        $this->assertSame('deleted', $log->event);
        $this->assertSame('Menghapus SK ' . $sk->nomor_sk . ' — alasan: Ganda dengan SK lain.', $log->description);
        $this->assertSame($admin->id, $log->causer_id);
        $this->assertSame('admin', $log->properties['peran_aktif']);
        $this->assertSame($sk->nomor_sk, $log->properties['old']['nomor_sk']);
        $this->assertSame($sk->perihal, $log->properties['old']['perihal']);
        $this->assertSame($sk->tanggal_surat->toDateString(), $log->properties['old']['tanggal_surat']);
    }

    public function test_refused_actions_leave_no_trail(): void
    {
        $sk = SuratKeputusan::firstOrFail();
        $before = Activity::inLog('surat_keputusan')->count();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Diubah loket'])->assertForbidden();

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->postJson('/api/penomoran-sk', ['perihal' => 'Tanpa data lain'])->assertUnprocessable();
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Bukan admin.'])->assertForbidden();

        $this->assertSame($before, Activity::inLog('surat_keputusan')->count());
    }

    public function test_sk_trail_shows_in_log_and_audit_and_cannot_be_changed(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $sk = SuratKeputusan::where('nomor_urut', 6)->firstOrFail();
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Dibatalkan rapat pimpinan.'])->assertNoContent();

        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->set('auditSearch', 'surat_keputusan')
            ->assertSee('Menghapus SK ' . $sk->nomor_sk);

        $this->expectException(\LogicException::class);
        $this->lastLog()->update(['description' => 'Diubah']);
    }
}
