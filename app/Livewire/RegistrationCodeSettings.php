<?php

namespace App\Livewire;

use App\Support\CivitasRegistration;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

/** Civitas registration switches and the shared code, edited from the roles page. */
class RegistrationCodeSettings extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->form->fill([
            'open' => CivitasRegistration::formEnabled(),
            'code_required' => CivitasRegistration::codeRequired(),
            'code' => CivitasRegistration::code(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Pendaftaran civitas')
                    ->description('Civitas (siswa, guru, pegawai, wali murid, alumni, instansi) mendaftar akun lewat tab "Civitas Internal" di halaman daftar, lalu memilih statusnya sendiri.')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Forms\Components\Toggle::make('open')
                            ->label('Buka form pendaftaran civitas')
                            ->helperText('Matikan untuk menutup pendaftaran civitas. Pendaftar umum tidak terpengaruh.')
                            ->live(),
                        Forms\Components\Toggle::make('code_required')
                            ->label('Wajibkan kode registrasi')
                            ->helperText('Matikan agar civitas bisa mendaftar tanpa kode. Siapa pun dapat memilih status civitas selama form dibuka.')
                            ->live(),
                    ]),
                Forms\Components\Section::make('Kode registrasi civitas')
                    ->description('Kode yang dimasukkan civitas saat mendaftar, bila penggunaan kode diwajibkan.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required(fn (Forms\Get $get) => $get('open') && $get('code_required'))
                            ->minLength(6)
                            ->maxLength(64)
                            ->regex('/^\S+$/')
                            ->validationMessages([
                                'required' => 'Isi kode, atau matikan "Wajibkan kode registrasi".',
                                'regex' => 'Kode tidak boleh berisi spasi.',
                            ])
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

        $state = $this->form->getState();
        CivitasRegistration::setFormEnabled((bool) ($state['open'] ?? false));
        CivitasRegistration::setCodeRequired((bool) ($state['code_required'] ?? true));
        CivitasRegistration::setCode($state['code'] ?? null);

        Notification::make()
            ->title(CivitasRegistration::isOpen() ? 'Pengaturan disimpan. Pendaftaran civitas dibuka.' : 'Pengaturan disimpan. Pendaftaran civitas ditutup.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.registration-code-settings', [
            'isOpen' => CivitasRegistration::isOpen(),
            'codeRequired' => CivitasRegistration::codeRequired(),
        ]);
    }
}
