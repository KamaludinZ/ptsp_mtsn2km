<?php

namespace App\Filament\Pages\System;

use App\Filament\Resources\RoleResource;
use App\Support\ProcessorRoles;
use App\Support\ServiceDisposition;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spatie\Permission\Models\Role;

/**
 * Daftar peran pemroses naskah: the six back-office units that receive
 * dispositions, who holds each role and which services use it. Admins
 * assign the staff holding each role from here.
 */
class ProcessorRoleList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Peran Pemroses Naskah';

    protected static ?string $title = 'Peran Pemroses Naskah';

    protected static ?string $slug = 'pengguna/peran-pemroses';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.processor-roles';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public function getSubheading(): ?string
    {
        return 'Unit back office yang menerima disposisi pimpinan. Setiap peran dipegang staf yang memproses naskah unitnya.';
    }

    protected function getViewData(): array
    {
        return [
            'roles' => ProcessorRoles::overview(),
            'editUrl' => fn ($role) => $role && RoleResource::can('edit', $role) ? RoleResource::getUrl('edit', ['record' => $role]) : null,
        ];
    }

    /** Penugasan staf: choose who holds one processor role. */
    public function assignAction(): Action
    {
        return Action::make('assign')
            ->label('Atur pemegang')
            ->icon('heroicon-m-user-plus')
            ->size('sm')
            ->link()
            ->modalHeading(fn (array $arguments) => 'Pemegang peran ' . (ServiceDisposition::RECIPIENTS[$arguments['role'] ?? ''] ?? ''))
            ->modalDescription('Staf yang dipilih menerima disposisi untuk unit ini. Peran back office mereka tetap berlaku.')
            ->modalSubmitActionLabel('Simpan pemegang')
            ->fillForm(fn (array $arguments) => [
                'users' => Role::where('guard_name', 'web')->where('name', $arguments['role'] ?? '')->first()?->users()->orderBy('users.id')->pluck('users.id')->all() ?? [],
            ])
            ->form([
                Select::make('users')
                    ->label('Staf pemegang')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => ProcessorRoles::eligibleStaff())
                    ->helperText('Hanya akun staf aktif dengan akses back office yang dapat dipilih.')
                    ->rules(['array'])
                    ->nestedRecursiveRules(['integer', 'in:' . implode(',', array_keys(ProcessorRoles::eligibleStaff()))])
                    ->validationMessages(['in' => 'Staf yang dipilih tidak memiliki akses back office.']),
            ])
            ->action(function (array $data, array $arguments) {
                $name = (string) ($arguments['role'] ?? '');
                ProcessorRoles::assign($name, $data['users'] ?? []);

                Notification::make()->success()
                    ->title('Pemegang ' . ServiceDisposition::RECIPIENTS[$name] . ' disimpan')
                    ->body(count($data['users'] ?? []) . ' staf memegang peran ini.')
                    ->send();
            });
    }
}
