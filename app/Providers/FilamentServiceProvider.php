<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Pages;
use Filament\Widgets;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('filamentphp/filament');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            \Filament\Commands\MakePageCommand::class,
            \App\Commands\FilamentMakePageCommand::class
        );

        $this->app->bind(
            \Filament\Commands\MakeResourceCommand::class,
            \App\Commands\FilamentMakeResourceCommand::class
        );
    }

    public function packageBooted(): void
    {
        Filament::serving(function () {
            //
        });
    }

    protected function getAssetPackageName(): ?string
    {
        return 'filament/filament';
    }

    /**
     * @return array<class-string>
     */
    protected function getAssetPackageClassNames(): array
    {
        return [
            \Filament\FilamentServiceProvider::class,
        ];
    }
}