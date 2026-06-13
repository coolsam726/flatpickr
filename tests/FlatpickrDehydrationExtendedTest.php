<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;
use Illuminate\Support\Carbon;

function setFlatpickrNativeDehydration(StatefulFlatpickr $component, bool $native = true): StatefulFlatpickr
{
    $property = new ReflectionProperty($component, 'isNative');
    $property->setValue($component, $native);

    return $component;
}

it('returns an empty array when a single date value cannot be parsed', function () {
    $component = Flatpickr::make('date')->format('Y-m-d');

    expect(Flatpickr::dehydrateFlatpickr($component, 'definitely-not-a-date'))->toBe([]);
});

it('returns null when dehydrating unsupported state types', function () {
    $component = Flatpickr::make('dates')->multiplePicker()->format('Y-m-d');

    expect(Flatpickr::dehydrateFlatpickr($component, false))->toBeNull();
});

it('dehydrates native range values as formatted date strings', function () {
    $component = setFlatpickrNativeDehydration(
        StatefulFlatpickr::make('range')->rangePicker()->format('Y-m-d'),
    );

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-01 to 2024-06-15');

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates native multiple values as formatted date strings', function () {
    $component = setFlatpickrNativeDehydration(
        StatefulFlatpickr::make('dates')
            ->multiplePicker()
            ->format('Y-m-d')
            ->conjunction(','),
    );

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-01,2024-06-15');

    expect($result)->toBe(['2024-06-01', '2024-06-15']);
});

it('dehydrates native time-only range values with minute precision', function () {
    $component = setFlatpickrNativeDehydration(
        StatefulFlatpickr::make('times')
            ->multiplePicker()
            ->date(false)
            ->time(true)
            ->format('H:i'),
    );

    $result = Flatpickr::dehydrateFlatpickr($component, '08:30,09:15');

    expect($result)->toBe(['08:30', '09:15']);
});

it('splits range strings using regex matches for custom formats', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('d/m/Y')
        ->rangeSeparator('invalid');

    $result = Flatpickr::dehydrateFlatpickr($component, '01/06/202401/06/2024');

    expect($result)->toBe(['01/06/2024', '01/06/2024']);
});

it('falls back to returning the original range string when it cannot be split', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d')
        ->rangeSeparator('invalid');

    $method = new ReflectionMethod(Flatpickr::class, 'splitRangeString');
    $parts = $method->invoke(null, 'not-a-range', $component);

    expect($parts)->toBe(['not-a-range']);
});

it('parses carbon instances passed directly into dehydration', function () {
    $component = Flatpickr::make('date')->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr(
        $component,
        Carbon::createFromFormat('Y-m-d', '2024-06-15', config('app.timezone')),
    );

    expect($result)->toBeInstanceOf(CarbonInterface::class);
});

it('dehydrates datetime carbon instances with minute precision when only time is enabled', function () {
    $component = Flatpickr::make('published_at')->date(false)->time(true)->format('H:i');

    $result = Flatpickr::dehydrateFlatpickr(
        $component,
        Carbon::createFromFormat('H:i', '14:45', config('app.timezone')),
    );

    expect($result)->toBe('14:45');
});

it('dehydrates datetime carbon instances with seconds when enabled', function () {
    $component = Flatpickr::make('published_at')->date(false)->time(true)->seconds()->format('H:i:s');

    $result = Flatpickr::dehydrateFlatpickr(
        $component,
        Carbon::createFromFormat('H:i:s', '14:45:10', config('app.timezone')),
    );

    expect($result)->toBe('14:45:10');
});

it('extracts repeated date matches from concatenated range strings', function () {
    $component = Flatpickr::make('range')->rangePicker()->format('Y-m-d');

    $method = new ReflectionMethod(Flatpickr::class, 'extractDateMatches');
    $matches = $method->invoke(null, '2024-06-012024-06-15', $component);

    expect($matches)->toBe(['2024-06-01', '2024-06-15']);
});
