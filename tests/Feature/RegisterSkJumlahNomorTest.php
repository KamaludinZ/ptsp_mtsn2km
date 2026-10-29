<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use App\Support\RegisterSkAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Jumlah nomor yang dibutuhkan (/cp/penomoran-sk): several SK numbers with
 * the same data at once. Up to 1000 for an administrator recording numbers
 * already used by hand (its own daily quota), up to 10 for other officers.
 */
class RegisterSkJumlahNomorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $acting, string ...$roles): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($roles ?: [$acting]);
        $user->forceFill(['active_role_id' => Role::findByName($acting, 'web')->id])->save();

        return $user->fresh();
    }

    private function request(int $jumlah, array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(), 'jumlah' => $jumlah, 'konfirmasi_jumlah' => true,
            'jenis_sk' => Persuratan::JENIS_SK[2], 'perihal' => 'Nomor manual buku agenda', 'nama_peminta' => 'Tata Usaha', ...$override,
        ];
    }

    private function next(): int
    {
        return SuratKeputusanService::nextNumber(now()->year);
    }

    public function test_an_administrator_records_a_thousand_numbers_used_by_hand_at_once(): void
    {
        $this->actingAs($this->staff('admin'));
        $year = now()->year;
        $first = $this->next();

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData(['jumlah' => 1000])
            ->assertSeeHtml('data-sk-preview="' . $first . '–' . ($first + 999) . ' TAHUN ' . $year . '"')
            ->assertSee('Ya, ambil 1000 nomor SK sekaligus.')
            ->assertDontSee('Scan SK (opsional)')
            ->callMountedAction()
            ->assertHasActionErrors(['konfirmasi_jumlah' => 'accepted']);
        $this->assertSame($first, $this->next());

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(1000))
            ->assertHasNoActionErrors()
            ->assertNotified('1000 nomor SK diberikan');

        $taken = SuratKeputusan::where('tahun', $year)->where('nomor_urut', '>=', $first)->orderBy('nomor_urut')->get();
        $this->assertSame(range($first, $first + 999), $taken->pluck('nomor_urut')->all());
        $this->assertSame(['Nomor manual buku agenda'], $taken->pluck('perihal')->unique()->values()->all());
        $this->assertSame(['admin'], $taken->pluck('peran_pembuat')->unique()->values()->all());
        $this->assertSame($first + 999, DB::table('surat_keputusan_sequences')->where('tahun', $year)->value('last_number'));
        $this->assertSame($first + 1000, $this->next());

        // Each number has its own entry in Log & Audit, naming the block.
        $logged = Activity::inLog('surat_keputusan')->where('event', 'created')->whereIn('subject_id', $taken->modelKeys());
        $this->assertSame(1000, $logged->count());
        $this->assertSame($first . '–' . ($first + 999) . ' TAHUN ' . $year, $logged->first()->properties['kumpulan']);
    }

    public function test_the_administrator_s_block_has_its_own_daily_quota(): void
    {
        $admin = $this->staff('admin');
        $this->actingAs($admin);
        $service = app(SuratKeputusanService::class);

        $service->reserveBlock(now(), $admin, $this->request(1), 990);
        $before = $this->next();

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(11))
            ->assertNotified('Nomor SK tidak dapat diberikan');
        $this->assertSame($before, $this->next());
        $this->assertSame(1, Activity::inLog('surat_keputusan')->where('event', 'limited')->count());

        // Ten more fit the quota, and ordinary requests keep their own limits.
        $this->assertCount(10, $service->reserveBlock(now(), $admin, $this->request(1), 10));
        Livewire::test(ListSuratKeputusan::class)->callAction('reserve', $this->request(1))->assertHasNoActionErrors();
        $this->assertSame($before + 11, $this->next());
    }

    public function test_more_than_a_thousand_or_more_than_the_year_has_left_is_refused(): void
    {
        $this->actingAs($this->staff('admin'));

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(1001))
            ->assertHasActionErrors(['jumlah' => 'max']);
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(0))
            ->assertHasActionErrors(['jumlah' => 'min']);

        DB::table('surat_keputusan_sequences')->updateOrInsert(['tahun' => now()->year], ['last_number' => 9995]);
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData(['jumlah' => 5])
            ->assertSeeHtml('data-sk-preview="kurang"')
            ->assertSee('Sisa nomor SK tahun ' . now()->year . ' tinggal 4; kurangi jumlahnya.');
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(5))
            ->assertNotified('Nomor SK tidak dapat diberikan');
        $this->assertSame(9996, $this->next());
    }

    public function test_other_officers_take_up_to_ten_within_the_usual_limits(): void
    {
        $officer = $this->staff('back_office');
        $this->actingAs($officer);
        $first = $this->next();

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(11))
            ->assertHasActionErrors(['jumlah' => 'max']);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(6))
            ->assertHasNoActionErrors()
            ->assertNotified('6 nomor SK diberikan');
        $this->assertSame(range($first, $first + 5), SuratKeputusan::where('nomor_urut', '>=', $first)->orderBy('nomor_urut')->pluck('nomor_urut')->all());

        // Ten a minute: five more would make eleven.
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->request(5))
            ->assertNotified('Nomor SK tidak dapat diberikan');
        $this->assertSame($first + 6, $this->next());

        $this->expectException(TicketActionException::class);
        app(SuratKeputusanService::class)->reserveBlock(now(), $officer, $this->request(1), 20);
    }

    public function test_an_administrator_working_in_another_role_is_an_officer_here(): void
    {
        $user = $this->staff('back_office', 'admin', 'back_office');

        $this->assertFalse(RegisterSkAccess::canRecordBlock($user));
        $this->assertSame(SuratKeputusanService::MAX_PER_MINUTE, RegisterSkAccess::maxPerRequest($user));

        $user->forceFill(['active_role_id' => Role::findByName('admin', 'web')->id])->save();
        $this->assertTrue(RegisterSkAccess::canRecordBlock($user->fresh()));
        $this->assertSame(1000, RegisterSkAccess::maxPerRequest($user->fresh()));
    }

    public function test_the_registration_page_takes_several_and_opens_the_register(): void
    {
        $this->actingAs($this->staff('admin'));
        $first = $this->next();

        Livewire::test(RegisterSkNumber::class)
            ->assertSee('Jumlah nomor yang dibutuhkan')
            ->fillForm($this->request(25))
            ->assertDontSee('Scan SK (opsional)')
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified('25 nomor SK diberikan')
            ->assertRedirect(SuratKeputusanResource::getUrl('index'));
        $this->assertSame($first + 25, $this->next());

        // "Ambil & minta lagi" starts the next request at one number.
        Livewire::test(RegisterSkNumber::class)
            ->fillForm($this->request(2))
            ->call('submitAnother')
            ->assertHasNoFormErrors()
            ->assertFormSet(['jumlah' => 1]);
    }

    public function test_a_ticket_s_sk_is_one_number(): void
    {
        // Even for an administrator: Proses tiket asks for that ticket's one SK.
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $ticket = Ticket::query()->orderBy('id')->firstOrFail();

        Livewire::withQueryParams(['tiket' => $ticket->id])
            ->test(RegisterSkNumber::class)
            ->assertSee($ticket->ticket_number)
            ->assertDontSee('Jumlah nomor yang dibutuhkan');
    }
}
