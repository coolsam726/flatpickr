<?php

use Coolsam\Flatpickr\Enums\FlatpickrMode;
use Coolsam\Flatpickr\Enums\FlatpickrMonthSelectorType;
use Coolsam\Flatpickr\Enums\FlatpickrPosition;
use Coolsam\Flatpickr\Enums\FlatpickrTheme;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Coolsam\Flatpickr\Tests\Support\StatefulFlatpickr;
use Illuminate\Support\Carbon;

it('maps configured options to flatpickr attributes', function () {
    $component = StatefulFlatpickr::make('field')
        ->displayFormat('d/m/Y')
        ->altInput(true)
        ->altInputClass('custom-class')
        ->allowInput(true)
        ->allowInvalidPreload(true)
        ->appendTo('#app')
        ->ariaDateFormat('F j, Y')
        ->conjunction('|')
        ->rangeSeparator(' - ')
        ->clickOpens(false)
        ->format('Y-m-d')
        ->defaultDate('2024-01-01')
        ->defaultHour(9)
        ->defaultMinute(30)
        ->disableDates(['2024-12-25'])
        ->disableMobile(true)
        ->enableDates(['2024-01-01'])
        ->time(true)
        ->seconds(true)
        ->hourIncrement(2)
        ->minuteIncrement(15)
        ->mode(FlatpickrMode::SINGLE)
        ->position(FlatpickrPosition::ABOVE)
        ->prevArrow('<')
        ->nextArrow('>')
        ->shorthandCurrentMonth(true)
        ->showMonths(2)
        ->time24hr(false)
        ->weekNumbers(true)
        ->monthSelectorType(FlatpickrMonthSelectorType::STATIC_SELECTOR)
        ->inline(true)
        ->locale('en')
        ->minDate('2024-01-01')
        ->maxDate('2024-12-31');

    $attributes = $component->getFlatpickrAttributes();

    expect($attributes)->toMatchArray([
        'altFormat' => 'd/m/Y',
        'altInput' => true,
        'altInputClass' => 'custom-class',
        'allowInput' => true,
        'allowInvalidPreload' => true,
        'appendTo' => '#app',
        'ariaDateFormat' => 'F j, Y',
        'conjunction' => '|',
        'rangeSeparator' => ' - ',
        'clickOpens' => false,
        'dateFormat' => 'Y-m-d',
        'defaultDate' => '2024-01-01',
        'defaultHour' => 9,
        'defaultMinute' => 30,
        'disable' => ['2024-12-25'],
        'disableMobile' => true,
        'enable' => ['2024-01-01'],
        'enableTime' => true,
        'enableSeconds' => true,
        'hourIncrement' => 2,
        'minuteIncrement' => 15,
        'mode' => FlatpickrMode::SINGLE->value,
        'position' => FlatpickrPosition::ABOVE->value,
        'prevArrow' => '<',
        'nextArrow' => '>',
        'shorthandCurrentMonth' => true,
        'showMonths' => 2,
        'time_24hr' => false,
        'weekNumbers' => true,
        'monthSelectorType' => FlatpickrMonthSelectorType::STATIC_SELECTOR->value,
        'inline' => true,
        'locale' => 'en',
        'minDate' => '2024-01-01',
        'maxDate' => '2024-12-31',
    ]);
});

it('maps year picker attributes including alt format fallback', function () {
    $component = Flatpickr::make('year')->yearPicker()->displayFormat('Y');

    expect($component->getFlatpickrAttributes())->toMatchArray([
        'yearPicker' => true,
        'dateFormat' => 'Y',
        'altFormat' => 'Y',
    ]);
});

it('maps time picker attributes when format still contains date tokens', function () {
    $component = Flatpickr::make('start_time')->timePicker()->format('Y-m-d H:i');

    expect($component->getFlatpickrAttributes())->toMatchArray([
        'timePicker' => true,
        'noCalendar' => true,
        'enableTime' => true,
        'dateFormat' => 'H:i',
        'altFormat' => 'h:i K',
    ]);
});

it('maps time picker attributes with seconds when format still contains date tokens', function () {
    $component = Flatpickr::make('start_time')->timePicker()->seconds()->format('Y-m-d H:i:s');

    expect($component->getFlatpickrAttributes())->toMatchArray([
        'dateFormat' => 'H:i:S',
        'altFormat' => 'h:i:S K',
    ]);
});

it('exposes theme asset helpers from config and enum cases', function () {
    config(['flatpickr.theme' => FlatpickrTheme::DARK]);

    $component = Flatpickr::make('date');

    expect($component->getThemeAsset())->toContain('dark.css')
        ->and($component->getLightThemeAsset())->toContain('light.css')
        ->and($component->getDarkThemeAsset())->toContain('dark.css');
});

it('resolves min and max dates from carbon instances', function () {
    $component = Flatpickr::make('date')
        ->minDate(Carbon::parse('2024-01-01'))
        ->maxDate(Carbon::parse('2024-12-31'));

    expect($component->getMinDate())->toBe('2024-01-01')
        ->and($component->getMaxDate())->toBe('2024-12-31');
});

it('supports fluent aliases for format and time configuration', function () {
    $component = Flatpickr::make('date')
        ->altFormat('d/m/Y')
        ->dateFormat('Y-m-d')
        ->enableTime(true)
        ->enableSeconds(true);

    expect($component->getAltFormat())->toBe('d/m/Y')
        ->and($component->getFormat())->toBe('Y-m-d')
        ->and($component->getEnableTime())->toBeTrue()
        ->and($component->getEnableSeconds())->toBeTrue();
});

it('defaults format for datetime-only fields', function () {
    $component = Flatpickr::make('published_at')->date(false)->time(true);

    expect($component->getFormat())->toBe('H:i');
});

it('defaults format for datetime fields with seconds', function () {
    $component = Flatpickr::make('published_at')->time(true)->seconds();

    expect($component->getFormat())->toBe('Y-m-d H:i:s');
});

it('uses the configured timezone with app fallback', function () {
    config(['app.timezone' => 'UTC']);

    $component = Flatpickr::make('date')->timezone('Europe/Paris');

    expect($component->getTimezone())->toBe('Europe/Paris')
        ->and(Flatpickr::make('date')->getTimezone())->toBe('UTC');
});
