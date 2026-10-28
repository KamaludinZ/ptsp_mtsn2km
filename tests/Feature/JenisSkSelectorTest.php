<?php

namespace Tests\Feature;

use App\Filament\Forms\PersuratanFields;
use App\Models\PersuratanMaster;
use App\Support\Persuratan;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

/** The Jenis SK picker of the SK numbering flow, fed by Master Persuratan. */
class JenisSkSelectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PersuratanMasterSeeder::class);
    }

    public function test_picker_offers_the_active_jenis_sk_in_master_order(): void
    {
        PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'Jenis Lama', 'is_active' => false]);
        PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Surat Kuasa']);

        $options = Livewire::test(JenisSkPickerForm::class)->instance()->form->getComponent('data.jenis_sk')->getOptions();

        $this->assertSame(Persuratan::JENIS_SK, array_keys($options));
        $this->assertSame(Persuratan::JENIS_SK, array_values($options));
    }

    public function test_picker_requires_a_jenis_from_the_list(): void
    {
        Livewire::test(JenisSkPickerForm::class)
            ->set('data.jenis_sk', null)->call('save')->assertHasErrors(['data.jenis_sk' => 'required'])
            ->set('data.jenis_sk', 'Diketik Sendiri')->call('save')->assertHasErrors(['data.jenis_sk' => 'in'])
            ->set('data.jenis_sk', 'Kepanitiaan dan Tim Pelaksana Kegiatan')->call('save')->assertHasNoErrors()
            ->assertSet('saved', 'Kepanitiaan dan Tim Pelaksana Kegiatan');
    }

    public function test_switched_off_jenis_stays_on_the_record_that_uses_it(): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        $fresh = Livewire::test(JenisSkPickerForm::class)->instance()->form->getComponent('data.jenis_sk')->getOptions();
        $this->assertArrayNotHasKey('Keuangan dan Operasional', $fresh);

        Livewire::test(JenisSkPickerForm::class, ['savedJenis' => 'Keuangan dan Operasional'])
            ->assertSet('data.jenis_sk', 'Keuangan dan Operasional')
            ->call('save')->assertHasNoErrors();
    }
}

/** A bare form holding the picker, standing in for the Minta Nomor SK form. */
class JenisSkPickerForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    /** The jenis of an SK being edited, standing in for its record. */
    public ?string $savedJenis = null;

    public ?string $saved = null;

    public function mount(): void
    {
        $this->form->fill(['jenis_sk' => $this->savedJenis]);
    }

    public function form(Form $form): Form
    {
        $record = $this->savedJenis ? (new PersuratanMaster)->setAttribute('jenis_sk', $this->savedJenis) : null;

        return $form->schema([PersuratanFields::jenisSk()->required()])->model($record ?? PersuratanMaster::class)->statePath('data');
    }

    public function save(): void
    {
        $this->saved = $this->form->getState()['jenis_sk'];
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
