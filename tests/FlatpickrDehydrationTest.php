<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Illuminate\Support\Carbon;

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

it('dehydrates a range using a custom separator', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('d/m/Y')
        ->rangeSeparator(' a ');

    $result = Flatpickr::dehydrateFlatpickr($component, '01/06/2024 a 15/06/2024');

    expect($result)->toBe(['01/06/2024', '15/06/2024']);
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

it('dehydrates multiple dates from an array state', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates a range from an array state', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates a time picker value as a time string', function () {
    $component = Flatpickr::make('start_time')->timePicker();

    $result = Flatpickr::dehydrateFlatpickr($component, '08:30');

    expect($result)->toBe('08:30');
});

it('defaults time picker format to hours and minutes', function () {
    $component = Flatpickr::make('start_time')->timePicker();

    expect($component->getFormat())->toBe('H:i');
});

it('passes locale arrays through to flatpickr attributes', function () {
    $component = Flatpickr::make('date')->locale([
        'locale' => 'es',
        'firstDayOfWeek' => 1,
    ]);

    expect($component->getLocale())->toBe([
        'locale' => 'es',
        'firstDayOfWeek' => 1,
    ])
        ->and($component->getFlatpickrAttributes()['locale'])->toBe([
            'locale' => 'es',
            'firstDayOfWeek' => 1,
        ]);
});

it('passes range separator through to flatpickr attributes', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->rangeSeparator(' — ');

    expect($component->getFlatpickrAttributes()['rangeSeparator'])->toBe(' — ');
});

it('registers date validation during setup instead of dehydration', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d');

    $rules = $component->getValidationRules();

    expect($rules)->not->toBeEmpty();
});

it('dehydrates cleared multiple picker strings without throwing', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    expect(Flatpickr::dehydrateFlatpickr($component, '2024-06-01,2024-06-15'))
        ->toBeArray()
        ->and(Flatpickr::dehydrateFlatpickr($component, null))
        ->toBeNull();
});

it('parses carbon instances for time-only dehydration output', function () {
    $component = Flatpickr::make('start_time')
        ->timePicker()
        ->format('H:i');

    $result = Flatpickr::dehydrateFlatpickr(
        $component,
        Carbon::createFromFormat('H:i', '14:45', config('app.timezone')),
    );

    expect($result)->toBe('14:45');
});
