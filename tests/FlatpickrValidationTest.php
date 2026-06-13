<?php

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Illuminate\Support\Facades\Validator;

it('registers a date rule for single date fields', function () {
    $component = Flatpickr::make('date')->format('Y-m-d');

    expect($component->getValidationRules())->toContain('date');
});

it('does not register the date rule for range pickers', function () {
    $component = Flatpickr::make('range')->rangePicker()->format('Y-m-d');

    expect($component->getValidationRules())->not->toContain('date');
});

it('validates multiple picker values as dates', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $rules = $component->getValidationRules();
    $closureRule = collect($rules)->first(fn ($rule) => $rule instanceof Closure);

    expect($closureRule)->toBeInstanceOf(Closure::class);

    $validator = Validator::make(
        ['dates' => '2024-06-01,not-a-date'],
        ['dates' => [$closureRule]],
    );

    expect($validator->fails())->toBeTrue();
});

it('validates range picker values as dates', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $rules = $component->getValidationRules();
    $closureRule = collect($rules)->first(fn ($rule) => $rule instanceof Closure);

    expect($closureRule)->toBeInstanceOf(Closure::class);

    $validator = Validator::make(
        ['range' => '2024-06-01 to invalid'],
        ['range' => [$closureRule]],
    );

    expect($validator->fails())->toBeTrue();
});

it('registers min and max date rules for single date fields', function () {
    $component = Flatpickr::make('date')
        ->format('Y-m-d')
        ->minDate('2024-01-01')
        ->maxDate('2024-12-31');

    $rules = collect($component->getValidationRules())
        ->filter(fn ($rule) => is_string($rule))
        ->values()
        ->all();

    expect($rules)->toContain('after_or_equal:2024-01-01')
        ->and($rules)->toContain('before_or_equal:2024-12-31');
});

it('does not register min and max date rules for range pickers', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d')
        ->minDate('2024-01-01')
        ->maxDate('2024-12-31');

    $stringRules = collect($component->getValidationRules())
        ->filter(fn ($rule) => is_string($rule))
        ->values()
        ->all();

    expect($stringRules)->not->toContain('after_or_equal:2024-01-01')
        ->and($stringRules)->not->toContain('before_or_equal:2024-12-31');
});

it('skips multiple picker validation when the value is blank', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(['dates' => ''], ['dates' => [$closureRule]]);

    expect($validator->passes())->toBeTrue();
});

it('skips range picker validation when the value is blank', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(['range' => ''], ['range' => [$closureRule]]);

    expect($validator->passes())->toBeTrue();
});

it('accepts valid multiple picker values', function () {
    $component = Flatpickr::make('dates')
        ->multiplePicker()
        ->format('Y-m-d')
        ->conjunction(',');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(
        ['dates' => '2024-06-01,2024-06-15'],
        ['dates' => [$closureRule]],
    );

    expect($validator->passes())->toBeTrue();
});

it('accepts valid range picker values', function () {
    $component = Flatpickr::make('range')
        ->rangePicker()
        ->format('Y-m-d');

    $closureRule = collect($component->getValidationRules())
        ->first(fn ($rule) => $rule instanceof Closure);

    $validator = Validator::make(
        ['range' => '2024-06-01 to 2024-06-15'],
        ['range' => [$closureRule]],
    );

    expect($validator->passes())->toBeTrue();
});
