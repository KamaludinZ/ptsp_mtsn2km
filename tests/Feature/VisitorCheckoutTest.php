<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Checkout from the public visitor book (/visitor-book). */
class VisitorCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_the_demo_visitor_book_has_every_kind_of_guest_the_checkout_meets(): void
    {
        $today = Visitor::whereDate('check_in_time', today())->get()->keyBy('name');

        // On site with a phone (both kinds), already gone, and on site without a phone.
        $this->assertSame(['active', 'active', 'active'], [$today['Rina Kartika']->status, $today['Budi Hartono']->status, $today['Sari Utami']->status]);
        $this->assertStringStartsWith('Pemohon Layanan: ', $today['Budi Hartono']->purpose);
        $this->assertNull($today['Sari Utami']->created_by); // from the public form
        $this->assertNotNull($today['Agus Salim']->check_out_time);
        $this->assertSame('checked_out', $today['Agus Salim']->status);
        $this->assertNull($today['Dewi Lestari']->phone);
        $this->assertNull($today['Dewi Lestari']->check_out_time);

        $yesterday = Visitor::where('name', 'Joko Prasetyo')->firstOrFail();
        $this->assertTrue($yesterday->check_in_time->isYesterday());
        $this->assertNull($yesterday->check_out_time);

        // Earlier visits: never leaving before arriving, the status as the times say.
        Visitor::whereDate('check_in_time', '<', today())->get()->each(function (Visitor $visitor) {
            $this->assertSame($visitor->check_out_time ? 'checked_out' : 'active', $visitor->status, $visitor->name);
            $this->assertTrue(! $visitor->check_out_time || $visitor->check_out_time->gte($visitor->check_in_time), $visitor->name);
        });

        $this->get(route('public.visitor.book'))->assertOk()
            ->assertSee('Selesai')
            ->assertSee('Aktif')
            ->assertDontSee('Rina Kartika'); // obscured on the public list
    }

    public function test_only_today_s_guests_on_site_with_a_phone_get_a_checkout_button(): void
    {
        $guests = Visitor::get()->keyBy('name');

        $this->assertTrue($guests['Rina Kartika']->canSelfCheckOut());
        $this->assertTrue($guests['Budi Hartono']->canSelfCheckOut());
        $this->assertFalse($guests['Agus Salim']->canSelfCheckOut()); // already gone
        $this->assertFalse($guests['Dewi Lestari']->canSelfCheckOut()); // no phone to check
        $this->assertFalse($guests['Joko Prasetyo']->canSelfCheckOut()); // yesterday

        $html = $this->get(route('public.visitor.book'))->assertOk()
            ->assertSee('id="checkoutModal"', false)
            ->assertSee('aria-label="Checkout ' . $guests['Rina Kartika']->publicName() . '"', false)
            ->assertSee('data-checkout-phone="' . $guests['Rina Kartika']->maskedPhone() . '"', false)
            ->assertDontSee('081234500001') // the phone number never reaches the page
            ->getContent();

        $onSiteWithPhone = Visitor::whereDate('check_in_time', today())->whereNull('check_out_time')->whereNotNull('phone')->count();
        $this->assertSame($onSiteWithPhone, substr_count($html, 'data-bs-target="#checkoutModal"'));
        $this->assertSame(1, substr_count($html, 'data-checkout-at-counter')); // Dewi

        // Each button carries where its guest checks out.
        $this->assertStringContainsString('data-checkout-url="' . url('/visitor-book/' . $guests['Rina Kartika']->id . '/checkout') . '"', $html);

        // Yesterday's list: Joko is checked out at the counter.
        $this->get(route('public.visitor.book', ['date' => today()->subDay()->toDateString()]))->assertOk()
            ->assertDontSee('data-bs-target="#checkoutModal"', false)
            ->assertSee('data-checkout-at-counter', false);
    }

    private function checkOut(Visitor $visitor, array $form)
    {
        return $this->post(route('public.visitor.checkout', $visitor), $form);
    }

    public function test_the_guest_checks_out_with_the_phone_given_on_arrival(): void
    {
        $rina = Visitor::where('name', 'Rina Kartika')->firstOrFail(); // registered as 081234500001
        $hendra = Visitor::where('name', 'Hendra Wijaya')->firstOrFail();
        $changed = fn (Visitor $visitor, array $before) => array_keys(array_diff_assoc($visitor->fresh()->getAttributes(), $before));
        [$rinaBefore, $hendraBefore] = [$rina->getAttributes(), $hendra->getAttributes()];
        $this->travel(1)->minutes(); // past the second the guests were seeded in

        $this->checkOut($rina, ['phone' => '+62 812-3450-0001', 'visitor' => $rina->id])
            ->assertRedirect(route('public.visitor.book'))
            ->assertSessionHas('success', 'Checkout berhasil. Terima kasih atas kunjungan Anda, ' . $rina->publicName() . '.');
        app(\App\Services\FrontDeskService::class)->checkOut($hendra, \App\Models\User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        // Recorded as the counter's checkout is: the same columns change, nothing else.
        $this->assertSame(['check_out_time', 'status', 'updated_at'], $changed($rina, $rinaBefore));
        $this->assertSame($changed($hendra, $hendraBefore), $changed($rina, $rinaBefore));
        $this->assertSame('checked_out', $rina->fresh()->status);

        // The history tells them apart.
        $this->assertSame(['mandiri', null, '127.0.0.1'], [$rina->checkouts->first()->method, $rina->checkouts->first()->checked_out_by, $rina->checkouts->first()->ip_address]);
        $this->assertSame('petugas', $hendra->checkouts->first()->method);
    }

    public function test_a_wrong_or_missing_phone_comes_back_to_the_modal(): void
    {
        $rina = Visitor::where('name', 'Rina Kartika')->firstOrFail();

        $this->checkOut($rina, ['phone' => '081299999999', 'visitor' => $rina->id])
            ->assertSessionHasErrors(['phone' => 'Nomor telepon tidak cocok dengan data kunjungan. Periksa kembali nomornya, atau checkout di loket.'], null, 'checkout')
            ->assertSessionHasInput('visitor', (string) $rina->id);
        $this->checkOut($rina, ['phone' => ''])->assertSessionHasErrors(['phone' => 'Nomor telepon/HP wajib diisi.'], null, 'checkout');

        $this->assertNull($rina->fresh()->check_out_time);
        $this->assertFalse($rina->checkouts()->exists());
    }

    public function test_only_a_guest_the_button_is_for_can_check_out(): void
    {
        $refusals = [
            'Agus Salim' => 'Tamu ini sudah checkout.',
            'Dewi Lestari' => 'Checkout tamu ini dilakukan oleh petugas di loket.', // no phone
            'Joko Prasetyo' => 'Checkout tamu ini dilakukan oleh petugas di loket.', // yesterday
        ];
        foreach ($refusals as $name => $message) {
            $guest = Visitor::where('name', $name)->firstOrFail();
            $before = $guest->check_out_time;
            $this->checkOut($guest, ['phone' => (string) $guest->phone])->assertSessionHasErrors(['checkout' => $message]);
            $this->assertEquals($before, $guest->fresh()->check_out_time, $name);
        }
    }

    public function test_guessing_a_phone_is_locked_after_a_few_tries(): void
    {
        $rina = Visitor::where('name', 'Rina Kartika')->firstOrFail();

        foreach (range(1, \App\Http\Controllers\PublicController::CHECKOUT_ATTEMPTS) as $try) {
            $this->withServerVariables(['REMOTE_ADDR' => "203.0.113.{$try}"]) // from anywhere
                ->checkOut($rina, ['phone' => '0812000000' . $try]);
        }
        // Even the right number waits now.
        $this->checkOut($rina, ['phone' => '081234500001'])
            ->assertSessionHasErrors(['phone' => 'Terlalu banyak percobaan. Coba lagi dalam 15 menit atau checkout di loket.'], null, 'checkout');
        $this->assertNull($rina->fresh()->check_out_time);

        $this->travel(\App\Http\Controllers\PublicController::CHECKOUT_LOCK_MINUTES + 1)->minutes();
        $this->checkOut($rina, ['phone' => '081234500001'])->assertSessionHas('success');
    }

    public function test_bots_and_floods_check_no_one_out(): void
    {
        $rina = Visitor::where('name', 'Rina Kartika')->firstOrFail();

        $this->checkOut($rina, ['phone' => '081234500001', 'website' => 'http://spam.test'])->assertSessionHas('success');
        $this->assertNull($rina->fresh()->check_out_time);

        $statuses = collect(range(1, 12))->map(fn () => $this->checkOut($rina, ['phone' => '0812'])->status());
        $this->assertContains(429, $statuses->all());
    }

    public function test_the_modal_asks_for_the_phone_and_reopens_on_a_refusal(): void
    {
        $rina = Visitor::where('name', 'Rina Kartika')->firstOrFail();

        $this->get(route('public.visitor.book'))->assertOk()
            ->assertSee('data-checkout-form', false)
            ->assertSee('name="phone" id="checkout_phone"', false)
            ->assertSee('name="website"', false) // the honeypot, as on the other forms
            ->assertSee('data-checkout-submit', false)
            // Checked in the browser before sending: characters, then 8–15 digits.
            ->assertSee('Nomor telepon hanya boleh berisi angka')
            ->assertSee('Nomor telepon terlalu pendek.')
            ->assertSee('Nomor telepon terlalu panjang.')
            ->assertDontSee('data-checkout-reopen', false);

        // As a refused checkout redirects back: the "checkout" error bag and the guest it was for.
        $errors = (new \Illuminate\Support\ViewErrorBag)->put('checkout', new \Illuminate\Support\MessageBag(['phone' => 'Nomor telepon tidak cocok dengan data kunjungan.']));
        $this->withSession(['errors' => $errors, '_old_input' => ['visitor' => (string) $rina->id]])
            ->get(route('public.visitor.book'))->assertOk()
            ->assertSee('data-checkout-reopen="' . $rina->id . '"', false)
            ->assertSee('Nomor telepon tidak cocok dengan data kunjungan.')
            ->assertSee('is-invalid', false);
    }
}
