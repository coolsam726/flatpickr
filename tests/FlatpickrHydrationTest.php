<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;
use Illuminate\Support\Carbon;

function setFlatpickrNative(StatefulFlatpickr $component, bool $native = true): StatefulFlatpickr
{
    $property = new ReflectionProperty($component, 'isNative');
    $property->setValue($component, $native);

    return $component;
}

it('returns early when hydrating blank state', function () {
    $component = StatefulFlatpickr::make('date')->format('Y-m-d');

    $component->hydrateFlatpickr($component, null);
    $component->hydrateFlatpickr($component, '');

    expect($component->hydratedState)->toBeNull();
});

it('hydrates a single carbon value for non-native fields', function () {
    $component = StatefulFlatpickr::make('date')->format('Y-m-d');

    $component->hydrateFlatpickr($component, Carbon::parse('2024-06-15'));

    expect($component->hydratedState)->toBe('2024-06-15');
});

it('hydrates a single carbon value for native date fields', function () {
    $component = setFlatpickrNative(StatefulFlatpickr::make('date')->format('Y-m-d'));

    $component->hydrateFlatpickr($component, Carbon::parse('2024-06-15'));

    expect($component->hydratedState)->toBe('2024-06-15');
});

it('hydrates a single carbon value for native datetime fields', function () {
    $component = setFlatpickrNative(
        StatefulFlatpickr::make('published_at')->time(true)->format('Y-m-d H:i:s'),
    );

    $component->hydrateFlatpickr($component, Carbon::parse('2024-06-15 14:30:00'));

    expect($component->hydratedState)->toBe('2024-06-15 14:30');
});

it('hydrates a single carbon value for native datetime fields with seconds', function () {
    $component = setFlatpickrNative(
        StatefulFlatpickr::make('published_at')->time(true)->seconds()->format('Y-m-d H:i:s'),
    );

    $component->hydrateFlatpickr($component, Carbon::parse('2024-06-15 14:30:45'));

    expect($component->hydratedState)->toBe('2024-06-15 14:30:45');
});

it('hydrates a single carbon value for native time-only fields', function () {
    $component = setFlatpickrNative(
        StatefulFlatpickr::make('start_time')->date(false)->time(true)->format('H:i'),
    );

    $component->hydrateFlatpickr($component, Carbon::createFromFormat('H:i', '08:30', config('app.timezone')));

    expect($component->hydratedState)->toBe('08:30');
});

it('hydrates multiple picker values from an array', function () {
    $component = StatefulFlatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $component->hydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($component->hydratedState)->toBe('2024-06-01,2024-06-15');
});

it('hydrates multiple picker values from a string', function () {
    $component = StatefulFlatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $component->hydrateFlatpickr($component, '2024-06-01');

    expect($component->hydratedState)->toBe('2024-06-01');
});

it('hydrates native multiple picker values', function () {
    $component = setFlatpickrNative(
        StatefulFlatpickr::make('dates')
            ->multiplePicker()
            ->format('Y-m-d')
            ->conjunction('|'),
    );

    $component->hydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($component->hydratedState)->toBe('2024-06-01|2024-06-15');
});

it('hydrates range picker values from an array', function () {
    $component = StatefulFlatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d')
        ->rangeSeparator(' to ');

    $component->hydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($component->hydratedState)->toBe('2024-06-01 to 2024-06-15');
});

it('hydrates native range picker values', function () {
    $component = setFlatpickrNative(
        StatefulFlatpickr::make('range')
            ->rangePicker()
            ->format('Y-m-d')
            ->rangeSeparator(' - '),
    );

    $component->hydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($component->hydratedState)->toBe('2024-06-01 - 2024-06-15');
});

it('clears state when hydration receives an unparsable value', function () {
    $component = StatefulFlatpickr::make('date')->format('Y-m-d');

    $component->hydrateFlatpickr($component, 'not-a-date');

    expect($component->hydratedState)->toBeNull();
});

it('parses carbon instances using the configured timezone', function () {
    $component = StatefulFlatpickr::make('date')
        ->format('Y-m-d')
        ->timezone('UTC');

    $result = (new ReflectionMethod($component, 'parseToCarbon'))
        ->invoke($component, Carbon::parse('2024-06-15', 'Europe/Paris'));

    expect($result)->toBeInstanceOf(CarbonInterface::class)
        ->and($result->timezone->getName())->toBe('UTC');
});
