<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SurveyArchives;
use App\Models\SurveyArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Halaman arsip rekap kuartalan SKM & SPAK. */
class SurveyArchivesPageTest extends TestCase
{
    use RefreshDatabase;

    private function archive(string $type, string $quarter, float $average, float $percentage): SurveyArchive
    {
        return SurveyArchive::create([
            'type' => $type, 'quarter' => $quarter, 'year' => (int) substr($quarter, 0, 4), 'period' => null,
            'data' => ['responses_count' => 12, 'start_date' => '2026-07-01', 'end_date' => '2026-09-30'],
            'calculated_values' => ['total_respondents' => 12, 'average' => $average, 'percentage' => $percentage,
                'scores' => [['question' => 'Kemudahan prosedur', 'average_score' => 3.5, 'total_responses' => 12]]],
            'is_quarterly_archive' => true,
        ]);
    }

    private function supervisor(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('supervisor', 'web'));

        return $user;
    }

    public function test_supervisors_see_quarterly_archives_with_scores(): void
    {
        $skm = $this->archive('skm', '2026-Q3', 3.52, 84.0);
        $spak = $this->archive('spak', '2026-Q2', 3.80, 93.33);
        SurveyArchive::create(['type' => 'skm', 'period' => '2026-09', 'year' => 2026, 'data' => [], 'calculated_values' => [], 'is_quarterly_archive' => false]);
        $this->actingAs($this->supervisor());

        $this->get(SurveyArchives::getUrl())->assertOk()->assertSee('Arsip Rekap Kuartalan Survei');

        Livewire::test(SurveyArchives::class)
            ->assertCanSeeTableRecords([$skm, $spak], inOrder: true)
            ->assertCountTableRecords(2)
            ->assertTableColumnStateSet('percentage', '84,00%', $skm)
            ->assertTableColumnStateSet('respondents', 12, $skm)
            ->filterTable('type', 'spak')
            ->assertCanNotSeeTableRecords([$skm])
            ->resetTableFilters()
            ->mountTableAction('view', $skm)
            ->assertSee('Kemudahan prosedur');
    }

    public function test_front_desk_cannot_open_it(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('front_desk', 'web'));

        $this->actingAs($user)->get(SurveyArchives::getUrl())->assertForbidden();
    }
}
