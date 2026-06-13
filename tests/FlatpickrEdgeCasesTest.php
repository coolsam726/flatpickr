<?php

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Coolsam\Flatpickr\Tests\Support\ParseThrowingFlatpickr;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;
use Illuminate\Support\Facades\Validator;

function setFlatpickrNativeCoverage(StatefulFlatpickr $component, bool $native = true): StatefulFlatpickr
{
    $property = new ReflectionProperty($component, 'isNative');
    $property->setValue($component, $native);

    return $component;
}

it('hydrates range picker values from a single string', function () {
    $component = StatefulFlatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d')
        ->rangeSeparator(' to ');

    $component->hydrateFlatpickr($component, '2024-06-01');

    expect($component->hydratedState)->toBe('2024-06-01');
});

it('hydrates native collection values for time-only fields', function () {
    $component = setFlatpickrNativeCoverage(
        StatefulFlatpickr::make('times')
            ->multiplePicker()
            ->date(false)
            ->time(true)
            ->format('H:i')
            ->conjunction(','),
    );

    $component->hydrateFlatpickr($component, ['08:30', '09:15']);

    expect($component->hydratedState)->toBe('08:30,09:15');
});

it('hydrates native collection values for datetime fields with seconds', function () {
    $component = setFlatpickrNativeCoverage(
        StatefulFlatpickr::make('published_at')
            ->rangePicker()
            ->time(true)
            ->seconds()
            ->format('Y-m-d H:i:s')
            ->rangeSeparator(' to '),
    );

    $component->hydrateFlatpickr($component, [
        '2024-06-15 14:30:45',
        '2024-06-16 10:00:00',
    ]);

    expect($component->hydratedState)->toBe('2024-06-15 14:30:45 to 2024-06-16 10:00:00');
});

it('hydrates native collection values for datetime fields without seconds', function () {
    $component = setFlatpickrNativeCoverage(
        StatefulFlatpickr::make('published_at')
            ->rangePicker()
            ->time(true)
            ->format('Y-m-d H:i:s')
            ->rangeSeparator(' to '),
    );

    $component->hydrateFlatpickr($component, [
        '2024-06-15 14:30:00',
        '2024-06-16 10:00:00',
    ]);

    expect($component->hydratedState)->toBe('2024-06-15 14:30 to 2024-06-16 10:00');
});

it('preserves the original state when parseToCarbon throws for single values', function () {
    $component = ParseThrowingFlatpickr::make('date')->format('Y-m-d');

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-15');

    expect($result)->toBe('2024-06-15');
});

it('dehydrates native datetime range values using datetime strings', function () {
    $component = setFlatpickrNativeCoverage(
        StatefulFlatpickr::make('range')
            ->rangePicker()
            ->time(true)
            ->format('Y-m-d H:i:s')
            ->rangeSeparator(' to '),
    );

    $result = Flatpickr::dehydrateFlatpickr($component, '2024-06-15 14:30:00 to 2024-06-16 10:00:00');

    expect($result)->toBe(['2024-06-15 14:30:00', '2024-06-16 10:00:00']);
});

it('splits concatenated range strings using brute force when regex extraction fails', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('d/m/Y')
        ->rangeSeparator('not-in-string');

    $method = new ReflectionMethod(Flatpickr::class, 'splitRangeString');
    $parts = $method->invoke(null, '2024-06-012024-06-15', $component);

    expect($parts)->toHaveCount(2);
});

it('returns the configured dateFormat property value', function () {
    $component = Flatpickr::make('date');
    $property = new ReflectionProperty($component, 'dateFormat');
    $property->setValue($component, 'Y-m-d');

    expect($component->getDateFormat())->toBe('Y-m-d');
});

it('validates multiple picker array values as dates', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(
        ['dates' => ['2024-06-01', '2024-06-15']],
        ['dates' => [$closureRule]],
    );

    expect($validator->passes())->toBeTrue();
});

it('validates range picker array values as dates', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(
        ['range' => ['2024-06-01', '2024-06-15']],
        ['range' => [$closureRule]],
    );

    expect($validator->passes())->toBeTrue();
});

it('skips multiple picker validation when the value is null', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(['dates' => null], ['dates' => [$closureRule]]);

    expect($validator->passes())->toBeTrue();
});

it('skips range picker validation when the value is null', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(['range' => null], ['range' => [$closureRule]]);

    expect($validator->passes())->toBeTrue();
});
