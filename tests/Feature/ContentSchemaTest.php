<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Pengumuman;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Konten publik: pengumuman date window and FAQ value checks. */
class ContentSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function pengumuman(array $values = []): Pengumuman
    {
        return Pengumuman::create($values + [
            'title' => 'Libur semester',
            'content' => '<p>Layanan tutup.</p>',
            'publish_date' => today(),
            'is_active' => true,
        ]);
    }

    /** @dataProvider invalidValues */
    public function test_invalid_values_are_rejected(string $table, array $values): void
    {
        $id = $table === 'faqs'
            ? Faq::create(['question' => 'Jam layanan?', 'answer' => 'Senin–Jumat.'])->id
            : $this->pengumuman()->id;

        $this->expectException(QueryException::class);
        DB::table($table)->where('id', $id)->update($values);
    }

    public static function invalidValues(): array
    {
        return [
            'end before publish' => ['pengumumen', ['end_date' => today()->subDay()]],
            'negative views' => ['pengumumen', ['view_count' => -1]],
            'negative order' => ['faqs', ['sort' => -1]],
            'blank question' => ['faqs', ['question' => '  ']],
            'blank answer' => ['faqs', ['answer' => '']],
        ];
    }

    public function test_one_day_announcements_are_allowed(): void
    {
        $item = $this->pengumuman(['end_date' => today()]);

        $this->assertTrue(Pengumuman::active()->whereKey($item->id)->exists());
    }

    public function test_existing_inverted_date_windows_are_repaired(): void
    {
        $migration = require database_path('migrations/2026_10_06_250000_harden_pengumuman_and_faqs_tables.php');
        $migration->down();

        $item = $this->pengumuman(['publish_date' => today(), 'end_date' => today()->subDays(3)]);
        $migration->up();

        $this->assertTrue($item->fresh()->end_date->isSameDay(today()));
        $this->assertTrue(Schema::hasIndex('pengumumen', ['is_active', 'publish_date', 'end_date']));
    }

    public function test_a_visit_is_counted_once_per_ip(): void
    {
        $item = $this->pengumuman();

        $this->assertTrue($item->recordView('10.0.0.1'));
        $this->assertFalse($item->recordView('10.0.0.1'));
        $this->assertTrue($item->recordView('10.0.0.2'));
        $this->assertFalse($item->recordView(null));

        $this->assertSame(2, $item->fresh()->view_count);
        $this->assertSame(2, $item->unique_view_count);

        $this->get(route('pengumuman.show', $item))->assertOk();
        $this->get(route('pengumuman.show', $item))->assertOk()->assertSee('Dilihat 3 kali');
    }

    public function test_relations_status_author_and_categories(): void
    {
        $writer = \App\Models\User::factory()->create(['name' => 'Siti TU']);
        $live = $this->pengumuman(['category' => 'akademik', 'user_id' => $writer->id]);
        $this->pengumuman(['category' => 'kegiatan', 'is_active' => false]);
        $named = $this->pengumuman(['author' => 'Humas']);

        $this->assertTrue($live->user->is($writer));
        $this->assertSame('Siti TU', $live->authorName());
        $this->assertSame('Humas', $named->authorName());
        $this->assertSame('Admin', $this->pengumuman()->authorName());
        $this->assertSame('tayang', $live->status());
        $this->assertSame(1, Pengumuman::withStatus('draf')->count());
        $this->assertSame(['akademik', 'kegiatan'], Pengumuman::categories());
        $this->assertSame(['akademik'], Pengumuman::categories(onlyPublished: true));

        $live->recordView('10.0.0.9');
        $this->assertTrue($live->views()->first()->pengumuman->is($live));

        Faq::create(['question' => 'Jam layanan?', 'answer' => 'Senin–Jumat.', 'category' => 'layanan', 'sort' => 4]);
        Faq::create(['question' => 'Lupa sandi?', 'answer' => 'Pakai tautan reset.']);
        $this->assertSame(['layanan'], Faq::categories());
        $this->assertSame(5, Faq::nextSort());
        $this->assertSame(['Lupa sandi?'], Faq::inCategory(null)->pluck('question')->all());
    }
}
