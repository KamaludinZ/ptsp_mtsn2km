<?php

namespace Tests\Feature;

use App\Models\ImpersonationSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

/** Tabel log sesi ganti akun: hanya ditambah dan ditutup sekali. */
class ImpersonationSessionSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function open(): ImpersonationSession
    {
        $admin = User::factory()->create(['name' => 'Admin Satu']);
        $target = User::factory()->create(['name' => 'Budi']);

        return ImpersonationSession::create([
            'admin_id' => $admin->id, 'admin_name' => $admin->name,
            'target_id' => $target->id, 'target_name' => $target->name, 'target_role' => 'umum',
            'reason' => 'Meninjau keluhan unggah berkas', 'ip_address' => '10.0.0.5', 'started_at' => now(),
        ]);
    }

    public function test_a_session_is_closed_once(): void
    {
        $session = $this->open();
        $this->assertTrue($session->isOpen());
        $this->assertSame(1, ImpersonationSession::open()->count());

        $session->update(['ended_at' => now(), 'end_reason' => 'selesai']);
        $this->assertFalse($session->fresh()->isOpen());

        $this->expectException(LogicException::class);
        $session->fresh()->update(['ended_at' => now()->addHour()]);
    }

    public function test_the_database_refuses_edits_and_deletes(): void
    {
        $session = $this->open();

        foreach ([
            fn () => DB::table('impersonation_sessions')->where('id', $session->id)->update(['reason' => 'diubah']),
            fn () => DB::table('impersonation_sessions')->where('id', $session->id)->update(['ended_at' => now(), 'target_name' => 'Orang lain']),
            fn () => DB::table('impersonation_sessions')->where('id', $session->id)->delete(),
        ] as $change) {
            try {
                DB::transaction($change);
                $this->fail('The change should have been refused.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('tidak dapat diubah atau dihapus', $e->getMessage());
            }
        }

        // Closing it is the one change allowed, and only once.
        DB::table('impersonation_sessions')->where('id', $session->id)->update(['ended_at' => now(), 'end_reason' => 'selesai']);
        $this->expectException(QueryException::class);
        DB::table('impersonation_sessions')->where('id', $session->id)->update(['end_reason' => 'keluar']);
    }

    public function test_log_survives_deleted_accounts(): void
    {
        $session = $this->open();
        User::whereKey($session->target_id)->forceDelete();

        $session = $session->fresh();
        $this->assertNull($session->target_id);
        $this->assertSame('Budi', $session->target_name);
    }

    public function test_model_refuses_deletes(): void
    {
        $this->expectException(LogicException::class);
        $this->open()->delete();
    }
}
