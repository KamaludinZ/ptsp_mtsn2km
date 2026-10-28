<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorResource\Pages\CreateVisitor;
use App\Models\Service;
use App\Models\User;
use App\Models\Visitor;
use App\Services\FrontDeskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** One guest, four ways in (counter form, counter API, public form, public API): the same entry. */
class VisitorRegistrationProcessingTest extends TestCase
{
    use RefreshDatabase;

    private const KEPT = ['guest_type', 'applicant_type', 'service_id', 'phone', 'email', 'institution', 'institution_category', 'purpose', 'notes', 'is_obscured', 'status'];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function counter(): User
    {
        return User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
    }

    /** Register $name four ways; returns the four entries' kept fields, keyed by the way. */
    private function everyWay(string $type, array $fields, array $formFields): array
    {
        Livewire::actingAs($this->counter())->withQueryParams(['jenis' => $type])->test(CreateVisitor::class)
            ->fillForm([...$formFields, 'name' => 'Panel'])->call('create')->assertHasNoFormErrors();
        auth()->logout();

        Sanctum::actingAs($this->counter());
        $this->postJson('/api/buku-tamu', [...$fields, 'jenis' => $type, 'name' => 'API loket'])->assertCreated();
        $this->app['auth']->forgetGuards();

        $this->post(route($type === 'pemohon' ? 'public.applicant.submit' : 'public.visitor.submit'), [...$fields, 'name' => 'Web publik', 'obscure_name' => 'on'])
            ->assertSessionHasNoErrors();
        $this->postJson('/api/publik/buku-tamu', [...$fields, 'jenis' => $type, 'name' => 'API publik'])->assertCreated();

        return Visitor::whereIn('name', ['Panel', 'API loket', 'Web publik', 'API publik'])->get()
            ->mapWithKeys(fn (Visitor $visitor) => [$visitor->name => array_intersect_key($visitor->getAttributes(), array_flip(self::KEPT))])
            ->all();
    }

    public function test_a_visiting_guest_is_the_same_entry_every_way(): void
    {
        $fields = ['phone' => '081200000020', 'email' => 'tamu@contoh.id', 'institution' => 'SDN 1', 'institution_category' => 'pendidikan',
            'purpose' => 'Lainnya', 'purpose_other' => 'Studi banding', 'notes' => 'Rombongan', 'obscure_name' => true];
        $form = [...array_diff_key($fields, ['obscure_name' => 1]), 'is_obscured' => true];

        $entries = $this->everyWay('kunjungan', $fields, $form);

        $this->assertCount(4, $entries);
        $this->assertCount(1, array_unique(array_map('serialize', $entries)), json_encode($entries));
        $this->assertSame(['kunjungan', 'Studi banding', true], [$entries['Panel']['guest_type'], $entries['Panel']['purpose'], (bool) $entries['Panel']['is_obscured']]);
    }

    public function test_an_applicant_is_the_same_entry_every_way(): void
    {
        $service = Service::where('is_active', true)->orderBy('name')->firstOrFail();
        $fields = ['phone' => '081200000021', 'applicant_type' => 'instansi_perusahaan', 'institution' => 'PT Maju', 'institution_category' => 'umkm',
            'target_service' => $service->name, 'notes' => 'Membawa berkas', 'obscure_name' => true];
        $form = [...array_diff_key($fields, ['obscure_name' => 1]), 'is_obscured' => true];

        $entries = $this->everyWay('pemohon', $fields, $form);

        $this->assertCount(4, $entries);
        $this->assertCount(1, array_unique(array_map('serialize', $entries)), json_encode($entries));
        $this->assertSame(['pemohon', 'instansi_perusahaan', $service->id, 'PT Maju'], [$entries['Panel']['guest_type'], $entries['Panel']['applicant_type'], $entries['Panel']['service_id'], $entries['Panel']['institution']]);
    }

    public function test_each_kind_keeps_only_what_its_form_shows(): void
    {
        $desk = app(FrontDeskService::class);
        $service = Service::where('is_active', true)->value('name');

        // An applicant not from an institution: an institution typed before the status changed is dropped.
        $person = $desk->register('pemohon', ['name' => 'A', 'phone' => '0812', 'applicant_type' => 'umum', 'institution' => 'PT Lama', 'institution_category' => 'umkm', 'target_service' => $service]);
        $this->assertSame([null, null], [$person->institution, $person->institution_category]);
        // No status given (it is optional): kept as typed.
        $this->assertSame('PT Maju', $desk->register('pemohon', ['name' => 'B', 'phone' => '0812', 'institution' => 'PT Maju', 'target_service' => $service])->institution);

        // A visiting guest without an institution: none, wherever registered (no more "-").
        $this->assertNull($desk->register('kunjungan', ['name' => 'C', 'purpose' => 'Komite', 'institution' => ''], $this->counter())->institution);

        // Samarkan nama, from each form's own field.
        foreach ([['is_obscured' => true], ['obscure_name' => 'on'], ['obscure_name' => '1'], ['obscure_name' => true]] as $choice) {
            $this->assertTrue($desk->register('kunjungan', ['name' => 'D', 'purpose' => 'Komite', ...$choice])->is_obscured, json_encode($choice));
        }
        $this->assertFalse($desk->register('kunjungan', ['name' => 'E', 'purpose' => 'Komite', 'is_obscured' => false, 'obscure_name' => 'on'])->is_obscured);
        $this->assertFalse($desk->register('kunjungan', ['name' => 'F', 'purpose' => 'Komite'])->is_obscured);
    }
}
