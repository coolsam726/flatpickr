<?php

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;

it('dehydrates a split range into separate start and end state paths', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    expect($component->getStateToDehydrate('2024-06-01 to 2024-06-15'))->toBe([
        'starts_at' => '2024-06-01',
        'ends_at' => '2024-06-15',
    ]);
});

it('clears both range end paths when the picker state is blank', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    expect($component->getStateToDehydrate(null))->toBe([
        'starts_at' => null,
        'ends_at' => null,
    ])->and($component->getStateToDehydrate(''))->toBe([
        'starts_at' => null,
        'ends_at' => null,
    ]);
});

it('dehydrates split range values from array picker state', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    expect($component->getStateToDehydrate(['2024-06-01', '2024-06-15']))->toBe([
        'starts_at' => '2024-06-01',
        'ends_at' => '2024-06-15',
    ]);
});

it('keeps single-field dehydration when no range end field is configured', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    expect(Flatpickr::dehydrateFlatpickr($component, '2024-06-01 to 2024-06-15'))->toBe([
        '2024-06-01',
        '2024-06-15',
    ]);
});

it('hydrates split database values into a combined range string', function () {
    $component = StatefulFlatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d')
        ->rangeSeparator(' to ');

    $component->hydrateFlatpickr($component, ['2024-06-01', '2024-06-15']);

    expect($component->hydratedState)->toBe('2024-06-01 to 2024-06-15');
});

it('exposes the configured range end field name and state path', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at');

    expect($component->hasRangeEndField())->toBeTrue()
        ->and($component->getRangeEndField())->toBe('ends_at')
        ->and($component->getRangeEndStatePath())->toBe('ends_at');
});

it('dehydrates a split datetime range into separate start and end state paths', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    expect($component->getStateToDehydrate('2024-06-14 07:00 to 2024-06-17 17:00'))->toBe([
        'starts_at' => '2024-06-14 07:00',
        'ends_at' => '2024-06-17 17:00',
    ]);
});

it('hydrates split datetime database values into a combined range string', function () {
    $component = StatefulFlatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    $component->hydrateFlatpickr($component, ['2024-06-14 07:00', '2024-06-17 17:00']);

    expect($component->hydratedState)->toBe('2024-06-14 07:00 to 2024-06-17 17:00');
});

it('extracts datetime matches from concatenated range strings', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator('not-in-string');

    $method = new ReflectionMethod(Flatpickr::class, 'splitRangeString');
    $parts = $method->invoke(null, '2024-06-14 07:002024-06-17 17:00', $component);

    expect($parts)->toBe(['2024-06-14 07:00', '2024-06-17 17:00']);
});

it('does not resolve a range end value until both dates are selected', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i');

    $method = new ReflectionMethod($component, 'resolveRangeEndStateValue');

    expect($method->invoke($component, '2024-06-14 07:00'))->toBeNull();
});

it('resolves a formatted datetime for the range end when the range is complete', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    $method = new ReflectionMethod($component, 'resolveRangeEndStateValue');

    expect($method->invoke($component, '2024-06-14 07:00 to 2024-06-17 17:00'))->toBe('2024-06-17 17:00');
});

it('dehydrates datetime ends without dropping the time component', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    $dehydrated = $component->getStateToDehydrate('2024-06-14 07:00 to 2024-06-17 17:00');

    expect($dehydrated['starts_at'])->toBe('2024-06-14 07:00')
        ->and($dehydrated['ends_at'])->toBe('2024-06-17 17:00')
        ->and($dehydrated['ends_at'])->not->toBe('2024-06-17 00:00');
});

it('preserves range order when the end datetime is before the start on the same day', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    $dehydrated = $component->getStateToDehydrate('2024-06-14 15:00 to 2024-06-14 14:00');

    expect($dehydrated['starts_at'])->toBe('2024-06-14 15:00')
        ->and($dehydrated['ends_at'])->toBe('2024-06-14 14:00');
});
