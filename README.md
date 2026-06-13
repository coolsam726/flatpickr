# Filament Flatpickr

<p align="center">
    <a href="https://github.com/coolsam726/flatpickr/actions/workflows/run-tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/coolsam726/flatpickr/run-tests.yml?branch=main&label=CI&style=for-the-badge&logo=github" alt="CI"></a>
    <a href="https://codecov.io/gh/coolsam726/flatpickr"><img src="https://img.shields.io/codecov/c/github/coolsam726/flatpickr/main?style=for-the-badge&logo=codecov" alt="Coverage"></a>
    <a href="https://packagist.org/packages/coolsam/flatpickr"><img src="https://img.shields.io/packagist/v/coolsam/flatpickr?style=for-the-badge&logo=packagist&logoColor=white" alt="Latest Version on Packagist"></a>
    <a href="https://packagist.org/packages/coolsam/flatpickr"><img src="https://img.shields.io/packagist/dt/coolsam/flatpickr?style=for-the-badge&logo=packagist&logoColor=white" alt="Total Downloads"></a>
</p>

<p align="center">
    A Filament form field powered by <a href="https://flatpickr.js.org/">Flatpickr</a> — date, time, range, week, month, year, and multi-date picking with a fluent API.
</p>

<p align="center">
    <img src="https://github.com/user-attachments/assets/334ea64a-48c3-48bc-a640-72162802a646" alt="Filament Flatpickr preview">
</p>

## Supported versions

| Package | Filament | Laravel | PHP |
|---------|----------|---------|-----|
| **v5.x** (current) | 4.x, 5.x | 11.x – 13.x | 8.2 – 8.5 (PHP 8.5 from Laravel 12+; Laravel 13 from PHP 8.3+) |
| v4.x | 3.x | 10.x – 11.x | 8.1 – 8.3 |
| v2.x | 2.x | 9.x – 10.x | 8.0 – 8.2 |

## Installation

Install the package with Composer:

```bash
composer require coolsam/flatpickr
```

Publish assets and configuration:

```bash
php artisan flatpickr:install
```

This publishes `config/flatpickr.php` and assets to `public/vendor/flatpickr`. You will be prompted to overwrite existing files when upgrading.

After upgrading, refresh Filament assets:

```bash
php artisan filament:upgrade
```

## Quick start

```php
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

Flatpickr::make('published_at')
    ->format('Y-m-d')
    ->minDate(today()->startOfYear())
    ->maxDate(today());
```

## Picker modes

One component covers every Flatpickr mode you need:

| Mode | Helper | Typical format |
|------|--------|----------------|
| Date | `Flatpickr::make('date')` | `Y-m-d` |
| Date & time | `->time(true)` or `->timePicker()` | `Y-m-d H:i` / `H:i` |
| Range | `->rangePicker()` | array of date strings, or two fields with `->rangeEnd()` |
| Multiple dates | `->multiplePicker()` | array of date strings |
| Week | `->weekPicker()` | `W Y` |
| Month | `->monthPicker()` | `Y-m` |
| Year | `->yearPicker()` | `Y` |

### Short examples

```php
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

Flatpickr::make('start_time')->timePicker();
Flatpickr::make('week_number')->weekPicker()->format('W Y');
Flatpickr::make('month')->monthPicker()->format('Y-m')->displayFormat('F Y');
Flatpickr::make('year')->yearPicker();
Flatpickr::make('range')->rangePicker();
Flatpickr::make('starts_at')->rangePicker()->rangeEnd('ends_at')->format('Y-m-d');
Flatpickr::make('occupied_slots')->multiplePicker()->format('Y-m-d')->displayFormat('F j, Y');
```

## Configuration

Most fluent methods mirror [Flatpickr's options](https://flatpickr.js.org/options/). The API is inspired by [Filament's date/time fields](https://filamentphp.com/docs/4.x/forms/overview) and works as a drop-in alternative with Flatpickr-specific behaviour.

```php
use Coolsam\Flatpickr\Enums\FlatpickrMode;
use Coolsam\Flatpickr\Enums\FlatpickrMonthSelectorType;
use Coolsam\Flatpickr\Enums\FlatpickrPosition;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

Flatpickr::make('event_date')
    ->format('Y-m-d')
    ->displayFormat('F j, Y')
    ->allowInput()
    ->altInput()
    ->minDate(fn () => today()->startOfYear())
    ->maxDate(fn () => today())
    ->disableDates(['2024-07-25', '2024-07-26'])
    ->rangeSeparator(' to ')
    ->conjunction(',')
    ->hourIncrement(1)
    ->minuteIncrement(10)
    ->seconds(false)
    ->weekNumbers()
    ->time24hr()
    ->inline()
    ->disableMobile()
    ->mode(FlatpickrMode::RANGE) // or ->rangePicker(), ->multiplePicker()
    ->monthSelectorType(FlatpickrMonthSelectorType::DROPDOWN)
    ->position(FlatpickrPosition::AUTO_CENTER)
    ->showMonths(2)
    ->timePicker()
    ->weekPicker()
    ->monthPicker()
    ->yearPicker()
    ->rangePicker()
    ->multiplePicker();
```

See the [Flatpickr documentation](https://flatpickr.js.org/options/) for details on each option.

## State types

| Picker | Dehydrated state |
|--------|------------------|
| Date, time, week, month, year | `string` or `CarbonInterface` |
| Range, multiple | `array` of date strings or `CarbonInterface` instances |
| Range with `->rangeEnd('ends_at')` | Two separate fields: start and end strings or `CarbonInterface` instances |

### Split range across two fields

When your model uses separate `starts_at` and `ends_at` columns, bind the range picker to the start field and name the end field explicitly. One picker is shown; both attributes are hydrated, synced live, and dehydrated on save.

```php
Flatpickr::make('starts_at')
    ->label('Event dates')
    ->rangePicker()
    ->rangeEnd('ends_at')
    ->format('Y-m-d');
```

Do not add a second Flatpickr on `ends_at`. Validation rules on `ends_at` (for example `after:starts_at`) still work because the end value is kept in sync while the user selects a range.

#### Date & time range

Use `->time(true)` with a format that includes hours and minutes. `displayFormat()` controls what the user sees in the input (Flatpickr tokens, not PHP `date()` tokens). Storage and dehydration still use `format()`.

```php
Flatpickr::make('starts_at')
    ->label('Event schedule')
    ->rangePicker()
    ->rangeEnd('ends_at')
    ->time(true)
    ->format('Y-m-d H:i')              // saved to starts_at / ends_at
    ->displayFormat('M j, Y h:i K')    // e.g. Jun 14, 2024 7:00 AM to Jun 17, 2024 5:00 PM
    ->rangeSeparator(' to ');
```

Ensure your model casts both columns as `datetime`. The picker UI lets you choose a date and time for each end of the range in one calendar.

See [RFC 0001](rfcs/0001-split-range-end-field.md) for the full design.

## Themes

Set the global theme in `config/flatpickr.php` using a `FlatpickrTheme` enum value:

```php
use Coolsam\Flatpickr\Enums\FlatpickrTheme;

return [
    'theme' => FlatpickrTheme::DEFAULT, // recommended
];
```

> **Recommendation:** Use the **DEFAULT** theme. It is styled with Tailwind to match Filament, including dark mode. Bundled Flatpickr themes may not align with your panel styling.

Theme previews are included in the [screenshots](#theme-gallery) below.

## Screenshots

### Picker modes

#### Single date

![Single date picker](https://github.com/user-attachments/assets/015ae745-96bd-4b5a-990a-11bba852aa14)

#### Multiple dates

![Multiple date picker](https://github.com/user-attachments/assets/d896aa05-7907-4957-8d46-1d51d1393b91)

#### Date range

![Date range picker](https://github.com/user-attachments/assets/3bcac5ad-5bfc-4a33-a320-3027c1e6a086)

#### Date & time

![Date-time picker](https://github.com/user-attachments/assets/1529a743-1c03-46b9-b0f5-0076e0a6b7e3)

#### Time only

![Time-only picker](https://github.com/user-attachments/assets/f6bab802-6d9a-468e-a6fc-b8fd74454656)

#### Multiple months

![Show multiple months](https://github.com/user-attachments/assets/0ea2e9f2-22df-45d3-a3d1-6430f283e6e0)

#### Week

![Week picker](https://github.com/user-attachments/assets/ea648d29-1bc2-46b7-9d82-301f500fab78)

#### Month

![Month picker](https://github.com/user-attachments/assets/e542c3d7-08ac-411d-874b-7ae0718ea000)

### Theme gallery

#### Default (recommended)

![Default theme](https://github.com/user-attachments/assets/ee615ae7-9956-45d6-a4d1-48054babf16c)

#### Airbnb

![Airbnb theme](https://github.com/user-attachments/assets/6ec6e97d-e8ce-4d93-b27b-21fcead8d644)

#### Light

![Light theme](https://github.com/user-attachments/assets/fa190cb6-1bb4-4175-8733-bf261350c29c)

#### Dark

![Dark theme](https://github.com/user-attachments/assets/ddd59f71-5fdc-469f-91be-37e7c4e67fb9)

#### Confetti

![Confetti theme](https://github.com/user-attachments/assets/2c76e329-678c-4443-ab42-ab4fd7230320)

#### Material Blue

![Material Blue theme](https://github.com/user-attachments/assets/db5074f8-22ad-493d-84e1-8e505f0c55be)

#### Material Green

![Material Green theme](https://github.com/user-attachments/assets/9d843720-4ef0-4768-ad71-1975a94922e0)

#### Material Orange

![Material Orange theme](https://github.com/user-attachments/assets/74021b21-2244-448b-8177-04e888db82c9)

#### Material Red

![Material Red theme](https://github.com/user-attachments/assets/936fbdc7-f73a-437d-88e4-c333a193a72b)

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for release notes.

## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING.md) for guidelines.

## Security

Report vulnerabilities according to [our security policy](../../security/policy).

## Credits

- [Savannabits](https://github.com/savannabits)
- [All Contributors](../../contributors)

## License

The MIT License. See [LICENSE.md](LICENSE.md).
