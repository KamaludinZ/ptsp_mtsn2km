<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorResource\Pages\EditVisitor;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** A checkout is the time with the status, whoever records it (counter, API, admin's edit, the guest). */
class VisitorCheckoutSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_10_28_210000_guard_visitor_checkout.php';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_the_status_follows_the_checkout_time_whoever_sets_it(): void
    {
        $visitor = Visitor::whereNull('check_out_time')->firstOrFail();

        $visitor->update(['check_out_time' => now()]);
        $this->assertSame('checked_out', $visitor->fresh()->status);

        $visitor->update(['check_out_time' => null, 'status' => 'checked_out']); // the time wins
        $this->assertSame('active', $visitor->fresh()->status);
    }

    public function test_the_database_refuses_a_checkout_out_of_step(): void
    {
        $visitor = Visitor::whereNull('check_out_time')->firstOrFail();

        foreach ([
            ['status' => 'checked_out'],
            ['check_out_time' => now()],
            ['check_out_time' => $visitor->check_in_time->copy()->subMinute(), 'status' => 'checked_out'],
        ] as $change) {
            try {
                DB::transaction(fn () => DB::table('visitors')->where('id', $visitor->id)->update($change));
                $this->fail('Accepted ' . json_encode($change));
            } catch (QueryException $e) {
                $this->assertStringContainsString('visitors_checkout_', $e->getMessage());
            }
        }
    }

    public function test_an_admin_s_correction_cannot_check_out_before_the_check_in(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $visitor = Visitor::whereNull('check_out_time')->whereNotNull('phone')->firstOrFail();

        Livewire::test(EditVisitor::class, ['record' => $visitor->getRouteKey()])
            ->fillForm(['check_out_time' => $visitor->check_in_time->copy()->subHour()])
            ->call('save')
            ->assertHasFormErrors(['check_out_time' => 'Waktu check-out tidak boleh sebelum waktu check-in.']);

        Livewire::test(EditVisitor::class, ['record' => $visitor->getRouteKey()])
            ->fillForm(['check_out_time' => $visitor->check_in_time->copy()->addHour()])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('checked_out', $visitor->fresh()->status); // the status came with the time
    }

    public function test_the_migration_puts_earlier_records_in_step(): void
    {
        $migration = require database_path('migrations/' . self::MIGRATION);
        $migration->down();

        $in = now()->subHours(3);
        $row = fn (string $name, array $checkout) => ['name' => $name, 'purpose' => 'Komite', 'check_in_time' => $in, 'created_at' => $in, 'updated_at' => $in, ...$checkout];
        DB::table('visitors')->insert([
            $row('Waktu tanpa status', ['check_out_time' => $in->copy()->addHour(), 'status' => 'active']),
            $row('Status tanpa waktu', ['check_out_time' => null, 'status' => 'checked_out']),
        ]);
        DB::table('visitors')->insert($row('Keluar sebelum masuk', ['check_out_time' => $in->copy()->subHour(), 'status' => 'checked_out']));
        $migration->up();

        $rows = DB::table('visitors')->whereIn('name', ['Waktu tanpa status', 'Status tanpa waktu', 'Keluar sebelum masuk'])->get()->keyBy('name');
        $this->assertSame('checked_out', $rows['Waktu tanpa status']->status);
        $this->assertSame(['active', null], [$rows['Status tanpa waktu']->status, $rows['Status tanpa waktu']->check_out_time]);
        $this->assertSame($rows['Keluar sebelum masuk']->check_in_time, $rows['Keluar sebelum masuk']->check_out_time);
    }
}
