<?php

namespace App\Filament\Actions;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Support\NomorFormatSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

/**
 * Tab Variabel Tambahan: tambah/ubah dan hapus definisi variabel {v}/{V}
 * satu jenis surat. Tersimpan di variabel_nomor (NomorFormatSettings).
 */
class NumberVariableAction
{
    public static function make(): Action
    {
        return Action::make('variable')
            ->label(fn (PersuratanMaster $record) => NomorFormatSettings::variable($record) ? 'Ubah variabel' : 'Tambah variabel')
            ->icon(fn (PersuratanMaster $record) => NomorFormatSettings::variable($record) ? 'heroicon-m-pencil-square' : 'heroicon-m-plus')
            ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === ListPersuratanMasters::VARIABLE_TAB)
            ->modalHeading(fn (PersuratanMaster $record) => 'Variabel tambahan ' . $record->nama)
            ->modalDescription('Petugas mengisi variabel ini saat meminta nomor. Nilainya masuk ke nomor lewat token {v} (seperti diketik) atau {V} (huruf besar).')
            ->modalSubmitActionLabel('Simpan variabel')
            ->fillForm(fn (PersuratanMaster $record) => NomorFormatSettings::variable($record) ?? ['label' => null, 'keterangan' => null, 'wajib' => true])
            ->form([
                TextInput::make('label')
                    ->label('Nama variabel')
                    ->placeholder('mis. Kelas')
                    ->helperText('Tampil sebagai label isian pada form Minta Nomor Surat.')
                    ->required()
                    ->maxLength(60),
                Textarea::make('keterangan')
                    ->label('Arti / petunjuk pengisian')
                    ->placeholder('mis. Rombel siswa yang dimaksud surat, contoh IX-A')
                    ->rows(2)
                    ->maxLength(255),
                Toggle::make('wajib')
                    ->label('Wajib diisi saat minta nomor')
                    ->default(true),
            ])
            ->action(function (PersuratanMaster $record, array $data) {
                NomorFormatSettings::saveVariable($record, $data['label'], $data['keterangan'] ?? null, (bool) ($data['wajib'] ?? true));

                $notification = Notification::make()->success()->title('Variabel ' . $record->nama . ' disimpan');
                // A variable outside the format never reaches the number: say so.
                if (! NomorFormatSettings::usesVariable($record->fresh())) {
                    $notification->warning()->body('Format nomor jenis surat ini belum memuat {v} atau {V}. Tambahkan lewat tab Penomoran Otomatis agar nilainya ikut di nomor.');
                }
                $notification->send();
            });
    }

    public static function delete(): Action
    {
        return Action::make('deleteVariable')
            ->label('Hapus variabel')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->visible(fn ($livewire, PersuratanMaster $record) => ($livewire->activeTab ?? null) === ListPersuratanMasters::VARIABLE_TAB && NomorFormatSettings::variable($record))
            ->requiresConfirmation()
            ->modalHeading(fn (PersuratanMaster $record) => 'Hapus variabel ' . $record->nama . '?')
            ->modalDescription('Form Minta Nomor Surat tidak lagi meminta isian ini. Nomor yang sudah terbit tidak berubah.')
            ->action(function (PersuratanMaster $record) {
                NomorFormatSettings::saveVariable($record, null);
                Notification::make()->success()->title('Variabel ' . $record->nama . ' dihapus')->send();
            });
    }
}
