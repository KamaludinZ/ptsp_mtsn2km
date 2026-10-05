<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Buku tamu publik: the guest form (photo with local preview, validation in Indonesian). */
class VisitorPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('public');
        $this->seed();
    }

    private function guest(array $overrides = []): array
    {
        return ['name' => 'Bu Ani', 'phone' => '0814', 'purpose' => 'Waka Humas', ...$overrides];
    }

    public function test_form_offers_a_photo_with_local_preview(): void
    {
        $this->get(route('public.visitor.book'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="photo"', false)
            ->assertSee('visitor_photo_preview', false);
    }

    public function test_guest_photo_is_stored_with_the_visit(): void
    {
        $this->post(route('public.visitor.submit'), $this->guest(['photo' => UploadedFile::fake()->image('wajah.jpg', 400, 400)]))
            ->assertSessionHasNoErrors();

        $visitor = Visitor::latest('id')->firstOrFail();
        $this->assertStringStartsWith('visitor-photos/', $visitor->photo_path);
        Storage::disk('public')->assertExists($visitor->photo_path);
    }

    public function test_photo_is_optional_and_must_be_a_small_image(): void
    {
        $this->post(route('public.visitor.submit'), $this->guest())->assertSessionHasNoErrors();
        $this->assertNull(Visitor::latest('id')->firstOrFail()->photo_path);

        $this->post(route('public.visitor.submit'), $this->guest(['photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('photo');
        $this->post(route('public.visitor.submit'), $this->guest(['photo' => UploadedFile::fake()->image('besar.jpg')->size(3000)]))
            ->assertSessionHasErrors('photo');
    }

    public function test_invalid_form_explains_in_indonesian_and_keeps_the_input(): void
    {
        $this->from(route('public.visitor.book'))
            ->followingRedirects()
            ->post(route('public.visitor.submit'), ['name' => 'Bu Ani', 'phone' => '', 'email' => 'bukan-email', 'purpose' => 'Waka Humas'])
            ->assertOk()
            ->assertSee('Nomor telepon/HP wajib diisi.')
            ->assertSee('Email harus berupa alamat email yang valid.')
            ->assertSee('value="Bu Ani"', false)
            ->assertSee('is-invalid', false);

        $this->post(route('public.applicant.submit'), ['name' => '', 'phone' => '0812', 'target_service' => 'Lainnya'])
            ->assertSessionHasErrors(['name' => 'Nama lengkap wajib diisi.', 'target_service_other' => 'Tuliskan layanan yang Anda tuju.']);
    }

    public function test_guest_sees_a_summary_of_the_registration(): void
    {
        $this->followingRedirects()
            ->post(route('public.visitor.submit'), $this->guest(['institution' => 'SDN 1 Malang', 'obscure_name' => 'on']))
            ->assertOk()
            ->assertSee('Tamu berhasil didaftarkan!')
            ->assertSeeInOrder(['Nama', 'Bu A*i', 'Instansi', 'SDN 1 Malang', 'Tujuan', 'Waka Humas', 'Waktu check-in', 'Nama disamarkan'])
            ->assertDontSee('Bu Ani');
    }

    public function test_public_api_returns_the_same_masked_summary(): void
    {
        $this->postJson('/api/publik/buku-tamu', $this->guest(['obscure_name' => true]))
            ->assertCreated()
            ->assertJsonPath('ringkasan.0', ['label' => 'Nama', 'nilai' => 'Bu A*i'])
            ->assertJsonPath('ringkasan.3.nilai', 'Waka Humas');
    }

    public function test_staff_get_the_printable_guest_card(): void
    {
        $this->post(route('public.visitor.submit'), $this->guest());
        $visitor = Visitor::latest('id')->firstOrFail();

        \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/buku-tamu/' . $visitor->id)->assertOk()->assertJsonPath('kartu_tamu', route('visitors.print', $visitor));
    }

    public function test_bots_filling_the_honeypot_are_not_stored(): void
    {
        $before = Visitor::count();

        $this->post(route('public.visitor.submit'), $this->guest(['website' => 'http://spam.example']))
            ->assertRedirect(route('public.visitor.book'))->assertSessionHas('success');
        $this->post(route('public.applicant.submit'), ['name' => 'Bot', 'phone' => '1', 'target_service' => 'Lainnya', 'target_service_other' => 'x', 'website' => 'spam'])
            ->assertSessionHas('success');

        $this->assertSame($before, Visitor::count());
        $this->get(route('public.visitor.book'))->assertSee('name="website"', false);
    }

    public function test_public_guest_book_is_rate_limited(): void
    {
        $limited = collect(range(1, 30))->map(fn () => $this->postJson('/api/publik/buku-tamu', $this->guest())->status())->contains(429);

        $this->assertTrue($limited);
    }
}
