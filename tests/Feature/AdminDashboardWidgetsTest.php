<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use App\Support\RoleAccess;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every Filament widget must render against a real (PostgreSQL) schema, as
 * a user allowed to see it: widgets load lazily, so wrong column/status
 * names only surface when the widget's own Livewire request runs, not when
 * the dashboard page loads.
 */
class AdminDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['admin', 'kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'];

    /** @return array<int, class-string<Widget>> */
    private function widgets(string $directory, string $namespace): array
    {
        return collect(File::allFiles(app_path($directory)))
            ->map(fn ($file) => $namespace . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
            ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, Widget::class)
                && ! (new \ReflectionClass($class))->isAbstract())
            ->values()
            ->all();
    }

    private function seedData(User $applicant): void
    {
        Service::factory()->count(2)->create();
        Ticket::factory()->count(3)->create();
        Ticket::factory()->create(['user_id' => $applicant->id, 'status' => 'completed', 'ready_for_pickup' => true, 'actual_completion_date' => now()]);
        Ticket::factory()->create(['status' => 'verified', 'approval_required' => true]);
        Complaint::factory()->count(3)->create();
        Complaint::factory()->create(['complaint_type' => 'whistleblowing']);
        Visitor::factory()->count(2)->create(['check_in_time' => now()]);
    }

    private function assertRenders(string $widget): void
    {
        Livewire::test($widget)->assertOk();

        // Widgets swallow query errors; on PostgreSQL that leaves the
        // transaction aborted, so probe it to pinpoint the culprit.
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $this->fail("{$widget} ran a failing query");
        }
    }

    public function test_every_staff_widget_renders_for_the_roles_that_see_it(): void
    {
        RoleAccess::sync();
        $applicant = User::factory()->create(['user_type' => 'umum']);
        $this->seedData($applicant);

        $widgets = $this->widgets('Filament/Widgets', 'App\\Filament\\Widgets\\');
        $this->assertNotEmpty($widgets);

        $rendered = [];
        foreach (self::ROLES as $role) {
            $user = User::factory()->create(['user_type' => 'pegawai']);
            $user->assignRole(Role::findOrCreate($role));
            $this->actingAs($user);

            foreach ($widgets as $widget) {
                if ($widget::canView()) {
                    $this->assertRenders($widget);
                    $rendered[$widget] = true;
                }
            }
        }

        // Every widget is visible to at least one role.
        $this->assertSame([], array_values(array_diff($widgets, array_keys($rendered))));
    }

    public function test_every_portal_widget_renders_for_an_applicant(): void
    {
        $applicant = User::factory()->create(['user_type' => 'umum']);
        $this->seedData($applicant);
        Filament::setCurrentPanel(Filament::getPanel('portal'));
        $this->actingAs($applicant);

        $widgets = $this->widgets('Filament/Portal/Widgets', 'App\\Filament\\Portal\\Widgets\\');
        $this->assertNotEmpty($widgets);

        foreach ($widgets as $widget) {
            $this->assertTrue($widget::canView(), "{$widget} should be shown to this applicant");
            $this->assertRenders($widget);
        }
    }
}
