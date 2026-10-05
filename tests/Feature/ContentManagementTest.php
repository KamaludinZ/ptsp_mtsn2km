<?php

namespace Tests\Feature;

use App\Filament\Pages\Content\ContentManagement;
use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\User;
use App\Support\ContentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Manajemen Konten hub. */
class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        Pengumuman::query()->delete();
        Faq::query()->delete();
    }

    private function announcement(array $attributes): Pengumuman
    {
        return Pengumuman::create($attributes + ['title' => 'Info', 'content' => 'Isi', 'category' => 'umum', 'is_active' => true, 'publish_date' => '2026-10-01']);
    }

    public function test_status_follows_active_flag_and_dates(): void
    {
        $live = $this->announcement(['title' => 'Tayang']);
        $scheduled = $this->announcement(['title' => 'Nanti', 'publish_date' => '2026-10-20']);
        $draft = $this->announcement(['title' => 'Draf', 'is_active' => false]);
        $ended = $this->announcement(['title' => 'Lewat', 'end_date' => '2026-10-10']);

        $this->assertSame(['tayang', 'dijadwalkan', 'draf', 'berakhir'], array_map(fn ($a) => ContentStatus::of($a), [$live, $scheduled, $draft, $ended]));
        $this->assertSame(['tayang' => 1, 'dijadwalkan' => 1, 'draf' => 1, 'berakhir' => 1], ContentStatus::counts());
        // Same rule as the public site.
        $this->assertSame([$live->id], Pengumuman::active()->pluck('id')->all());
    }

    public function test_admin_sees_counts_ending_soon_and_quick_links(): void
    {
        $this->announcement(['title' => 'Libur Maulid', 'end_date' => '2026-10-18']);
        $this->announcement(['title' => 'Draf Rapat', 'is_active' => false]);
        Faq::forceCreate(['question' => 'Jam layanan?', 'answer' => 'Senin-Jumat', 'is_active' => true]);
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        $this->get(ContentManagement::getUrl())->assertOk()
            ->assertSee('Manajemen Konten')
            ->assertSee('Pengumuman tayang')
            ->assertSeeInOrder(['Segera berakhir', 'Libur Maulid', '18 Okt'])
            ->assertSee('Draf Rapat')
            ->assertSee('FAQ aktif')
            ->assertSee('Buat pengumuman');
    }

    public function test_only_admins_open_the_hub(): void
    {
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->get(ContentManagement::getUrl())->assertForbidden();
    }

    public function test_announcement_list_searches_filters_and_tabs_by_status(): void
    {
        $live = $this->announcement(['title' => 'Jadwal PPDB', 'content' => 'Pendaftaran dibuka', 'category' => 'akademik', 'view_count' => 12]);
        $scheduled = $this->announcement(['title' => 'Libur semester', 'publish_date' => '2026-10-25', 'category' => 'umum']);
        $draft = $this->announcement(['title' => 'Rapat komite', 'is_active' => false, 'category' => 'umum']);
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $list = \App\Filament\Resources\PengumumanResource\Pages\ListPengumumen::class;

        \Livewire\Livewire::test($list)
            ->assertCanSeeTableRecords([$live, $scheduled, $draft])
            ->assertTableColumnStateSet('status', 'dijadwalkan', $scheduled)
            ->searchTable('pendaftaran')
            ->assertCanSeeTableRecords([$live])
            ->assertCanNotSeeTableRecords([$scheduled, $draft]);

        \Livewire\Livewire::test($list)->set('activeTab', 'draf')->assertCanSeeTableRecords([$draft])->assertCanNotSeeTableRecords([$live]);
        \Livewire\Livewire::test($list)->filterTable('category', 'umum')->assertCanSeeTableRecords([$scheduled, $draft])->assertCanNotSeeTableRecords([$live]);
        \Livewire\Livewire::test($list)->filterTable('status', 'tayang')->assertCanSeeTableRecords([$live])->assertCanNotSeeTableRecords([$scheduled]);
        \Livewire\Livewire::test($list)->filterTable('publish_date', ['from' => '2026-10-20'])->assertCanSeeTableRecords([$scheduled])->assertCanNotSeeTableRecords([$live]);
    }

    public function test_form_saves_real_columns_and_previews_the_announcement(): void
    {
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($admin);
        $create = \App\Filament\Resources\PengumumanResource\Pages\CreatePengumuman::class;

        \Livewire\Livewire::test($create)
            ->fillForm(['title' => 'Libur Maulid', 'category' => 'Umum ', 'content' => '<p>Kantor tutup.</p><script>alert(1)</script>', 'publish_date' => '2026-10-20', 'is_active' => true])
            ->assertSee('Libur Maulid')
            ->assertSee('Dijadwalkan')
            ->assertSee('Baru tampil di situs mulai 20 Oktober 2026')
            ->assertDontSee('alert(1)')
            ->call('create')
            ->assertHasNoFormErrors();

        $item = Pengumuman::where('title', 'Libur Maulid')->firstOrFail();
        $this->assertSame(['umum', $admin->id, $admin->name], [$item->category, $item->user_id, $item->author]);

        \Livewire\Livewire::test($create)
            ->fillForm(['title' => 'Salah tanggal', 'content' => '<p>x</p>', 'publish_date' => '2026-10-20', 'end_date' => '2026-10-10'])
            ->call('create')
            ->assertHasFormErrors(['end_date']);
    }

    public function test_status_actions_and_delete_with_attachment(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('pengumuman-attachments/jadwal.pdf', 'x');
        $draft = $this->announcement(['title' => 'Draf', 'is_active' => false, 'publish_date' => '2026-09-01', 'end_date' => '2026-09-30']);
        $live = $this->announcement(['title' => 'Tayang', 'attachment' => 'pengumuman-attachments/jadwal.pdf']);
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $list = \App\Filament\Resources\PengumumanResource\Pages\ListPengumumen::class;

        \Livewire\Livewire::test($list)
            ->assertTableActionHidden('draft', $draft)
            ->callTableAction('publish', $draft)
            ->assertNotified('Pengumuman ditayangkan');
        $draft->refresh();
        $this->assertSame(['tayang', '2026-09-01', null], [ContentStatus::of($draft), $draft->publish_date->toDateString(), $draft->end_date]);

        \Livewire\Livewire::test($list)->callTableAction('end', $live)->assertNotified('Pengumuman diakhiri');
        $this->assertSame('berakhir', ContentStatus::of($live->fresh()));
        $this->assertSame([$draft->id], Pengumuman::active()->pluck('id')->all());

        \Livewire\Livewire::test($list)->callTableAction('draft', $draft);
        $this->assertSame('draf', ContentStatus::of($draft->fresh()));

        \Livewire\Livewire::test($list)->callTableAction('delete', $live);
        $this->assertModelMissing($live);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('pengumuman-attachments/jadwal.pdf');
    }

    public function test_bulk_publish_and_draft(): void
    {
        $a = $this->announcement(['title' => 'A', 'is_active' => false]);
        $b = $this->announcement(['title' => 'B', 'is_active' => false]);
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $list = \App\Filament\Resources\PengumumanResource\Pages\ListPengumumen::class;

        \Livewire\Livewire::test($list)->callTableBulkAction('publish', [$a, $b]);
        $this->assertSame(2, Pengumuman::active()->count());
        \Livewire\Livewire::test($list)->callTableBulkAction('draft', [$a, $b]);
        $this->assertSame(0, Pengumuman::active()->count());
    }

    public function test_public_list_shows_only_published_announcements_through_their_last_day(): void
    {
        $lastDay = $this->announcement(['title' => 'Hari terakhir', 'end_date' => '2026-10-15', 'category' => 'kegiatan']);
        $live = $this->announcement(['title' => 'Jadwal PPDB', 'category' => 'akademik', 'content' => 'Pendaftaran dibuka']);
        $this->announcement(['title' => 'Belum tayang', 'publish_date' => '2026-10-20']);
        $this->announcement(['title' => 'Draf rahasia', 'is_active' => false]);
        $this->announcement(['title' => 'Sudah lewat', 'end_date' => '2026-10-14']);
        auth()->logout();

        $this->get(route('pengumuman.index'))->assertOk()
            ->assertSee('Hari terakhir')
            ->assertSee('Jadwal PPDB')
            ->assertDontSee('Belum tayang')
            ->assertDontSee('Draf rahasia')
            ->assertDontSee('Sudah lewat')
            ->assertSee('<option value="kegiatan"', false)
            ->assertSee('<option value="akademik"', false);

        $this->get(route('pengumuman.index', ['search' => 'pendaftaran']))->assertSee('Jadwal PPDB')->assertDontSee('Hari terakhir');
        $this->get(route('pengumuman.index', ['category' => 'kegiatan']))->assertSee('Hari terakhir')->assertDontSee('Jadwal PPDB');

        $this->get(route('pengumuman.show', $lastDay))->assertOk();
        $this->get(route('pengumuman.show', $lastDay))->assertOk();
        $this->assertSame(1, $lastDay->fresh()->view_count); // one view per visitor
        $this->get(route('pengumuman.show', Pengumuman::where('title', 'Draf rahasia')->first()))->assertNotFound();
        $this->get(route('pengumuman.show', Pengumuman::where('title', 'Belum tayang')->first()))->assertNotFound();
    }

    public function test_faq_can_be_created_grouped_and_ordered(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        \Livewire\Livewire::test(\App\Filament\Resources\FaqResource\Pages\CreateFaq::class)
            ->fillForm(['question' => 'Berapa lama legalisir?', 'answer' => '<p>1-3 hari kerja.</p>', 'category' => 'Layanan ', 'sort' => 2, 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();
        $legalisir = Faq::where('question', 'Berapa lama legalisir?')->firstOrFail();
        $this->assertSame(['layanan', 2], [$legalisir->category, $legalisir->sort]);

        $first = Faq::create(['question' => 'Jam layanan?', 'answer' => 'Senin-Jumat', 'category' => 'layanan', 'sort' => 1, 'is_active' => true]);
        $complaint = Faq::create(['question' => 'Cara mengadu?', 'answer' => 'Lewat menu Pengaduan', 'category' => 'pengaduan', 'sort' => 1, 'is_active' => true]);
        $hidden = Faq::create(['question' => 'Rahasia?', 'answer' => 'x', 'is_active' => false]);

        // By group (alphabetical), then by hand-set order; hidden ones left out.
        $this->assertSame([$first->id, $legalisir->id, $complaint->id], Faq::published()->pluck('id')->all());

        $list = \App\Filament\Resources\FaqResource\Pages\ListFaqs::class;
        \Livewire\Livewire::test($list)->filterTable('category', 'pengaduan')->assertCanSeeTableRecords([$complaint])->assertCanNotSeeTableRecords([$first]);

        auth()->logout();
        $this->get(route('public.faq'))->assertOk()
            ->assertSeeInOrder(['Layanan', 'Jam layanan?', 'Berapa lama legalisir?', 'Pengaduan', 'Cara mengadu?'])
            ->assertDontSee('Rahasia?');
        $this->getJson('/api/publik/faq')->assertOk()->assertJsonPath('data.0.pertanyaan', 'Jam layanan?')->assertJsonPath('data.0.kelompok', 'Layanan');
    }

    public function test_faq_edit_delete_and_duplicate_questions(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $faq = Faq::create(['question' => 'Jam layanan?', 'answer' => '<p>Senin-Jumat</p>', 'is_active' => true, 'sort' => 1]);

        \Livewire\Livewire::test(\App\Filament\Resources\FaqResource\Pages\CreateFaq::class)
            ->fillForm(['question' => 'Jam layanan?', 'answer' => '<p>x</p>'])
            ->call('create')
            ->assertHasFormErrors(['question' => 'unique']);

        \Livewire\Livewire::test(\App\Filament\Resources\FaqResource\Pages\EditFaq::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['answer' => '<p>Senin-Jumat, 07.00-15.00</p>', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('FAQ disimpan')
            ->assertRedirect(\App\Filament\Resources\FaqResource::getUrl('index'));
        $this->assertFalse($faq->fresh()->is_active);

        \Livewire\Livewire::test(\App\Filament\Resources\FaqResource\Pages\EditFaq::class, ['record' => $faq->getRouteKey()])
            ->assertActionHidden('public')
            ->callAction('delete');
        $this->assertModelMissing($faq);
    }

    public function test_public_faq_page_has_group_links_anchors_and_faq_page_data(): void
    {
        $a = Faq::create(['question' => 'Jam layanan?', 'answer' => '<p>Senin-Jumat</p>', 'category' => 'layanan', 'sort' => 1, 'is_active' => true]);
        Faq::create(['question' => 'Biaya legalisir?', 'answer' => '<p>Gratis</p>', 'category' => 'layanan', 'sort' => 2, 'is_active' => true]);
        Faq::create(['question' => 'Cara mengadu?', 'answer' => '<p>Lewat menu Pengaduan</p>', 'category' => 'pengaduan', 'sort' => 1, 'is_active' => true]);
        auth()->logout();

        $html = $this->get(route('public.faq'))->assertOk()
            ->assertSee('href="#faq-layanan"', false)
            ->assertSee('Layanan <span class="text-muted">(2)</span>', false)
            ->assertSee('id="faq-' . $a->id . '"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->getContent();

        preg_match('/<script type="application\/ld\+json">\s*(.+?)\s*<\/script>/s', $html, $m);
        $data = json_decode($m[1], true);
        $this->assertSame('Jam layanan?', $data['mainEntity'][0]['name']);
        $this->assertSame('Senin-Jumat', $data['mainEntity'][0]['acceptedAnswer']['text']);

        // The home page shows the short list without the FAQ page extras.
        $this->get(route('home'))->assertOk()->assertDontSee('"@type":"FAQPage"', false);
    }
}
