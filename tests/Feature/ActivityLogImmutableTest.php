<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** activity_log hanya dapat ditambah: tidak diubah, dihapus, atau dikosongkan. */
class ActivityLogImmutableTest extends TestCase
{
    use RefreshDatabase;

    private function entry(): Activity
    {
        return activity('audit')->withProperties(['ip' => '10.0.0.1'])->log('Mengubah pengaturan');
    }

    public function test_model_refuses_changes_and_deletes(): void
    {
        $entry = $this->entry();

        try {
            $entry->update(['description' => 'Diubah']);
            $this->fail('Update should be refused.');
        } catch (LogicException) {
        }

        $this->expectException(LogicException::class);
        $entry->delete();
    }

    public function test_database_refuses_changes_deletes_and_truncate(): void
    {
        $entry = $this->entry();

        foreach ([
            fn () => DB::table('activity_log')->where('id', $entry->id)->update(['description' => 'Diubah']),
            fn () => DB::table('activity_log')->where('id', $entry->id)->delete(),
            fn () => DB::statement('TRUNCATE activity_log'),
        ] as $change) {
            try {
                DB::transaction($change);
                $this->fail('The change should have been refused.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('tidak dapat diubah atau dihapus', $e->getMessage());
            }
        }

        $this->assertSame('Mengubah pengaturan', Activity::find($entry->id)->description);
    }

    public function test_new_entries_are_still_recorded(): void
    {
        $this->entry();
        $this->entry();

        $this->assertSame(2, Activity::count());
    }

    public function test_maintenance_session_can_lift_the_lock(): void
    {
        $entry = $this->entry();

        DB::transaction(function () use ($entry) {
            DB::statement("SET LOCAL app.allow_history_changes = 'on'");
            DB::table('activity_log')->where('id', $entry->id)->delete();
        });

        $this->assertNull(Activity::find($entry->id));
    }
}
