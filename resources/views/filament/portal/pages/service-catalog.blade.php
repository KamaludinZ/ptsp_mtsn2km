<x-filament-panels::page>
    {{-- An account with several applicant roles picks whose services to see. --}}
    @if (count($this->roles) > 1)
        <nav style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;" aria-label="Tampilkan layanan untuk peran">
            <span class="text-sm text-gray-500 dark:text-gray-400">Layanan untuk:</span>
            @foreach (['' => 'Semua peran saya', ...collect($this->roles)->mapWithKeys(fn ($role) => [$role => \App\Support\RoleAccess::roleLabel($role)])->all()] as $value => $label)
                <x-filament::button size="sm" :color="($role ?? '') === $value ? 'primary' : 'gray'" :outlined="($role ?? '') !== $value"
                    wire:click="$set('role', {{ $value === '' ? 'null' : \Illuminate\Support\Js::from($value) }})"
                    :aria-pressed="($role ?? '') === $value ? 'true' : 'false'">
                    {{ $label }}
                </x-filament::button>
            @endforeach
        </nav>
    @endif

    {{-- Search and category: narrows the cards below (ServiceCatalog::services). --}}
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
        <div style="flex: 1 1 16rem;">
            <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                <x-filament::input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau kode layanan" aria-label="Cari layanan" />
            </x-filament::input.wrapper>
        </div>
        @if ($this->categories->isNotEmpty())
            <div style="flex: 0 1 16rem;">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="category" aria-label="Kategori layanan">
                        <option value="">Semua kategori</option>
                        @foreach ($this->categories as ['category' => $item, 'count' => $count])
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $count }})</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        @endif
        <span class="text-sm text-gray-500 dark:text-gray-400" role="status">{{ $this->services->count() }} layanan</span>
    </div>

    @if ($this->services->isEmpty())
        <x-filament::section>
            <div style="text-align: center; padding: 2rem 1rem;">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-10 w-10 text-gray-400" style="margin: 0 auto 0.75rem;" />
                @if ($this->offered->isEmpty())
                    <p class="font-semibold text-gray-950 dark:text-white">Belum ada layanan untuk akun Anda</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Hubungi petugas PTSP bila Anda memerlukan layanan yang belum tercantum.</p>
                @else
                    <p class="font-semibold text-gray-950 dark:text-white">Tidak ada layanan yang cocok</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-bottom: 1rem;">Ubah kata kunci atau pilih kategori lain.</p>
                    <x-filament::button color="gray" wire:click="resetFilters">Tampilkan semua layanan</x-filament::button>
                @endif
            </div>
        </x-filament::section>
    @else
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 18rem), 1fr));">
            @foreach ($this->services as $service)
                <x-portal.service-card :service="$service" :open-request="$this->openRequests->get($service->id)"
                    :for-roles="count($this->roles) > 1 ? $this->rolesFor($service) : []" wire:key="layanan-{{ $service->id }}">
                    {{ ($this->standardAction)(['service' => $service->id]) }}
                </x-portal.service-card>
            @endforeach
        </div>
    @endif

</x-filament-panels::page>
