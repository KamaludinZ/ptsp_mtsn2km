<?php

namespace App\Filament\Resources\NotificationTemplateResource\Pages;

use App\Filament\Resources\NotificationTemplateResource;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Support\NotificationTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListNotificationTemplates extends ListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

    public function getSubheading(): ?string
    {
        return 'Isi pesan setiap notifikasi per kanal. Pesan hanya terkirim bila templatnya dan kanalnya aktif.';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->togglesAction(),
        ];
    }

    /** Panel kanal & pemicu: switch whole channels and each trigger per channel in one place. */
    private function togglesAction(): Action
    {
        $channels = array_keys(NotificationSetting::CHANNELS);

        return Action::make('toggles')
            ->label('Atur kanal & pemicu')
            ->icon('heroicon-m-adjustments-horizontal')
            ->modalHeading('Kanal & pemicu notifikasi')
            ->modalDescription('Pesan terkirim hanya bila kanalnya aktif dan pemicunya dicentang pada kanal itu. Isi pesan tetap diatur per template.')
            ->modalSubmitActionLabel('Simpan')
            ->fillForm(fn () => collect($channels)->mapWithKeys(fn (string $channel) => [$channel => [
                'is_enabled' => NotificationSetting::for($channel)->is_enabled,
                'events' => NotificationTemplate::where('channel', $channel)->where('is_active', true)->pluck('key')->all(),
            ]])->all())
            ->form([
                Grid::make(['default' => 1, 'md' => 2])->schema(collect($channels)->map(fn (string $channel) => Section::make(NotificationSetting::CHANNELS[$channel])
                    ->statePath($channel)
                    ->schema([
                        Toggle::make('is_enabled')->label('Kanal aktif')->live(),
                        CheckboxList::make('events')->label('Pemicu yang dikirim')
                            ->options(NotificationTemplates::EVENTS)
                            ->bulkToggleable()
                            ->disabled(fn (Get $get) => ! $get('is_enabled'))
                            ->helperText(fn (Get $get) => $get('is_enabled') ? null : 'Kanal nonaktif: tidak ada pesan yang dikirim.'),
                    ]))->all()),
            ])
            ->action(function (array $data) use ($channels) {
                DB::transaction(function () use ($data, $channels) {
                    foreach ($channels as $channel) {
                        $setting = NotificationSetting::for($channel);
                        $setting->is_enabled = (bool) ($data[$channel]['is_enabled'] ?? false);
                        if ($setting->isDirty('is_enabled')) {
                            $setting->fill(['updated_by' => auth()->id()])->save();
                        }

                        // A disabled CheckboxList is not submitted: keep that channel's triggers as they were.
                        if (! array_key_exists('events', $data[$channel] ?? [])) {
                            continue;
                        }

                        $wanted = $data[$channel]['events'] ?? [];
                        foreach (NotificationTemplates::EVENTS as $event => $label) {
                            $template = NotificationTemplate::firstOrNew(['key' => $event, 'channel' => $channel]);
                            if (! $template->exists && ! in_array($event, $wanted, true)) {
                                continue;
                            }
                            if (! $template->exists) {
                                $default = NotificationTemplates::defaults()[$event] ?? ['subject' => $label, 'body' => $label];
                                $template->fill(['subject' => $channel === 'email' ? $default['subject'] : null, 'body' => $default['body']]);
                            }
                            $template->is_active = in_array($event, $wanted, true);
                            if ($template->isDirty()) {
                                $template->fill(['updated_by' => auth()->id()])->save();
                            }
                        }
                    }
                });

                Notification::make()->title('Kanal & pemicu notifikasi disimpan.')->success()->send();
                $this->resetTable();
            });
    }
}
