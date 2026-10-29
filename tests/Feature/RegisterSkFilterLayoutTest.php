<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/** Saringan and table of the SK register: the criteria of the downloaded report. */
class RegisterSkFilterLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    private function datePicker(Testable $page, string $name): DatePicker
    {
        return collect($page->instance()->getTableFiltersForm()->getFlatComponents())
            ->first(fn ($component) => $component instanceof DatePicker && $component->getName() === $name);
    }

    public function test_the_saringan_sit_above_the_register(): void
    {
        $table = Livewire::test(ListSuratKeputusan::class)->instance()->getTable();

        $this->assertSame(FiltersLayout::AboveContentCollapsible, $table->getFiltersLayout());
        $this->assertSame(['default' => 1, 'md' => 2, 'xl' => 4], $table->getFiltersFormColumns());
    }

    public function test_the_date_range_cannot_end_before_it_starts(): void
    {
        $year = now()->year;
        $page = Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tanggal', ['from' => "{$year}-02-01", 'until' => "{$year}-04-20"]);

        $this->assertSame("{$year}-02-01", $this->datePicker($page, 'until')->getMinDate());
        $this->assertSame("{$year}-04-20", $this->datePicker($page, 'from')->getMaxDate());
        // Seeded SK are dated 23 days apart from 1 January: 3 on 16 Feb, 4 on 11 Mar, 5 on 3 Apr, 6 on 26 Apr.
        $page->assertCanSeeTableRecords(SuratKeputusan::whereIn('nomor_urut', [3, 4, 5])->get())
            ->assertCountTableRecords(3);
    }

    public function test_nothing_matching_offers_to_clear_search_and_saringan(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('tidak ada perihal seperti ini')
            ->assertSee('Tidak ada SK yang cocok')
            ->assertTableActionVisible('resetFilters')
            ->callTableAction('resetFilters')
            ->assertSet('tableSearch', '')
            ->assertCountTableRecords(6);

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tahun', now()->year - 1)
            ->assertSee('Tidak ada SK yang cocok')
            ->callTableAction('resetFilters')
            ->assertSee('Register tahun ' . now()->year)
            ->assertCountTableRecords(6);
    }

    public function test_an_empty_year_says_no_number_was_given_yet(): void
    {
        SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::query()->forceDelete());

        Livewire::test(ListSuratKeputusan::class)
            ->assertSee('Belum ada nomor SK tahun ' . now()->year)
            ->assertTableActionHidden('resetFilters')
            ->removeTableFilter('tahun')
            ->assertSee('Belum ada nomor SK');
    }
}
