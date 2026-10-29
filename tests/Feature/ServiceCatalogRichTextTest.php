<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceRichTextSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Kartu layanan /services with rich text content, as TinyMCE stores it, for every kind of visitor. */
class ServiceCatalogRichTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->seed(ServiceRichTextSeeder::class);
    }

    public function test_the_demo_services_hold_rich_text_and_an_older_json_list(): void
    {
        $this->seed(ServiceRichTextSeeder::class); // seeding again changes nothing

        $services = Service::whereIn('code', ServiceRichTextSeeder::SERVICES)->get()->keyBy('code');
        $this->assertCount(3, $services);
        $this->assertTrue($services->every(fn (Service $service) => $service->is_active && $service->user_types_allowed === null && $service->categories()->exists()));
        $this->assertStringContainsString('<table', $services['SKP-021']->mechanism);
        $this->assertStringContainsString('<h3>', $services['PSP-022']->requirements);
        $this->assertIsArray(json_decode($services['LDK-023']->requirements, true));
    }

    public function test_every_visitor_sees_the_cards_with_their_rich_text_details(): void
    {
        $visitors = [
            'anonim' => null,
            'pemohon' => User::where('email', 'ahmad.rizki.parent@gmail.com')->firstOrFail(),
            'petugas' => User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail(),
        ];

        foreach ($visitors as $who => $user) {
            $this->app['auth']->forgetGuards();
            $response = $user ? $this->actingAs($user)->get('/services') : $this->get('/services');

            $response->assertOk()
                // Card: name, and the description as plain text.
                ->assertSee('Surat Keterangan Pindah (Mutasi) Siswa')
                ->assertSee('Penerbitan surat keterangan pindah bagi siswa yang pindah ke madrasah')
                // Detail: the rich text as safe HTML.
                ->assertSee('<li>Surat permohonan pindah dari orang tua/wali <em>bermeterai Rp10.000</em>.</li>', false)
                ->assertSee('<th>Tahap</th>', false)
                ->assertSee('<h3>Instansi</h3>', false)
                ->assertSee('<blockquote>', false)
                // The older JSON list becomes list items.
                ->assertSee('Ijazah asli untuk dicocokkan');

            $this->assertStringNotContainsString('&lt;table', $response->getContent(), "{$who}: rich text shown as escaped tags");
        }
    }

    public function test_text_pasted_with_another_site_s_classes_and_ids_stays_visible(): void
    {
        // As pasted from a Bootstrap site: its classes hid the section ("isi detail menghilang").
        $service = Service::where('code', 'PSP-022')->firstOrFail();
        $service->forceFill([
            'requirements' => '<div class="tab-content"><div class="tab-pane fade" id="syarat"><ol><li>Fotokopi ijazah legalisir</li></ol></div></div>',
            'mechanism' => '<div class="collapse d-none" id="collapse' . $service->id . '"><p class="MsoNormal">Ajukan di loket.</p></div>',
        ])->saveQuietly();

        foreach (['/services', route('onlineportal.service.detail', $service->slug)] as $page) {
            $html = $this->get($page)->assertOk()
                ->assertSee('<div><div><ol><li>Fotokopi ijazah legalisir</li></ol></div></div>', false)
                ->assertSee('<div><p>Ajukan di loket.</p></div>', false)
                ->getContent();

            $this->assertStringNotContainsString('tab-pane', $html, $page);
            $this->assertStringNotContainsString('MsoNormal', $html, $page);
            $this->assertStringNotContainsString('id="syarat"', $html, $page);
        }

        // The card's own accordion keeps the only element with its id.
        $this->assertSame(1, substr_count($this->get('/services')->getContent(), 'id="collapse' . $service->id . '"'));
    }

    public function test_a_visitor_not_signed_in_logs_in_and_comes_back_to_the_form(): void
    {
        $online = Service::where('code', 'SKP-021')->firstOrFail(); // hybrid: served online too
        $apply = route('onlineportal.service.apply', $online->slug);

        $html = $this->get('/services')->assertOk()
            ->assertSee('href="' . $apply . '" class="btn btn-outline-primary w-100"', false)
            ->getContent();
        $this->assertStringNotContainsString('?redirect=', $html);

        // The apply address sends the visitor to log in, remembering the form.
        $this->get($apply)->assertRedirect('/portal/ajukan?layanan=' . $online->slug);
        $this->get('/portal/ajukan?layanan=' . $online->slug)->assertRedirect(route('login'));

        $applicant = User::where('email', 'ahmad.rizki.parent@gmail.com')->firstOrFail();
        $applicant->forceFill(['password' => bcrypt('rahasia-uji')])->save();
        $this->withSession(['captcha_value' => 'ABCDE'])
            ->post('/login', ['email' => $applicant->email, 'password' => 'rahasia-uji', 'captcha' => 'ABCDE'])
            ->assertRedirect(url('/portal/ajukan?layanan=' . $online->slug));
    }

    public function test_an_offline_service_is_requested_at_the_counter(): void
    {
        $offline = Service::where('code', 'PSP-022')->firstOrFail();
        $online = Service::where('code', 'LDK-023')->firstOrFail();

        $this->get('/services')->assertOk()->assertSee('Layanan ini diajukan langsung di loket PTSP pada jam layanan.');

        $this->get(route('onlineportal.service.detail', $offline->slug))->assertOk()
            ->assertSee('Diajukan langsung di loket PTSP pada jam layanan.')
            ->assertDontSee('Ajukan Layanan Online');
        $this->get(route('onlineportal.service.detail', $online->slug))->assertOk()
            ->assertSee('Ajukan Layanan Online')
            ->assertSee('Anda akan diminta masuk atau mendaftar terlebih dahulu.');
    }

    public function test_signed_in_visitors_get_the_way_to_apply_that_is_theirs(): void
    {
        $service = Service::where('code', 'LDK-023')->firstOrFail(); // online
        $apply = route('onlineportal.service.apply', $service->slug);
        $detail = route('onlineportal.service.detail', $service->slug);
        $counter = \App\Filament\Pages\FrontDesk\RegisterService::getUrl(panel: 'admin');

        // Pemohon: the apply button, on the card and on the service page.
        $this->actingAs(User::where('email', 'ahmad.rizki.parent@gmail.com')->firstOrFail());
        $this->get('/services')->assertOk()->assertSee('href="' . $apply . '" class="btn w-100 btn-primary"', false)->assertDontSee('data-staff-note', false);
        $this->get($detail)->assertOk()->assertSee('Ajukan Layanan Online')->assertDontSee('data-staff-note', false);

        // Back office: the portal is not theirs, and the counter is not either.
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->get('/services')->assertOk()->assertSee('data-staff-note', false)->assertDontSee('href="' . $apply . '"', false)->assertDontSee('Daftarkan di Loket');
        $this->get($detail)->assertOk()->assertSee('data-staff-note', false)->assertDontSee('Ajukan Layanan Online');

        // Loket: registers the walk-in applicant instead.
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->get('/services')->assertOk()->assertSee('href="' . $counter . '"', false);
        $this->get($detail)->assertOk()->assertSee('Daftarkan di Loket');

        // Staff who are applicants too (a waka who is a guru) still apply in the portal
        // (for services open to guru; ServiceCatalogVisitorTest covers the others).
        $teacher = User::factory()->create(['user_type' => 'guru']);
        $teacher->assignRole('waka_kurikulum', 'guru');
        $this->actingAs($teacher);
        $this->get('/services')->assertOk()->assertSee('href="' . $apply . '" class="btn w-100 btn-primary"', false);
        $this->get($detail)->assertOk()->assertSee('Ajukan Layanan Online')->assertDontSee('data-staff-note', false);
    }

    public function test_bootstrap_s_collapse_is_left_to_bootstrap(): void
    {
        // Tailwind's `collapse` utility and daisyUI's collapse component restyled
        // (and in older browsers hid) the accordions of /services and the FAQ.
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@source not inline("collapse");', $css);
        $this->assertMatchesRegularExpression('/@plugin "daisyui" \{[^}]*exclude: collapse, modal;/', $css);
    }

    public function test_a_card_the_filter_moves_up_is_shown_at_once(): void
    {
        // AOS keeps each card hidden until the place it had on load is scrolled to.
        $this->get('/services')->assertOk()
            ->assertSee("if (visible) {\n            item.classList.add('aos-animate');", false);
    }

    public function test_a_cut_description_ends_on_a_word_and_opens_in_full_with_the_detail(): void
    {
        $this->assertSame('Penerbitan surat...', \App\Support\RichText::plain('<p>Penerbitan surat keterangan pindah</p>', 20));

        $html = $this->get('/services')->assertOk()
            ->assertSee('ke madrasah atau sekolah lain, lengkap...')
            ->assertDontSee('lengkap den...')
            ->getContent();

        // The grid card cuts it at 100 characters: its detail has the whole description.
        $card = substr($html, strpos($html, 'Surat Keterangan Pindah (Mutasi) Siswa'));
        $card = substr($card, 0, strpos($card, 'Sistem & Prosedur'));
        $this->assertStringContainsString('Tentang Layanan', $card);
        $this->assertStringContainsString('<strong>pindah ke madrasah atau sekolah lain</strong>, lengkap dengan rapor dan dokumen pendukung.', $card);

        // A description that fits is not repeated.
        $short = substr($html, strpos($html, 'Legalisir Dokumen Kelulusan'));
        $this->assertStringNotContainsString('Tentang Layanan', substr($short, 0, strpos($short, 'Sistem & Prosedur')));
    }

    public function test_the_grid_and_the_list_show_the_same_detail_component(): void
    {
        $html = $this->get('/services')->assertOk()->getContent();
        $shown = Service::where('is_active', true)->availableFor('umum')->count();

        // One detail per card in the grid (an accordion) and one in the list (two columns).
        $this->assertSame($shown, substr_count($html, 'class="service-detail accordion-body"'));
        $this->assertSame($shown, substr_count($html, '<div class="service-detail">'));
        $this->assertSame(2, substr_count($html, '<th>Tahap</th>'));
        $this->assertSame($shown, substr_count($html, 'Informasi Biaya'));
    }
}
