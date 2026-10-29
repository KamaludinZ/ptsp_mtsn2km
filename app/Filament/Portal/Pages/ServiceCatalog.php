<?php

namespace App\Filament\Portal\Pages;

use App\Models\Service;
use App\Models\Ticket;
use App\Support\PortalServices;
use App\Support\ServiceSummary;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Katalog layanan of the portal (/portal/layanan): the services the
 * applicant may request, searchable and by category, each opening its
 * standard here and its request form (ApplyService) without leaving the
 * portal for the public catalogue.
 */
class ServiceCatalog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Katalog Layanan';

    protected static ?string $title = 'Katalog Layanan';

    protected static ?string $slug = 'layanan';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.portal.pages.service-catalog';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'kategori')]
    public ?string $category = null;

    /** One of the applicant's roles to show services for; none for all of them. */
    #[Url(as: 'peran')]
    public ?string $role = null;

    public function getSubheading(): ?string
    {
        return 'Layanan yang dapat Anda ajukan. Buka standar pelayanannya, lalu ajukan permohonan langsung dari sini.';
    }

    /**
     * The applicant's roles (an account may hold several, e.g. a teacher who
     * is also a parent); an account without any, its applicant type.
     *
     * @return list<string>
     */
    #[Computed]
    public function roles(): array
    {
        return PortalServices::rolesOf(auth()->user());
    }

    /** The services open to any of the applicant's roles, with their categories and what to prepare. */
    #[Computed]
    public function offered(): Collection
    {
        return PortalServices::offered(auth()->user())
            ->with(['categories' => fn ($query) => $query->orderBy('name')])
            ->withCount(['requirements', 'activeTemplates'])
            ->orderBy('name')
            ->get();
    }

    /** offered() narrowed by the search and the category. */
    #[Computed]
    public function services(): Collection
    {
        $term = mb_strtolower(trim($this->search));

        return $this->offered
            ->when($term !== '', fn ($services) => $services->filter(fn (Service $service) => str_contains(mb_strtolower($service->name), $term)
                || str_contains(mb_strtolower((string) $service->code), $term)))
            ->when($this->category, fn ($services) => $services->filter(fn (Service $service) => $service->categories->contains('id', (int) $this->category)))
            ->when(in_array($this->role, $this->roles, true), fn ($services) => $services->filter(fn (Service $service) => $service->isAvailableFor($this->role)))
            ->values();
    }

    /** Which of the applicant's roles $service is open to (shown when they hold several). */
    public function rolesFor(Service $service): array
    {
        return array_values(array_filter($this->roles, fn (string $role) => $service->isAvailableFor($role)));
    }

    /** The categories of the offered services, each with how many it holds. */
    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        return $this->offered->flatMap->categories->groupBy('id')
            ->map(fn ($group) => ['category' => $group->first(), 'count' => $group->count()])
            ->sortBy(fn (array $row) => $row['category']->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * The applicant's requests still in progress, by service: a card says
     * so before another request for the same service is made.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Ticket>
     */
    #[Computed]
    public function openRequests(): \Illuminate\Support\Collection
    {
        return Ticket::where('user_id', auth()->id())->open()->latest()->latest('id')
            ->get(['id', 'ticket_number', 'service_id', 'status'])
            ->unique('service_id') // the newest per service
            ->keyBy('service_id');
    }

    /** "Ajukan layanan" for one who knows what they want: pick a service, then its request form. */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Ajukan layanan')
                ->icon('heroicon-m-paper-airplane')
                ->modalHeading('Ajukan layanan')
                ->modalDescription('Pilih layanan yang ingin Anda ajukan secara online.')
                ->modalSubmitActionLabel('Lanjut ke formulir')
                ->form([
                    Select::make('service')
                        ->label('Layanan')
                        ->options(fn () => $this->offered->whereIn('mode', ['online', 'hybrid'])->pluck('name', 'slug'))
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data) => $this->redirect(ApplyService::getUrl(['layanan' => $data['service']]))),
        ];
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'role');
    }

    /** Standar pelayanan of one service, in a modal. */
    public function standardAction(): Action
    {
        return Action::make('standard')
            ->label('Standar pelayanan')
            ->icon('heroicon-m-clipboard-document-list')
            ->color('gray')
            ->link()
            ->modalHeading(fn (array $arguments) => Service::find($arguments['service'] ?? null)?->name ?? 'Standar pelayanan')
            ->modalContent(function (array $arguments) {
                $service = $this->offered->firstWhere('id', $arguments['service'] ?? null);

                return $service ? ServiceSummary::html($service) : null;
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
