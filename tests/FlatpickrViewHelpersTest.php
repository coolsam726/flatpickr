<?php

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Illuminate\View\ComponentAttributeBag;

it('reports native input types for date, time, and datetime fields', function () {
    expect(Flatpickr::make('date')->getType())->toBe('date')
        ->and(Flatpickr::make('start_time')->date(false)->time(true)->getType())->toBe('time')
        ->and(Flatpickr::make('published_at')->time(true)->getType())->toBe('datetime-local');
});

it('exposes view helper accessors for datalist, placeholder, and readonly state', function () {
    $component = Flatpickr::make('date')
        ->datalist(['2024-01-01'])
        ->placeholder('Pick a date')
        ->readOnly(true)
        ->autofocus(true)
        ->extraAlpineAttributes(['x-on:change' => 'changed()']);

    expect($component->getDatalistOptions())->toBe(['2024-01-01'])
        ->and($component->getStep())->toBeNull()
        ->and($component->getPlaceholder())->toBe('Pick a date')
        ->and($component->isReadOnly())->toBeTrue()
        ->and($component->isAutofocused())->toBeTrue()
        ->and($component->getExtraAlpineAttributes())->toBe(['x-on:change' => 'changed()'])
        ->and($component->getExtraInputAttributeBag())->toBeInstanceOf(ComponentAttributeBag::class);
});

it('exposes prefix and suffix view helpers', function () {
    $component = Flatpickr::make('date')
        ->prefixActions([])
        ->prefixIcon('heroicon-o-calendar')
        ->prefixIconColor('primary')
        ->prefixLabel('From')
        ->suffixActions([])
        ->suffixIcon('heroicon-o-clock')
        ->suffixIconColor('gray')
        ->suffixLabel('UTC');

    expect($component->getPrefixActions())->toBe([])
        ->and($component->getPrefixIcon())->toBe('heroicon-o-calendar')
        ->and($component->getPrefixIconColor())->toBe('primary')
        ->and($component->getPrefixLabel())->toBe('From')
        ->and($component->isPrefixInline())->toBeFalse()
        ->and($component->getSuffixActions())->toBe([])
        ->and($component->getSuffixIcon())->toBe('heroicon-o-clock')
        ->and($component->getSuffixIconColor())->toBe('gray')
        ->and($component->getSuffixLabel())->toBe('UTC')
        ->and($component->isSuffixInline())->toBeFalse();
});

it('reports native mode from the underlying property', function () {
    $component = Flatpickr::make('date');
    $property = new ReflectionProperty($component, 'isNative');
    $property->setValue($component, true);

    expect($component->isNative())->toBeTrue();
});
