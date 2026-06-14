<?php

use Carbon\CarbonInterface;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Coolsam\Flatpickr\Tests\Support\RangeEndTestForm;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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

it('sorts inverted same-day datetime ranges chronologically when dehydrating', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i')
        ->rangeSeparator(' to ');

    $dehydrated = $component->getStateToDehydrate('2024-06-14 15:00 to 2024-06-14 14:00');

    expect($dehydrated['starts_at'])->toBe('2024-06-14 14:00')
        ->and($dehydrated['ends_at'])->toBe('2024-06-14 15:00');
});

it('normalizes inverted range picker state after updates', function () {
    [$livewire, $field] = RangeEndTestForm::mount([
        'starts_at' => null,
        'ends_at' => null,
    ], useDatetime: true);

    $field->state('2024-06-14 06:30 to 2024-06-14 06:10');
    $field->callAfterStateUpdated(false);

    expect($field->getState())->toBe('2024-06-14 06:10 to 2024-06-14 06:30')
        ->and($livewire->data['ends_at'])->toBe('2024-06-14 06:30');
});

it('sorts inverted datetime ranges when hydrating separate database values', function () {
    [, $field] = RangeEndTestForm::mount([
        'starts_at' => '2024-06-14 06:30',
        'ends_at' => '2024-06-14 06:10',
    ], useDatetime: true);

    $field->callAfterStateHydrated();

    expect($field->getState())->toBe('2024-06-14 06:10 to 2024-06-14 06:30');
});

it('returns a single date unchanged when sorting range dates', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $method = new ReflectionMethod(Flatpickr::class, 'sortRangeDates');
    $dates = ['2024-06-14'];

    expect($method->invoke(null, $dates, $component))->toBe($dates);
});

it('returns already sorted array range state unchanged', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $method = new ReflectionMethod($component, 'normalizeSortedRangeState');
    $state = ['2024-06-14', '2024-06-15'];

    expect($method->invoke($component, $state))->toBe($state);
});

it('sorts inverted array range state chronologically', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->time(true)
        ->format('Y-m-d H:i');

    $method = new ReflectionMethod($component, 'normalizeSortedRangeState');

    expect($method->invoke($component, ['2024-06-14 15:00', '2024-06-14 14:00']))->toBe([
        '2024-06-14 14:00',
        '2024-06-14 15:00',
    ]);
});

it('returns non-string range state unchanged when normalizing', function () {
    $component = Flatpickr::make('range')->rangePicker()->format('Y-m-d');

    $method = new ReflectionMethod($component, 'normalizeSortedRangeState');

    expect($method->invoke($component, false))->toBeFalse()
        ->and($method->invoke($component, Carbon::parse('2024-06-14')))->toBeInstanceOf(CarbonInterface::class);
});

it('returns the range end field name when the start field has no state path', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at');

    $property = new ReflectionProperty($component, 'statePath');
    $property->setValue($component, null);

    expect($component->getRangeEndStatePath())->toBe('ends_at');
});

it('resolves nested range end state paths relative to the start field', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at');

    $property = new ReflectionProperty($component, 'statePath');
    $property->setValue($component, 'event.starts_at');

    expect($component->getRangeEndStatePath())->toBe('event.ends_at');
});

it('resolves range end and dehydration paths through the schema container', function () {
    [, $field] = RangeEndTestForm::mount();

    $dehydrationPath = (new ReflectionMethod($field, 'getDehydrationStatePath'))->invoke($field);

    expect($field->getRangeEndStatePath())->toBe('data.ends_at')
        ->and($dehydrationPath)->toBe('data.starts_at');
});

it('delegates to parent dehydration when range end is configured without range picker mode', function () {
    $livewire = new RangeEndTestForm;
    $field = Flatpickr::make('starts_at')
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    Schema::make($livewire)
        ->statePath('data')
        ->components([$field])
        ->getComponents(withActions: false, withHidden: true);

    $result = $field->getStateToDehydrate('2024-06-01');

    expect($result)->toHaveKey('data.starts_at')
        ->and($result['data.starts_at'])->toBeInstanceOf(CarbonInterface::class)
        ->and($result['data.starts_at']->toDateString())->toBe('2024-06-01');
});

it('applies state casts before splitting range end dehydration', function () {
    $cast = new class implements StateCast
    {
        public function get(mixed $state): mixed
        {
            return is_string($state) ? trim($state) : $state;
        }

        public function set(mixed $state): mixed
        {
            return $state;
        }
    };

    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d')
        ->stateCast(fn (): StateCast => $cast);

    expect($component->getStateToDehydrate(' 2024-06-01 to 2024-06-15 '))->toBe([
        'starts_at' => '2024-06-01',
        'ends_at' => '2024-06-15',
    ]);
});

it('dehydrates a carbon range start without an end when only one date is selected', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->time(true)
        ->format('Y-m-d H:i');

    $carbon = Carbon::parse('2024-06-14 15:00');

    expect($component->getStateToDehydrate($carbon))->toBe([
        'starts_at' => $carbon,
        'ends_at' => null,
    ])->and($carbon)->toBeInstanceOf(CarbonInterface::class);
});

it('returns null when resolving a blank range end state value', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    $method = new ReflectionMethod($component, 'resolveRangeEndStateValue');

    expect($method->invoke($component, null))->toBeNull()
        ->and($method->invoke($component, ''))->toBeNull();
});

it('resolves range end values from array picker state', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d');

    $method = new ReflectionMethod($component, 'resolveRangeEndStateValue');

    expect($method->invoke($component, ['2024-06-01', '2024-06-15']))->toBe('2024-06-15');
});

it('returns null when the range end value cannot be parsed', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d')
        ->rangeSeparator(' to ');

    $method = new ReflectionMethod($component, 'resolveRangeEndStateValue');

    expect($method->invoke($component, '2024-06-01 to not-a-date'))->toBeNull();
});

it('does not brute force split range strings when resolving the end field', function () {
    $component = Flatpickr::make('starts_at')
        ->rangePicker()
        ->rangeEnd('ends_at')
        ->format('Y-m-d')
        ->rangeSeparator('invalid');

    $method = new ReflectionMethod(Flatpickr::class, 'splitRangeString');
    $parts = $method->invoke(null, '2024-06-01', $component, false);

    expect($parts)->toBe(['2024-06-01']);
});

it('builds regex patterns for all supported datetime format tokens', function () {
    $method = new ReflectionMethod(Flatpickr::class, 'formatToRegexPattern');

    expect(preg_match($method->invoke(null, 'y-m-d'), '24-06-01'))->toBe(1)
        ->and(preg_match($method->invoke(null, 'n/j'), '6/5'))->toBe(1)
        ->and(preg_match($method->invoke(null, 'h:i:s'), '03:30:45'))->toBe(1)
        ->and(preg_match($method->invoke(null, 'g:i'), '3:30'))->toBe(1)
        ->and(preg_match($method->invoke(null, 'i:s.S'), '30:45.12'))->toBe(1)
        ->and(preg_match($method->invoke(null, 'Y/m/d'), '2024/06/01'))->toBe(1);
});

it('hydrates an existing combined range string without re-merging separate values', function () {
    [, $field] = RangeEndTestForm::mount([
        'starts_at' => '2024-06-01 to 2024-06-15',
        'ends_at' => '2024-06-20',
    ]);

    $field->callAfterStateHydrated();

    expect($field->getState())->toBe('2024-06-01 to 2024-06-15');
});

it('hydrates separate start and end database values into a combined range string', function () {
    [, $field] = RangeEndTestForm::mount([
        'starts_at' => '2024-06-01',
        'ends_at' => '2024-06-15',
    ]);

    $field->callAfterStateHydrated();

    expect($field->getState())->toBe('2024-06-01 to 2024-06-15');
});

it('hydrates only the start value when the end value is missing', function () {
    [, $field] = RangeEndTestForm::mount([
        'starts_at' => '2024-06-01',
    ]);

    $field->callAfterStateHydrated();

    expect($field->getState())->toBe('2024-06-01');
});

it('syncs the range end field when the picker state is updated', function () {
    [$livewire, $field] = RangeEndTestForm::mount([
        'starts_at' => null,
        'ends_at' => null,
    ]);

    $field->state('2024-06-01 to 2024-06-15');
    $field->callAfterStateUpdated(false);

    expect($livewire->data['ends_at'])->toBe('2024-06-15');
});

it('clears the range end field when the picker state is blank', function () {
    [$livewire, $field] = RangeEndTestForm::mount([
        'starts_at' => '2024-06-01 to 2024-06-15',
        'ends_at' => '2024-06-15',
    ]);

    $field->state('');
    $field->callAfterStateUpdated(false);

    expect($livewire->data['ends_at'])->toBeNull();
});

it('does not sync the range end field until both dates are selected', function () {
    [$livewire, $field] = RangeEndTestForm::mount([
        'starts_at' => null,
        'ends_at' => '2024-06-17 17:00',
    ], useDatetime: true);

    $field->state('2024-06-14 07:00');
    $field->callAfterStateUpdated(false);

    expect($livewire->data['ends_at'])->toBe('2024-06-17 17:00');
});

it('syncs datetime range end values without dropping the time component', function () {
    [$livewire, $field] = RangeEndTestForm::mount([
        'starts_at' => null,
        'ends_at' => null,
    ], useDatetime: true);

    $field->state('2024-06-14 07:00 to 2024-06-17 17:00');
    $field->callAfterStateUpdated(false);

    expect($livewire->data['ends_at'])->toBe('2024-06-17 17:00');
});
