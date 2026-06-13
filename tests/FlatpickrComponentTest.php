<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Enums\FlatpickrMode;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Illuminate\Support\Carbon;

it('configures week picker defaults and attributes', function () {
    $component = Flatpickr::make('week')->weekPicker()->format('W Y');

    expect($component->isWeekPicker())->toBeTrue()
        ->and($component->getFormat())->toBe('W Y')
        ->and($component->getFlatpickrAttributes()['weekPicker'])->toBeTrue();
});

it('configures month picker defaults and attributes', function () {
    $component = Flatpickr::make('month')->monthPicker()->format('Y-m');

    expect($component->isMonthPicker())->toBeTrue()
        ->and($component->getFormat())->toBe('Y-m')
        ->and($component->getFlatpickrAttributes()['monthPicker'])->toBeTrue();
});

it('configures time picker attributes with no calendar', function () {
    $component = Flatpickr::make('start_time')->timePicker();

    $attributes = $component->getFlatpickrAttributes();

    expect($component->isTimePicker())->toBeTrue()
        ->and($attributes['timePicker'])->toBeTrue()
        ->and($attributes['noCalendar'])->toBeTrue()
        ->and($attributes['enableTime'])->toBeTrue()
        ->and($attributes['dateFormat'])->toBe('H:i');
});

it('uses seconds format for time picker when enabled', function () {
    $component = Flatpickr::make('start_time')->timePicker()->seconds();

    expect($component->getFormat())->toBe('H:i:S')
        ->and($component->getFlatpickrAttributes()['dateFormat'])->toBe('H:i:S');
});

it('dehydrates datetime values to carbon instances', function () {
    $component = Flatpickr::make('published_at')->time(true)->format('Y-m-d H:i:s');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-15 14:30:00');

    expect($result)->toBeInstanceOf(CarbonInterface::class)
        ->and($result->format('Y-m-d H:i:s'))->toBe('2024-06-15 14:30:00');
});

it('passes min and max dates through to flatpickr attributes', function () {
    $component = Flatpickr::make('date')
        ->format('Y-m-d')
        ->minDate('2024-01-01')
        ->maxDate(Carbon::parse('2024-12-31'));

    $attributes = $component->getFlatpickrAttributes();

    expect($attributes['minDate'])->toBe('2024-01-01')
        ->and($attributes['maxDate'])->toBe('2024-12-31');
});

it('detects range picker mode from fluent helper and enum', function () {
    $fromHelper = Flatpickr::make('range')->rangePicker();
    $fromMode = Flatpickr::make('range')->mode(FlatpickrMode::RANGE);

    expect($fromHelper->isRangePicker())->toBeTrue()
        ->and($fromMode->isRangePicker())->toBeTrue()
        ->and($fromHelper->getFlatpickrAttributes()['rangePicker'])->toBeTrue();
});

it('detects multiple picker mode from fluent helper and enum', function () {
    $fromHelper = Flatpickr::make('dates')->multiplePicker();
    $fromMode = Flatpickr::make('dates')->mode(FlatpickrMode::MULTIPLE);

    expect($fromHelper->isMultiplePicker())->toBeTrue()
        ->and($fromMode->isMultiplePicker())->toBeTrue()
        ->and($fromHelper->getFlatpickrAttributes()['multiplePicker'])->toBeTrue();
});

it('defaults month picker format when not explicitly configured', function () {
    $component = Flatpickr::make('month')->monthPicker();

    expect($component->getFormat())->toBe('Y-m-d')
        ->and($component->isMonthPicker())->toBeTrue()
        ->and($component->getFlatpickrAttributes()['monthPicker'])->toBeTrue();
});

it('filters out unparsable single date values during dehydration', function () {
    $component = Flatpickr::make('date')->format('Y-m-d');

    expect(Flatpickr::dehydrateFlatpickr($component, 'not-a-date'))->toBe([]);
});

it('uses the default range separator and conjunction', function () {
    $range = Flatpickr::make('range')->rangePicker();
    $multiple = Flatpickr::make('dates')->multiplePicker();

    expect($range->getRangeSeparator())->toBe(' to ')
        ->and($multiple->getConjunction())->toBe(',');
});
