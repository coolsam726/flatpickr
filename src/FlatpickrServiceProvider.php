<?php

namespace Coolsam\Flatpickr;

use Coolsam\Flatpickr\Commands\FlatpickrCommand;
use Coolsam\Flatpickr\Testing\TestsFlatpickr;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FlatpickrServiceProvider extends PackageServiceProvider
{
    public static string $name = 'flatpickr';

    public static string $viewNamespace = 'flatpickr';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (Command $command) {
                        $command->alert('Installing Flatpickr...');
                        $command->comment('Publishing package assets...');
                        $overwriteAssets = $command->confirm(__('Do you want to overwrite the existing package assets if any?'), false);
                        $assetPublish = [
                            '--tag' => 'flatpickr-assets',
                        ];
                        if ($overwriteAssets) {
                            $assetPublish['--force'] = true;
                        }
                        Artisan::call('vendor:publish', $assetPublish);
                        $command->info('Done.');
                        $command->comment('Publishing package config file...');
                        $overwriteConfig = $command->confirm(__('Do you want to overwrite the package config file if existing?'), false);
                        $configPublish = [
                            '--tag' => 'flatpickr-config',
                        ];
                        if ($overwriteConfig) {
                            $configPublish['--force'] = true;
                        }
                        Artisan::call('vendor:publish', $configPublish);
                    })
                    ->askToStarRepoOnGitHub('coolsam726/flatpickr');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // No package-level bindings are registered for this plugin.
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        $this->publishes([
            __DIR__ . '/../resources/dist' => public_path('vendor/flatpickr'),
        ], 'flatpickr-assets');

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/flatpickr/{$file->getFilename()}"),
                ], 'flatpickr-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFlatpickr);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'coolsam/flatpickr';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make('flatpickr', __DIR__ . '/../resources/dist/components/flatpickr.js'),
            Css::make('flatpickr-styles', __DIR__ . '/../resources/dist/flatpickr.css'),
            // Js::make('flatpickr-scripts', __DIR__ . '/../resources/dist/flatpickr.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FlatpickrCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_flatpickr_table',
        ];
    }
}
