<?php

use Coolsam\Flatpickr\FlatpickrServiceProvider;
use Filament\Support\Facades\FilamentAsset;

it('registers flatpickr assets with filament', function () {
    expect(FilamentAsset::getAlpineComponentSrc('flatpickr', 'coolsam/flatpickr'))->toBeString()
        ->and(FilamentAsset::getStyleHref('flatpickr-styles', 'coolsam/flatpickr'))->toBeString();
});

it('exposes the flatpickr package name', function () {
    expect(FlatpickrServiceProvider::$name)->toBe('flatpickr');
});
