<?php

namespace App\Livewire;

use App\Support\CivitasRegistration;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

/** The single civitas registration code, edited from the roles page. */
class RegistrationCodeSettings extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->form->fill(['code' => CivitasRegistration::code()]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Kode registrasi civitas')
                    ->description('Civitas (siswa, guru, pegawai, wali murid, alumni, instansi) memasukkan kode ini saat mendaftar akun, lalu memilih statusnya sendiri. Kosongkan untuk menutup pendaftaran civitas.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->minLength(6)
                            ->maxLength(64)
                            ->regex('/^\S+$/')
                            ->validationMessages(['regex' => 'Kode tidak boleh berisi spasi.'])
                            ->helperText('Minimal 6 karakter, tanpa spasi. Ganti kode bila kode lama sudah tersebar ke pihak luar.')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Buat kode acak')
                                    ->action(fn (Forms\Set $set) => $set('code', (string) random_int(1000000000, 9999999999)))
                            ),
                    ]),
            ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $code = $this->form->getState()['code'] ?? null;
        CivitasRegistration::setCode($code);

        Notification::make()
            ->title(filled($code) ? 'Kode registrasi disimpan.' : 'Pendaftaran civitas ditutup.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.registration-code-settings', [
            'isOpen' => CivitasRegistration::code() !== null,
        ]);
    }
}
