<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorResource\Pages\CreateVisitor;
use App\Filament\Resources\VisitorResource\Pages\EditVisitor;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** /cp/visitors/create: the counter picks the kind of guest first, as the public visitor book does. */
class VisitorRegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_the_form_starts_as_a_visiting_guest(): void
    {
        Livewire::test(CreateVisitor::class)
            ->assertFormSet(['guest_type' => 'kunjungan'])
            ->assertSee('Jenis tamu')
            ->assertSee('Tamu Kunjungan')
            ->assertSee('Pemohon Layanan Offline')
            ->assertFormFieldIsVisible('person_to_meet_id')
            ->assertFormFieldIsVisible('purpose')
            ->assertDontSee('Permohonan layanan');
    }

    public function test_choosing_an_applicant_swaps_the_visit_for_the_service_section(): void
    {
        Livewire::test(CreateVisitor::class)
            ->fillForm(['guest_type' => 'pemohon'])
            ->assertSee('Permohonan layanan')
            ->assertFormFieldIsHidden('person_to_meet_id')
            ->assertFormFieldIsHidden('purpose')
            ->assertFormFieldIsVisible('name')
            ->assertFormFieldIsVisible('phone')
            ->fillForm(['guest_type' => 'kunjungan'])
            ->assertFormFieldIsVisible('person_to_meet_id');
    }

    public function test_buku_tamu_opens_the_form_set_to_each_kind_of_guest(): void
    {
        $this->get(\App\Filament\Resources\VisitorResource::getUrl('index'))->assertOk()
            ->assertSee('Tamu kunjungan')
            ->assertSee('Pemohon layanan offline')
            ->assertSee(\App\Filament\Resources\VisitorResource::getUrl('create', ['jenis' => 'pemohon']), false);

        Livewire::withQueryParams(['jenis' => 'pemohon'])->test(CreateVisitor::class)
            ->assertFormSet(['guest_type' => 'pemohon'])
            ->assertSee('Permohonan layanan');
        Livewire::withQueryParams(['jenis' => 'lain'])->test(CreateVisitor::class)->assertFormSet(['guest_type' => 'kunjungan']);
    }

    public function test_switching_the_kind_back_keeps_what_was_typed(): void
    {
        Livewire::test(CreateVisitor::class)
            ->fillForm(['name' => 'Tamu Uji', 'purpose' => 'Komite'])
            ->fillForm(['guest_type' => 'pemohon'])
            ->fillForm(['guest_type' => 'kunjungan'])
            ->assertFormSet(['name' => 'Tamu Uji', 'purpose' => 'Komite']);
    }

    public function test_a_visiting_guest_is_still_registered_as_before(): void
    {
        $host = User::factory()->create(['user_type' => 'guru', 'name' => 'Bu Guru']);

        Livewire::test(CreateVisitor::class)
            ->fillForm(['guest_type' => 'kunjungan', 'name' => 'Tamu Uji', 'phone' => '081234567890', 'purpose' => 'Wali Kelas', 'person_to_meet_id' => $host->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Bu Guru', Visitor::where('name', 'Tamu Uji')->firstOrFail()->person_to_meet);
    }

    public function test_a_visiting_guest_is_asked_what_the_public_form_asks(): void
    {
        $purposes = \App\Models\VisitorMaster::options('tujuan');

        $form = Livewire::test(CreateVisitor::class)
            ->assertFormSet(['is_obscured' => true])
            ->assertFormFieldIsVisible('institution_category')
            ->assertFormFieldIsVisible('notes')
            ->assertFormFieldIsHidden('purpose_other')
            ->fillForm(['purpose' => 'Lainnya'])
            ->assertFormFieldIsVisible('purpose_other');

        // Required as on the public form: name, phone, a purpose from the list (Lainnya needs its text).
        $form->fillForm(['name' => '', 'phone' => '', 'purpose' => 'Lainnya', 'purpose_other' => ''])->call('create')
            ->assertHasFormErrors(['name' => 'required', 'phone' => 'required', 'purpose_other' => 'required']);
        $form->fillForm(['purpose' => 'Bukan dari daftar'])->call('create')->assertHasFormErrors(['purpose']);

        $this->assertContains('Lainnya', $purposes);
        $this->assertSame(['Instansi', 'Perusahaan', 'lainnya'], array_keys(Visitor::INSTITUTION_CATEGORIES));
    }

    public function test_an_applicant_is_asked_what_the_public_applicant_form_asks(): void
    {
        $form = Livewire::withQueryParams(['jenis' => 'pemohon'])->test(CreateVisitor::class)
            ->assertFormSet(['is_obscured' => true])
            ->assertFormFieldIsVisible('applicant_type')
            ->assertFormFieldIsVisible('target_service')
            ->assertFormFieldIsVisible('notes')
            ->assertFormFieldIsHidden('photo_path')
            // The institution only for one coming on behalf of an institution.
            ->assertFormFieldIsHidden('institution')
            ->assertFormFieldIsHidden('institution_category')
            ->fillForm(['applicant_type' => 'instansi_perusahaan'])
            ->assertFormFieldIsVisible('institution')
            ->assertFormFieldIsVisible('institution_category')
            ->assertFormFieldIsHidden('target_service_other')
            ->fillForm(['target_service' => 'Lainnya'])
            ->assertFormFieldIsVisible('target_service_other');

        $form->fillForm(['name' => 'Pemohon Uji', 'phone' => '081234567890', 'target_service' => 'Lainnya', 'target_service_other' => ''])->call('create')
            ->assertHasFormErrors(['target_service_other' => 'required']);
        $form->fillForm(['target_service' => 'Layanan yang tidak ada'])->call('create')->assertHasFormErrors(['target_service']);
        $form->fillForm(['target_service' => '', 'applicant_type' => 'tamu'])->call('create')
            ->assertHasFormErrors(['target_service' => 'required', 'applicant_type']);

        // Only active services are offered.
        $inactive = \App\Models\Service::where('is_active', true)->firstOrFail();
        $inactive->update(['is_active' => false]);
        $form->fillForm(['applicant_type' => 'umum', 'target_service' => $inactive->name])->call('create')->assertHasFormErrors(['target_service']);
    }

    /** What the form hands over for saving: only the fields shown for that kind of guest. */
    private function state(array $fill, array $query = []): array
    {
        return Livewire::withQueryParams($query)->test(CreateVisitor::class)->fillForm($fill)->instance()->form->getState();
    }

    public function test_hidden_fields_are_neither_checked_nor_handed_over(): void
    {
        $person = ['name' => 'Tamu Uji', 'phone' => '081234567890', 'institution' => 'Dinas', 'institution_category' => 'pemerintah'];

        $visit = $this->state([...$person, 'purpose' => 'Komite', 'purpose_other' => 'sisa isian', 'target_service' => 'sisa', 'notes' => 'Rapat komite']);
        $this->assertSame('kunjungan', $visit['guest_type']);
        $this->assertSame(['Dinas', 'pemerintah', 'Komite', 'Rapat komite', true], [$visit['institution'], $visit['institution_category'], $visit['purpose'], $visit['notes'], $visit['is_obscured']]);
        $this->assertArrayNotHasKey('purpose_other', $visit);
        $this->assertArrayNotHasKey('target_service', $visit);
        $this->assertArrayNotHasKey('applicant_type', $visit);

        // An applicant from the public: no institution, no visit; Lainnya brings its text.
        $service = \App\Models\Service::where('is_active', true)->value('name');
        $apply = $this->state([...$person, 'applicant_type' => 'umum', 'target_service' => 'Lainnya', 'target_service_other' => 'Surat rekomendasi lomba', 'purpose' => 'Komite'], ['jenis' => 'pemohon']);
        $this->assertSame(['pemohon', 'umum', 'Lainnya', 'Surat rekomendasi lomba'], [$apply['guest_type'], $apply['applicant_type'], $apply['target_service'], $apply['target_service_other']]);
        foreach (['institution', 'institution_category', 'purpose', 'person_to_meet_id', 'photo_path'] as $hidden) {
            $this->assertArrayNotHasKey($hidden, $apply, $hidden);
        }

        $forInstitution = $this->state([...$person, 'applicant_type' => 'instansi_perusahaan', 'target_service' => $service], ['jenis' => 'pemohon']);
        $this->assertSame(['Dinas', 'pemerintah', $service], [$forInstitution['institution'], $forInstitution['institution_category'], $forInstitution['target_service']]);
        $this->assertArrayNotHasKey('target_service_other', $forInstitution);
    }

    public function test_a_visit_for_a_ptsp_service_suggests_the_applicant_form(): void
    {
        Livewire::test(CreateVisitor::class)
            ->assertDontSee('pilih jenis Pemohon Layanan Offline')
            ->fillForm(['purpose' => 'Layanan PTSP'])
            ->assertSee('pilih jenis Pemohon Layanan Offline');
    }

    public function test_the_messages_are_the_visitor_book_s_in_indonesian(): void
    {
        $errors = Livewire::test(CreateVisitor::class)
            ->fillForm(['name' => '', 'phone' => '', 'email' => 'bukan-email', 'purpose' => 'Lainnya', 'purpose_other' => ''])
            ->call('create')
            ->errors();

        $this->assertSame('Nama lengkap wajib diisi.', $errors->first('data.name'));
        $this->assertSame('Email harus berupa alamat email yang valid.', $errors->first('data.email'));
        $this->assertSame('Tuliskan tujuan kunjungan tamu.', $errors->first('data.purpose_other'));

        $errors = Livewire::withQueryParams(['jenis' => 'pemohon'])->test(CreateVisitor::class)
            ->fillForm(['name' => 'Pemohon', 'phone' => '0812', 'target_service' => 'Lainnya'])
            ->call('create')
            ->errors();
        $this->assertSame('Tuliskan layanan yang dituju.', $errors->first('data.target_service_other'));
    }

    public function test_a_visiting_guest_is_saved_with_what_the_counter_typed(): void
    {
        Livewire::test(CreateVisitor::class)
            ->assertActionHasLabel('create', 'Daftarkan tamu')
            ->fillForm(['name' => 'Rina', 'phone' => '081234567890', 'institution_category' => 'pendidikan', 'institution' => 'SDN 1',
                'purpose' => 'Lainnya', 'purpose_other' => 'Studi banding perpustakaan', 'notes' => 'Rombongan 5 orang', 'is_obscured' => false])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Tamu berhasil didaftarkan');

        $visitor = Visitor::where('name', 'Rina')->firstOrFail();
        $this->assertSame(['SDN 1', 'pendidikan', 'Studi banding perpustakaan', 'Rombongan 5 orang', false, 'active'],
            [$visitor->institution, $visitor->institution_category, $visitor->purpose, $visitor->notes, (bool) $visitor->is_obscured, $visitor->status]);
        $this->assertSame(auth()->id(), $visitor->created_by);
    }

    public function test_an_applicant_is_saved_as_the_public_applicant_form_saves_one(): void
    {
        $service = \App\Models\Service::where('is_active', true)->orderBy('name')->firstOrFail();

        Livewire::withQueryParams(['jenis' => 'pemohon'])->test(CreateVisitor::class)
            ->assertActionHasLabel('create', 'Daftarkan pemohon')
            ->fillForm(['name' => 'Budi', 'phone' => '081298765432', 'applicant_type' => 'umum', 'target_service' => $service->name, 'notes' => 'Membawa berkas'])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Pemohon layanan berhasil didaftarkan');

        $visitor = Visitor::where('name', 'Budi')->firstOrFail();
        $this->assertSame(['Pemohon Layanan: ' . $service->name, 'Membawa berkas', true, null, 'active'],
            [$visitor->purpose, $visitor->notes, (bool) $visitor->is_obscured, $visitor->person_to_meet, $visitor->status]);
        $this->assertSame(['pemohon', 'umum', $service->id], [$visitor->guest_type, $visitor->applicant_type, $visitor->service_id]);
        $this->assertSame(auth()->id(), $visitor->created_by);

        Livewire::withQueryParams(['jenis' => 'pemohon'])->test(CreateVisitor::class)
            ->fillForm(['name' => 'Sari', 'phone' => '081211112222', 'applicant_type' => 'instansi_perusahaan', 'institution' => 'PT Maju',
                'target_service' => 'Lainnya', 'target_service_other' => 'Kerja sama magang'])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertSame(['PT Maju', 'Pemohon Layanan: Kerja sama magang'], [Visitor::where('name', 'Sari')->value('institution'), Visitor::where('name', 'Sari')->value('purpose')]);
    }

    public function test_editing_a_guest_has_no_type_to_pick(): void
    {
        $visitor = Visitor::firstOrFail();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail()); // admin corrects guests

        Livewire::test(EditVisitor::class, ['record' => $visitor->getRouteKey()])
            ->assertFormFieldIsHidden('guest_type')
            ->assertFormFieldIsVisible('purpose')
            ->assertDontSee('Permohonan layanan');
    }
}
