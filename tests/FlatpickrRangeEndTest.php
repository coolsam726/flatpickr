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
