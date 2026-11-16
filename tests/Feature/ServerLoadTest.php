<?php

namespace Tests\Feature;

use App\Enums\DispositionAction;
use App\Filament\Pages\Leadership\Approvals;
use App\Models\DispositionLog;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Support\ActiveRoles;
use App\Support\ServiceDisposition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Beban server: the checks that run on every panel page stay cheap however
 * many requests wait, and give the same answers as before.
 */
class ServerLoadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    /** Waiting tickets across every disposition mode, some already disposed by one leader. */
    /** @return array{0: int, 1: int} the joint-mode tickets disposed by the headmaster, and by the Kepala TU */
    private function waitingTickets(): array
    {
        $modes = ['kepsek_tu', 'kepsek', 'tu', null];
        foreach ($modes as $i => $mode) {
            $service = Service::factory()->create(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
            if ($mode) {
                ServiceDisposition::apply($service, $mode);
            }
            Ticket::factory()->count(3)->create(['service_id' => $service->id, 'status' => $i % 2 ? 'verified' : 'in_process', 'approval_required' => true, 'approval_status' => 'pending']);
        }

        // A service decided by one named person.
        $named = Service::factory()->create(['approval_required' => true, 'approval_roles' => ['kepala_tu'], 'approval_users' => [(string) User::where('email', 'pengawas@mtsn2malang.sch.id')->value('id')]]);
        Ticket::factory()->count(2)->create(['service_id' => $named->id, 'status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);

        // Not waiting: decided, not verified yet, or not needing a decision.
        Ticket::factory()->create(['service_id' => $named->id, 'status' => 'approved', 'approval_required' => true, 'approval_status' => 'approved']);
        Ticket::factory()->create(['service_id' => $named->id, 'status' => 'submitted', 'approval_required' => true, 'approval_status' => 'pending']);
        Ticket::factory()->create(['service_id' => $named->id, 'status' => 'verified', 'approval_required' => false, 'approval_status' => 'pending']);

        // "Kepala Madrasah + Kepala TU": the headmaster already disposed one, the Kepala TU another.
        $joint = Ticket::with('service')->where('approval_status', 'pending')->orderBy('id')->get()
            ->filter(fn (Ticket $ticket) => $ticket->service->disposition_mode === ServiceDisposition::JOINT_MODE)->values();
        foreach ([['kepala_sekolah', $joint[0]], ['kepala_tu', $joint[1]]] as [$role, $ticket]) {
            DispositionLog::create(['ticket_id' => $ticket->id, 'role' => $role, 'action' => DispositionAction::Disposisi, 'signature_model' => 'acknowledged_by']);
        }

        return [$joint[0]->id, $joint[1]->id];
    }

    /** What Ticket::approvableBy() returned before: the policy, ticket by ticket. */
    private function byPolicy(User $user): array
    {
        return Ticket::with('service')->awaitingApproval()->orderBy('created_at')->orderBy('id')->get()
            ->filter(fn (Ticket $ticket) => $user->can('approve', $ticket))
            ->pluck('id')->sort()->values()->all();
    }

    public function test_approvable_tickets_match_the_disposition_policy_for_every_role(): void
    {
        $this->waitingTickets();

        $emails = ['kepsek@mtsn2malang.sch.id', 'katu@mtsn2malang.sch.id', 'ptsp@mtsn2malang.sch.id', 'pengawas@mtsn2malang.sch.id', 'staff1@mtsn2malang.sch.id', 'loket1@mtsn2malang.sch.id', 'waka.kesiswaan@mtsn2malang.sch.id'];
        $checked = 0;
        foreach ($emails as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->actingAs($user);

            $expected = $this->byPolicy($user);
            $this->assertSame($expected, Ticket::approvableBy($user)->pluck('id')->sort()->values()->all(), "approvableBy() for {$email}");
            $this->assertSame(count($expected), Ticket::approvableQuery($user)->count(), "approvableQuery() for {$email}");
            $checked += count($expected);
        }

        $this->assertGreaterThan(0, $checked, 'Some user must have something to decide, or the test proves nothing.');
    }

    public function test_a_leader_who_disposed_in_joint_mode_no_longer_sees_it(): void
    {
        [$disposed, $byOther] = $this->waitingTickets();
        $headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();

        $this->actingAs($headmaster);
        $ids = Ticket::approvableBy($headmaster)->pluck('id');

        $this->assertNotContains($disposed, $ids);
        $this->assertContains($byOther, $ids); // still waits for the headmaster
    }

    public function test_the_approvals_badge_costs_the_same_however_many_requests_wait(): void
    {
        $headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($headmaster);
        $service = Service::factory()->create(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        ServiceDisposition::apply($service, 'kepsek_tu');

        $queries = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            Approvals::getNavigationBadge();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        Ticket::factory()->count(2)->create(['service_id' => $service->id, 'status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $few = $queries();
        Ticket::factory()->count(25)->create(['service_id' => $service->id, 'status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $many = $queries();

        $this->assertSame($few, $many, 'One query per waiting request is back (N+1).');
        $this->assertSame((string) Ticket::approvableQuery($headmaster)->count(), Approvals::getNavigationBadge());
    }

    public function test_active_role_permissions_match_spatie_without_a_query_per_check(): void
    {
        $permissions = Permission::where('guard_name', 'web')->pluck('name');

        foreach (User::STAFF_ROLES as $roleName) {
            $role = Role::findByName($roleName, 'web');
            $holder = tap(User::factory()->create())->assignRole($role);
            $this->assertSame($roleName, ActiveRoles::inContext($holder));
            foreach ($permissions as $permission) {
                $this->assertSame($role->hasPermissionTo($permission, 'web'), ActiveRoles::can($holder, $permission), "{$roleName} / {$permission}");
            }
        }

        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($staff);

        // Repeated checks are answered from the permission cache.
        ActiveRoles::can($staff, 'backoffice.access');
        DB::flushQueryLog();
        DB::enableQueryLog();
        foreach (range(1, 20) as $_) {
            ActiveRoles::can($staff, 'backoffice.access');
        }
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(1, count(array_filter(DB::getQueryLog(), fn ($q) => str_contains($q['query'], '"roles"') || str_contains($q['query'], 'role_has_permissions'))));

        // A permission that does not exist is still an error, as before.
        $this->expectException(PermissionDoesNotExist::class);
        ActiveRoles::can($staff, 'tidak.ada');
    }

    public function test_expired_cache_entries_are_pruned(): void
    {
        $dir = storage_path('framework/testing/cache-prune');
        File::deleteDirectory($dir);
        config(['cache.stores.file.path' => $dir]);
        $files = Cache::store('file');
        $files->put('lama', 'x', 1);
        $files->put('baru', 'x', 3600);
        $files->forever('selamanya', 'x');

        DB::table('cache')->insert([
            ['key' => 'lama', 'value' => serialize('x'), 'expiration' => now()->subMinute()->getTimestamp()],
            ['key' => 'baru', 'value' => serialize('x'), 'expiration' => now()->addHour()->getTimestamp()],
        ]);

        $this->travel(5)->seconds();
        $this->artisan('cache:prune-expired')->assertSuccessful();

        $left = collect(File::allFiles($dir))->map(fn ($f) => substr((string) file_get_contents($f->getPathname()), 10))->all();
        $this->assertCount(2, $left);
        $this->assertSame('x', $files->get('baru'));
        $this->assertSame('x', $files->get('selamanya'));
        $this->assertSame(['baru'], DB::table('cache')->whereIn('key', ['lama', 'baru'])->pluck('key')->all());

        File::deleteDirectory($dir);
    }

    public function test_app_cpu_is_measured_from_the_container_between_snapshots(): void
    {
        $monitor = new class extends SystemMonitorService
        {
            public int $usage = 0;

            protected function container(): ?array
            {
                return ['id' => 'test', 'memory_usage' => null, 'memory_limit' => null];
            }

            protected function containerCpuUsageMicros(): ?int
            {
                return $this->usage;
            }

            protected function containerCpuLimit(): ?float
            {
                return 2.0;
            }
        };
        Cache::forget(SystemMonitorService::APP_CPU_SAMPLE_KEY);

        $monitor->usage = 10_000_000;
        $this->assertNull($monitor->appCpuPercent(remember: true)); // first sample

        // 60 s later the container used 30 CPU-seconds of its 2 CPUs: 25%.
        Cache::put(SystemMonitorService::APP_CPU_SAMPLE_KEY, ['usage' => 10_000_000, 'at' => microtime(true) - 60]);
        $monitor->usage = 40_000_000;
        $this->assertEqualsWithDelta(25.0, $monitor->appCpuPercent(), 0.5);

        // A restarted container (counter went back) gives no reading rather than a wrong one.
        $monitor->usage = 5;
        $this->assertNull($monitor->appCpuPercent());
    }
}
