<?php

namespace Coolsam\Flatpickr\Forms\Components;

use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Closure;
use Coolsam\Flatpickr\Enums\FlatpickrMode;
use Coolsam\Flatpickr\Enums\FlatpickrMonthSelectorType;
use Coolsam\Flatpickr\Enums\FlatpickrPosition;
use Coolsam\Flatpickr\Enums\FlatpickrTheme;
use Coolsam\Flatpickr\FilamentFlatpickr;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Flatpickr extends DateTimePicker
{
    /**
     * @phpstan-ignore-next-line
     */
    protected string $view = 'flatpickr::forms.components.flatpickr';

    protected bool | Closure $isNative = false;

    // Add all the following as protected properties: https://flatpickr.js.org/options/
    protected bool | Closure $altInput = true;

    protected string | Closure $altInputClass = '';

    protected bool | Closure $allowInput = false;

    protected bool | Closure $allowInvalidPreload = false;

    protected string | Closure | null $appendTo = null;

    protected string | Closure | null $ariaDateFormat = null;

    protected string | Closure $conjunction = ',';

    protected bool | Closure $clickOpens = true;

    protected string | Closure | null $dateFormat = null;

    protected string | Closure | array | null $defaultDate = null;

    protected int | Closure $defaultHour = 12;

    protected int | Closure $defaultMinute = 0;

    protected array | Closure | null $disableDates = null;

    protected bool | Closure $disableMobile = false;

    protected array | Closure | null $enableDates = null;

    protected int | Closure $hourIncrement = 1;

    protected bool | Closure $inline = false;

    protected int | Closure $minuteIncrement = 5;

    protected FlatpickrMode | Closure $mode = FlatpickrMode::SINGLE;

    protected bool | Closure $noCalendar = false;

    protected FlatpickrPosition | Closure $position = FlatpickrPosition::AUTO;

    protected string | Closure | null $prevArrow = null;

    protected string | Closure | null $nextArrow = null;

    protected bool | Closure $shorthandCurrentMonth = false;

    protected int | Closure $showMonths = 1;

    protected bool | Closure $time24hr = true;

    protected bool | Closure $hasTime = false;

    protected bool | Closure $weekNumbers = false;

    protected bool | Closure $weekPicker = false;

    protected bool | Closure $monthPicker = false;

    protected bool | Closure $rangePicker = false;

    protected bool | Closure $multiplePicker = false;

    protected bool | Closure $timePicker = false;

    protected FlatpickrMonthSelectorType | Closure $monthSelectorType = FlatpickrMonthSelectorType::DROPDOWN_SELECTOR;

    protected function parseToCarbon($state): ?CarbonInterface
    {
        $component = $this;

        try {
            if ($state instanceof CarbonInterface) {
                return $state->setTimezone($component->getTimezone());
            }
            $state = Carbon::createFromFormat($component->getFormat(), (string) $state, config('app.timezone'));

            return $state->setTimezone($component->getTimezone());
        } catch (InvalidFormatException $exception) {
            try {
                $state = Carbon::parse($state, config('app.timezone'));

                return $state->setTimezone(config('app.timezone'));
            } catch (InvalidFormatException $exception) {
                return null;
            }
        }
    }

    public function hydrateFlatpickr(Flatpickr $component, $state): void
    {
        if (blank($state)) {
            return;
        }

        if ($component->isMultiplePicker()) {
            $conjunction = $this->getConjunction();
            if (is_array($state)) {
                $state = collect($state)->map(static fn ($date) => $component->parseToCarbon($date));
            } else {
                $state = collect([$state])->map(static fn ($date) => $component->parseToCarbon($date));
            }
            $state = $state
                ->filter(static fn ($date) => $date instanceof CarbonInterface);
        } elseif ($component->isRangePicker()) {
            $conjunction = ' to ';
            if (is_array($state)) {
                $state = collect($state)->map(static fn ($date) => $component->parseToCarbon($date));
            } else {
                $state = collect([$state])->map(static fn ($date) => $component->parseToCarbon($date));
            }
            $state = $state
                ->filter(static fn ($date) => $date instanceof CarbonInterface)
                ->take(2);
        } else {
            $conjunction = null;
            $state = $component->parseToCarbon($state);
        }

        if ($state instanceof CarbonInterface) {
            if (! $component->isNative()) {
                $component->state($state->format($component->getFormat()));

                return;
            }

            if (! $component->hasTime()) {
                $component->state($state->toDateString());

                return;
            }

            $precision = $component->hasSeconds() ? 'second' : 'minute';

            if (! $component->hasDate()) {
                $component->state($state->toTimeString($precision));

                return;
            }

            $component->state($state->toDateTimeString($precision));
        } elseif ($state instanceof Collection) {
            $state = $state->map(function ($date) use ($component) {
                if (! $component->isNative()) {
                    return $date->format($component->getFormat());
                }
                if (! $component->hasTime()) {
                    return $date->toDateString();
                }
                $precision = $component->hasSeconds() ? 'second' : 'minute';
                if (! $component->hasDate()) {
                    return $date->toTimeString($precision);
                }

                return $date->toDateTimeString($precision);
            })
                ->implode($conjunction);
            $component->state($state);

            return;
        } else {
            $component->state(null);

            return;
        }
    }

    public static function dehydrateFlatpickr(Flatpickr $component, $state): array | CarbonInterface | null
    {
        if (blank($state)) {
            return null;
        }
        $component->rule(
            'date',
            static fn (
                Flatpickr $component
            ): bool => $component->isMultiplePicker() && ! $component->isRangePicker() && $component->hasDate(),
        );

        // try to convert the state to a Carbon instance
        if (! ($component->isMultiplePicker() || $component->isRangePicker())) {
            try {
                $res = $component->parseToCarbon($state);
                if ($res) {
                    $state = $res;
                }
            } catch (InvalidFormatException $exception) {
                // if it fails, return the state as is
                return $state;
            }
        }

        if (! $state instanceof CarbonInterface) {
            if (is_string($state)) {
                if ($component->isRangePicker()) {
                    $range = str($state)->explode(' to ');
                } elseif ($component->isMultiplePicker()) {
                    $range = str($state)->explode($component->getConjunction());
                } else {
                    $range = [$state];
                }

                return collect($range)->map(function ($date) use ($component) {
                    $date = $component->parseToCarbon($date)
                        ->setTimezone($component->getTimezone());

                    if (! $component->isNative()) {
                        return $date->format($component->getFormat());
                    }

                    if (! $component->hasTime()) {
                        return $date->toDateString();
                    }

                    $precision = $component->hasSeconds() ? 'second' : 'minute';

                    if (! $component->hasDate()) {
                        return $date->toTimeString($precision);
                    }

                    return $date->toDateTimeString();
                })
                    ->toArray();
            } else {
                return $state;
            }
        }

        return $state;
    }

    protected function setUp(): void
    {
        $this->afterStateHydrated(fn (Flatpickr $component, $state) => $component->hydrateFlatpickr(
            $component,
            $state
        ));

        $this->dehydrateStateUsing(fn (Flatpickr $component, $state) => $component::dehydrateFlatpickr(
            $component,
            $state
        ));
    }

    public function altFormat(Closure | string | null $altFormat): Flatpickr
    {
        $this->displayFormat($altFormat);

        return $this;
    }

    public function altInput(Closure | bool $altInput = true): Flatpickr
    {
        $this->altInput = $altInput;

        return $this;
    }

    public function altInputClass(Closure | string $altInputClass): Flatpickr
    {
        $this->altInputClass = $altInputClass;

        return $this;
    }

    public function allowInput(Closure | bool $allowInput = true): Flatpickr
    {
        $this->allowInput = $allowInput;

        return $this;
    }

    public function allowInvalidPreload(Closure | bool $allowInvalidPreload = true): Flatpickr
    {
        $this->allowInvalidPreload = $allowInvalidPreload;

        return $this;
    }

    public function appendTo(Closure | string | null $appendTo = null): Flatpickr
    {
        $this->appendTo = $appendTo;

        return $this;
    }

    public function ariaDateFormat(Closure | string | null $ariaDateFormat): Flatpickr
    {
        $this->ariaDateFormat = $ariaDateFormat;

        return $this;
    }

    public function conjunction(Closure | string $conjunction = ','): Flatpickr
    {
        $this->conjunction = $conjunction;

        return $this;
    }

    public function clickOpens(Closure | bool $clickOpens = true): Flatpickr
    {
        $this->clickOpens = $clickOpens;

        return $this;
    }

    public function maxDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->maxDate = $date;

        if (! $this->isMultiplePicker() && ! $this->isRangePicker()) {
            $this->rule(static function (DateTimePicker $component) {
                return "before_or_equal:{$component->getMaxDate()}";
            }, static fn (DateTimePicker $component): bool => (bool) $component->getMaxDate());
        }

        return $this;
    }

    public function minDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->minDate = $date;

        if (! $this->isMultiplePicker() && ! $this->isRangePicker()) {
            $this->rule(static function (DateTimePicker $component) {
                return "after_or_equal:{$component->getMinDate()}";
            }, static fn (DateTimePicker $component): bool => (bool) $component->getMinDate());
        }

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated use format() instead
     */
    public function dateFormat(Closure | string | null $dateFormat): Flatpickr
    {
        $this->format($dateFormat);

        return $this;
    }

    public function defaultDate(Closure | string | array | null $defaultDate): Flatpickr
    {
        $this->defaultDate = $defaultDate;

        return $this;
    }

    public function defaultHour(Closure | int $defaultHour): Flatpickr
    {
        $this->defaultHour = $defaultHour;

        return $this;
    }

    public function defaultMinute(Closure | int $defaultMinute): Flatpickr
    {
        $this->defaultMinute = $defaultMinute;

        return $this;
    }

    public function disableDates(Closure | array | null $disableDates = null): Flatpickr
    {
        $this->disableDates = $disableDates;

        return $this;
    }

    public function disableMobile(Closure | bool $disableMobile = true): Flatpickr
    {
        $this->disableMobile = $disableMobile;

        return $this;
    }

    public function enableDates(Closure | array | null $enableDates): Flatpickr
    {
        $this->enableDates = $enableDates;

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated use time() instead
     */
    public function enableTime(Closure | bool $enableTime = true): Flatpickr
    {
        $this->time($enableTime);

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated use seconds() instead
     */
    public function enableSeconds(Closure | bool $enableSeconds = true): Flatpickr
    {
        $this->seconds($enableSeconds);

        return $this;
    }

    public function hourIncrement(Closure | int $hourIncrement): Flatpickr
    {
        $this->hourIncrement = $hourIncrement;

        return $this;
    }

    public function minuteIncrement(Closure | int $minuteIncrement): Flatpickr
    {
        $this->minuteIncrement = $minuteIncrement;

        return $this;
    }

    public function mode(Closure | FlatpickrMode $mode): Flatpickr
    {
        $this->mode = $mode;

        return $this;
    }

    public function noCalendar(Closure | bool $noCalendar = true): Flatpickr
    {
        $this->noCalendar = $noCalendar;

        return $this;
    }

    public function position(Closure | FlatpickrPosition $position): Flatpickr
    {
        $this->position = $position;

        return $this;
    }

    public function prevArrow(Closure | string | null $prevArrow): Flatpickr
    {
        $this->prevArrow = $prevArrow;

        return $this;
    }

    public function nextArrow(Closure | string | null $nextArrow): Flatpickr
    {
        $this->nextArrow = $nextArrow;

        return $this;
    }

    public function shorthandCurrentMonth(Closure | bool $shorthandCurrentMonth = true): Flatpickr
    {
        $this->shorthandCurrentMonth = $shorthandCurrentMonth;

        return $this;
    }

    public function showMonths(Closure | int $showMonths = 2): Flatpickr
    {
        $this->showMonths = $showMonths;

        return $this;
    }

    public function time24hr(Closure | bool $time24hr = true): Flatpickr
    {
        $this->time24hr = $time24hr;

        return $this;
    }

    public function weekNumbers(Closure | bool $weekNumbers = true): Flatpickr
    {
        $this->weekNumbers = $weekNumbers;

        return $this;
    }

    public function monthSelectorType(Closure | FlatpickrMonthSelectorType $monthSelectorType): Flatpickr
    {
        $this->monthSelectorType = $monthSelectorType;

        return $this;
    }

    public function weekPicker(Closure | bool $weekPicker = true): Flatpickr
    {
        $this->weekPicker = $weekPicker;

        return $this;
    }

    public function monthPicker(Closure | bool $monthPicker = true): Flatpickr
    {
        $this->monthPicker = $monthPicker;

        return $this;
    }

    public function rangePicker(Closure | bool $rangePicker = true): Flatpickr
    {
        $this->rangePicker = $rangePicker;

        return $this;
    }

    public function multiplePicker(Closure | bool $multiplePicker = true): Flatpickr
    {
        $this->multiplePicker = $multiplePicker;

        return $this;
    }

    public function timePicker(Closure | bool $timePicker = true): Flatpickr
    {
        $this->timePicker = $timePicker;
        $this->time($timePicker);
        $this->noCalendar($timePicker);

        return $this;
    }

    public function inline(Closure | bool $inline = true): Flatpickr
    {
        $this->inline = $inline;

        return $this;
    }

    // Getters

    public function getThemeAsset(): string
    {
        /**
         * @var FlatpickrTheme $theme
         */
        $theme = Config::get('flatpickr.theme', FlatpickrTheme::DEFAULT);

        return $theme->getAsset();
    }

    public function getLightThemeAsset(): string
    {
        return FlatpickrTheme::LIGHT->getAsset();
    }

    public function getDarkThemeAsset(): string
    {
        return FlatpickrTheme::DARK->getAsset();
    }

    /**
     * @deprecated use getDisplayFormat() instead
     */
    public function getAltFormat(): ?string
    {
        return $this->getDisplayFormat();
    }

    public function getAltInput(): bool
    {
        return $this->evaluate($this->altInput);
    }

    public function getAltInputClass(): string
    {
        return $this->evaluate($this->altInputClass);
    }

    public function getAllowInput(): bool
    {
        return $this->evaluate($this->allowInput);
    }

    public function getAllowInvalidPreload(): bool
    {
        return $this->evaluate($this->allowInvalidPreload);
    }

    public function getAppendTo(): ?string
    {
        return $this->evaluate($this->appendTo);
    }

    public function getAriaDateFormat(): ?string
    {
        return $this->evaluate($this->ariaDateFormat);
    }

    public function getConjunction(): string
    {
        return $this->evaluate($this->conjunction) ?? ',';
    }

    public function getClickOpens(): bool
    {
        return $this->evaluate($this->clickOpens);
    }

    /**
     * @deprecated use getFormat() instead
     */
    public function getDateFormat(): ?string
    {
        return $this->evaluate($this->dateFormat);
    }

    public function getDefaultDate(): string | array | null
    {
        return $this->evaluate($this->defaultDate);
    }

    public function getDefaultHour(): int
    {
        return $this->evaluate($this->defaultHour);
    }

    public function getDefaultMinute(): int
    {
        return $this->evaluate($this->defaultMinute);
    }

    public function getDisableDates(): ?array
    {
        return $this->evaluate($this->disableDates);
    }

    public function getDisableMobile(): bool
    {
        return $this->evaluate($this->disableMobile);
    }

    public function getEnableDates(): ?array
    {
        return $this->evaluate($this->enableDates);
    }

    /**
     * @deprecated use hasTime() instead
     */
    public function getEnableTime(): bool
    {
        return $this->hasTime();
    }

    /**
     * @deprecated Use hasSeconds() instead
     */
    public function getEnableSeconds(): bool
    {
        return $this->hasSeconds();
    }

    public function getHourIncrement(): int
    {
        return $this->evaluate($this->hourIncrement);
    }

    public function getMinuteIncrement(): int
    {
        return $this->evaluate($this->minuteIncrement);
    }

    public function getMode(): FlatpickrMode
    {
        return $this->evaluate($this->mode);
    }

    public function hasNoCalendar(): bool
    {
        return $this->evaluate($this->noCalendar);
    }

    public function getPosition(): FlatpickrPosition
    {
        return $this->evaluate($this->position);
    }

    public function getPrevArrow(): ?string
    {
        return $this->evaluate($this->prevArrow);
    }

    public function getNextArrow(): ?string
    {
        return $this->evaluate($this->nextArrow);
    }

    public function getShorthandCurrentMonth(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->shorthandCurrentMonth));
    }

    public function getShowMonths(): int
    {
        return FilamentFlatpickr::getInt($this->evaluate($this->showMonths));
    }

    public function getTime24hr(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->time24hr));
    }

    public function getWeekNumbers(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->weekNumbers));
    }

    public function getMonthSelectorType(): FlatpickrMonthSelectorType
    {
        return $this->evaluate($this->monthSelectorType);
    }

    public function isTimePicker(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->timePicker)) || ($this->hasNoCalendar() && $this->hasTime());
    }

    public function isWeekPicker(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->weekPicker));
    }

    public function isMonthPicker(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->monthPicker));
    }

    public function isRangePicker(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->rangePicker) || $this->getMode()->value === FlatpickrMode::RANGE->value);
    }

    public function isMultiplePicker(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->multiplePicker) || $this->getMode()->value === FlatpickrMode::MULTIPLE->value);
    }

    public function isInline(): bool
    {
        return FilamentFlatpickr::getBool($this->evaluate($this->inline));
    }

    public function getFlatpickrAttributes(): array
    {
        $attrs = collect();
        if (filled($this->getDisplayFormat())) {
            $attrs->put('altFormat', $this->getDisplayFormat());
        }
        if (filled($this->getAltInput())) {
            $attrs->put('altInput', FilamentFlatpickr::getBool($this->getAltInput()));
        }

        if (filled($this->getAltInputClass())) {
            $attrs->put('altInputClass', $this->getAltInputClass());
        }

        if (filled($this->getAllowInput())) {
            $attrs->put('allowInput', FilamentFlatpickr::getBool($this->getAllowInput()));
        }

        if (filled($this->getAllowInvalidPreload())) {
            $attrs->put('allowInvalidPreload', FilamentFlatpickr::getBool($this->getAllowInvalidPreload()));
        }

        if (filled($this->getAppendTo())) {
            $attrs->put('appendTo', $this->getAppendTo());
        }

        if (filled($this->getAriaDateFormat())) {
            $attrs->put('ariaDateFormat', $this->getAriaDateFormat());
        }

        if (filled($this->getConjunction())) {
            $attrs->put('conjunction', $this->getConjunction());
        }

        if (filled($this->getClickOpens())) {
            $attrs->put('clickOpens', FilamentFlatpickr::getBool($this->getClickOpens()));
        }

        if (filled($this->getFormat())) {
            $attrs->put('dateFormat', $this->getFormat());
        }

        if (filled($this->getDefaultDate())) {
            $attrs->put('defaultDate', $this->getDefaultDate());
        }

        if (filled($this->getDefaultHour())) {
            $attrs->put('defaultHour', FilamentFlatpickr::getInt($this->getDefaultHour()));
        }

        if (filled($this->getDefaultMinute())) {
            $attrs->put('defaultMinute', FilamentFlatpickr::getInt($this->getDefaultMinute()));
        }

        if (filled($this->getDisableDates())) {
            $attrs->put('disable', $this->getDisableDates());
        }

        if (filled($this->getDisableMobile())) {
            $attrs->put('disableMobile', FilamentFlatpickr::getBool($this->getDisableMobile()));
        }

        if (filled($this->getEnableDates())) {
            $attrs->put('enable', $this->getEnableDates());
        }

        if (filled($this->hasTime())) {
            $attrs->put('enableTime', FilamentFlatpickr::getBool($this->hasTime()));
        }

        if (filled($this->hasSeconds())) {
            $attrs->put('enableSeconds', FilamentFlatpickr::getBool($this->hasSeconds()));
        }

        if (filled($this->getHourIncrement())) {
            $attrs->put('hourIncrement', FilamentFlatpickr::getInt($this->getHourIncrement()));
        }

        if (filled($this->getMinuteIncrement())) {
            $attrs->put('minuteIncrement', FilamentFlatpickr::getInt($this->getMinuteIncrement()));
        }

        if (filled($this->getMode())) {
            $attrs->put('mode', $this->getMode()->value);
        }

        if (filled($this->isRangePicker())) {
            $attrs->put('rangePicker', ($isRange = FilamentFlatpickr::getBool($this->isRangePicker())));

            if ($isRange) {
                $attrs->put('mode', FlatpickrMode::RANGE->value);
            }
        }

        if (filled($this->isMultiplePicker())) {
            $attrs->put('multiplePicker', ($isMultiple = FilamentFlatpickr::getBool($this->isMultiplePicker())));
            if ($isMultiple) {
                $attrs->put('mode', FlatpickrMode::MULTIPLE->value);
            }
        }

        if (filled($this->hasNoCalendar())) {
            $attrs->put('noCalendar', FilamentFlatpickr::getBool($this->hasNoCalendar()));
        }

        if (filled($this->getPosition())) {
            $attrs->put('position', $this->getPosition()->value);
        }

        if (filled($this->getPrevArrow())) {
            $attrs->put('prevArrow', $this->getPrevArrow());
        }

        if (filled($this->getNextArrow())) {
            $attrs->put('nextArrow', $this->getNextArrow());
        }

        if (filled($this->getShorthandCurrentMonth())) {
            $attrs->put('shorthandCurrentMonth', FilamentFlatpickr::getBool($this->getShorthandCurrentMonth()));
        }

        if (filled($this->getShowMonths())) {
            $attrs->put('showMonths', FilamentFlatpickr::getInt($this->getShowMonths()));
        }

        if (filled($this->getTime24hr())) {
            $attrs->put('time_24hr', FilamentFlatpickr::getBool($this->getTime24hr()));
        }

        if (filled($this->getWeekNumbers())) {
            $attrs->put('weekNumbers', FilamentFlatpickr::getBool($this->getWeekNumbers()));
        }

        if (filled($this->getMonthSelectorType())) {
            $attrs->put('monthSelectorType', $this->getMonthSelectorType()->value);
        }

        if (filled($this->isWeekPicker())) {
            $attrs->put('weekPicker', FilamentFlatpickr::getBool($this->isWeekPicker()));
        }

        if (filled($this->isMonthPicker())) {
            $attrs->put('monthPicker', FilamentFlatpickr::getBool($this->isMonthPicker()));
        }
        if (filled($this->getLocale())) {
            $attrs->put('locale', $this->getLocale());
        }
        if (filled($this->isTimePicker())) {
            $isTimePicker = FilamentFlatpickr::getBool($this->isTimePicker());
            if ($isTimePicker) {
                $attrs->put('timePicker', true);
                $attrs->put('noCalendar', true);
                $attrs->put('enableTime', true);
            }

            if ($isTimePicker && (! $this->getFormat() || str($this->getFormat())->contains(['Y', 'm', 'd']))) {
                $attrs->put('dateFormat', $this->hasSeconds() ? 'H:i:S' : 'H:i');
                $attrs->put('altFormat', $this->hasSeconds() ? 'h:i:S K' : 'h:i K');
            }
        }

        if (filled($this->getMinDate())) {
            $attrs->put('minDate', $this->getMinDate());
        }

        if (filled($this->getMaxDate())) {
            $attrs->put('maxDate', $this->getMaxDate());
        }

        if (filled($this->isInline())) {
            $attrs->put('inline', $this->isInline());
        }

//        $this->dispatchEvent('attributes-updated', id: $this->getId());

        return $attrs->toArray();
    }
}
