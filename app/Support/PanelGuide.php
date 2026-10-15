<?php

namespace App\Support;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Illuminate\Support\Collection;

/**
 * Panduan menu: setiap menu yang boleh dibuka pengguna (mengikuti peran
 * aktifnya) beserta penjelasan dan langkah pemakaiannya. Isi panduan ada di
 * resources/panduan/{panel}.php, berkunci kelas halaman/resource atau
 * "nav:<label>" untuk menu tambahan panel.
 */
class PanelGuide
{
    /**
     * @return Collection<int, array{group: string, items: list<array{key: string, label: string, icon: ?string, url: ?string, ringkas: string, langkah: list<string>, tips: ?string}>}>
     */
    public static function sections(Panel $panel): Collection
    {
        $content = static::content($panel->getId());

        $items = collect([...$panel->getResources(), ...$panel->getPages()])
            ->unique()
            ->filter(fn (string $class) => $class !== \App\Filament\Pages\Guide::class
                && $class !== \App\Filament\Portal\Pages\Guide::class
                && $class::shouldRegisterNavigation() && $class::canAccess())
            ->map(fn (string $class) => static::entry($class, [
                'label' => (string) $class::getNavigationLabel(),
                'group' => $class::getNavigationGroup(),
                'icon' => $class::getNavigationIcon(),
                'url' => $class::getNavigationUrl(),
                'sort' => $class::getNavigationSort(),
            ], $content[$class] ?? null))
            ->merge(collect($panel->getNavigationItems())
                ->filter(fn (NavigationItem $item) => $item->isVisible())
                ->map(fn (NavigationItem $item) => static::entry('nav:' . $item->getLabel(), [
                    'label' => $item->getLabel(),
                    'group' => $item->getGroup(),
                    'icon' => $item->getIcon(),
                    'url' => $item->getUrl(),
                    'sort' => $item->getSort(),
                ], $content['nav:' . $item->getLabel()] ?? null)));

        $groupOrder = collect($panel->getNavigationGroups())
            ->map(fn ($group, $key) => $group instanceof NavigationGroup ? $group->getLabel() : (is_string($group) ? $group : $key))
            ->values();

        return $items
            ->groupBy(fn (array $item) => $item['group'] ?? '')
            ->map(fn (Collection $group, string $name) => [
                'group' => $name === '' ? 'Umum' : $name,
                'order' => $name === '' ? -1 : (($i = $groupOrder->search($name)) === false ? PHP_INT_MAX : $i),
                'items' => $group->sortBy([['sort', 'asc'], ['label', 'asc']])->map(fn ($item) => collect($item)->except(['group', 'sort'])->all())->values()->all(),
            ])
            ->sortBy('order')
            ->map(fn (array $group) => collect($group)->except('order')->all())
            ->values();
    }

    /** @return array<string, array{ringkas?: string, langkah?: list<string>, tips?: string}> */
    public static function content(string $panelId): array
    {
        $file = resource_path("panduan/{$panelId}.php");

        return is_file($file) ? require $file : [];
    }

    private static function entry(string $key, array $nav, ?array $guide): array
    {
        return $nav + [
            'key' => str($key)->afterLast('\\')->afterLast(':')->kebab()->slug()->toString(),
            'ringkas' => $guide['ringkas'] ?? 'Menu ' . $nav['label'] . '.',
            'langkah' => $guide['langkah'] ?? [],
            'tips' => $guide['tips'] ?? null,
        ];
    }
}
