<?php

use Coolsam\Flatpickr\FlatpickrServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Artisan;

it('registers flatpickr assets with filament', function () {
    expect(FilamentAsset::getAlpineComponentSrc('flatpickr', 'coolsam/flatpickr'))->toBeString()
        ->and(FilamentAsset::getStyleHref('flatpickr-styles', 'coolsam/flatpickr'))->toBeString();
});

it('exposes the flatpickr package name', function () {
    expect(FlatpickrServiceProvider::$name)->toBe('flatpickr');
});

it('publishes flatpickr assets from the service provider', function () {
    Artisan::call('vendor:publish', [
        '--tag' => 'flatpickr-assets',
        '--force' => true,
    ]);

    expect(Artisan::output())->toContain('Publishing');
});

it('publishes flatpickr stubs from the service provider', function () {
    Artisan::call('vendor:publish', [
        '--tag' => 'flatpickr-stubs',
        '--force' => true,
    ]);

    expect(Artisan::output())->toContain('Publishing')
        ->and(base_path('stubs/flatpickr/flatpickr-field.stub'))->toBeFile();
});

it('exposes package routes and migration metadata', function () {
    $provider = $this->app->getProvider(FlatpickrServiceProvider::class);

    $routesMethod = new ReflectionMethod($provider, 'getRoutes');
    $migrationsMethod = new ReflectionMethod($provider, 'getMigrations');

    expect($routesMethod->invoke($provider))->toBe([])
        ->and($migrationsMethod->invoke($provider))->toBe(['create_flatpickr_table']);
});
