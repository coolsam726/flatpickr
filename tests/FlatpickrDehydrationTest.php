<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

it('returns null when dehydrating blank state', function () {
    $component = Flatpickr::make('date');

    expect(Flatpickr::dehydrateFlatpickr($component, null))->toBeNull()
        ->and(Flatpickr::dehydrateFlatpickr($component, ''))->toBeNull();
});

it('dehydrates a single date string to a carbon instance', function () {
    $component = Flatpickr::make('date')->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-15');

    expect($result)->toBeInstanceOf(CarbonInterface::class)
        ->and($result->format('Y-m-d'))->toBe('2024-06-15');
});

it('dehydrates a range with dates separated by whitespace', function () {
    $component = Flatpickr::make('range')->rangePicker()->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-01 to 2024-06-15');

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates a range by dynamically splitting concatenated dates', function () {
    $component = Flatpickr::make('range')->rangePicker()->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-012024-06-15');

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates multiple dates separated by the configured conjunction', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-01,2024-06-15,2024-06-30');

    expect($result)->toBe(['2024-06-01', '2024-06-15', '2024-06-30']);
});
