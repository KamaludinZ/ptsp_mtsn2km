<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every Filament widget must render against a real (PostgreSQL) schema:
 * wrong column/status names or missing models only surface when the
 * widget's Livewire request runs, not when the dashboard page loads.
 */
class AdminDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_admin_widgets_render(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Ticket::factory()->count(3)->create();
        Complaint::factory()->count(3)->create();
        Complaint::factory()->create(['complaint_type' => 'whistleblowing']);

        $this->actingAs($admin);

        $widgets = collect(File::files(app_path('Filament/Widgets')))
            ->map(fn ($file) => 'App\\Filament\\Widgets\\' . $file->getFilenameWithoutExtension())
            ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, Widget::class)
                && ! (new \ReflectionClass($class))->isAbstract());

        $this->assertNotEmpty($widgets);

        foreach ($widgets as $widget) {
            Livewire::test($widget)->assertOk();

            // Widgets swallow query errors; on PostgreSQL that leaves the
            // transaction aborted, so probe it to pinpoint the culprit.
            try {
                \Illuminate\Support\Facades\DB::select('select 1');
            } catch (\Throwable $e) {
                $this->fail("{$widget} ran a failing query");
            }
        }
    }
}
