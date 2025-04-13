<p align="center">
    <a href="https://github.com/coolsam726/flatpickr/actions?query=workflow%3Arun-tests+branch%3Amain"><img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/coolsam726/flatpickr/run-tests.yml?branch=main&label=tests&style=for-the-badge&logo=github"></a>
    <a href="https://github.com/coolsam726/flatpickr/actions?query=workflow%php-code-styling+branch%3Amain"><img alt="Styling" src="https://img.shields.io/github/actions/workflow/status/coolsam726/flatpickr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=for-the-badge&logo=github"></a>
    <a href="https://laravel.com"><img alt="Laravel v9.x" src="https://img.shields.io/badge/Laravel-v12.x-FF2D20?style=for-the-badge&logo=laravel"></a>
    <a href="https://filamentphp.com"><img alt="Filament v3.x" src="https://img.shields.io/badge/FilamentPHP-v3.x-FB70A9?style=for-the-badge&logo=filament"></a>
    <a href="https://php.net"><img alt="PHP 8.1" src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php"></a>
    <a href="https://packagist.org/packages/coolsam/flatpickr"><img alt="Packagist" src="https://img.shields.io/packagist/dt/coolsam/flatpickr.svg?style=for-the-badge&logo=count"></a>
</p>

Use **[Flatpickr](https://flatpickr.js.org/)** as your datepicker in the Filament Forms and Panels.

## Supported Versions
| Package Version | Supported Filament Version(s) |
|------------------|------------------------------|
| v2.x             | Filament v2                  |
| v3.x             | Filament v3                  |
| v4.x             | Filament v3, Filament v4     |

## Installation

Install the package via composer:

```bash
composer require coolsam/flatpickr
```

```bash
php artisan flatpickr:install
```

If you are upgrading from a previous version be sure to run the following to ensure assets are up to date
```bash
php artisan filament:upgrade
```

## Usage
You can do a lot with just one Component: `Flatpickr`
You can use the Flatpickr component from this package as:
* DatePicker
* TimePicker
* DateTimePicker
* Range Picker
* Week Picker,
* Multiple-Date Picker
* Month Picker

Most of the fluent config methods are similar to [Flatpickr's official](https://flatpickr.js.org/options/) options in naming.

This package is also an extension of [Filament's DateTimePicker](https://filamentphp.com/docs/3.x/forms/fields/date-time-picker), so most of the methods are similar to the ones in the DateTimePicker component. You can use the Flatpickr component as a drop-in replacement for the DateTimePicker component.

Here are some examples of the methods. Refer to Flatpickr's Official Documentation for details on each of the configurations.

```php
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

// Basic, Date Field
Flatpickr::make('test_field') // Minimal Config as a datepicker
Flatpickr::make('test_field')
    ->allowInput() // Allow a user to manually input the date in the textbox (make the textbox editable)
    ->altInput(true) // Enable the use of Alternative Input (See Flatpickr docs)
    ->altFormat('F j, Y') // Alternative input format
    ->time(true) // Turn this into a DateTimePicker
    ->disabledDates(['2023-07-25','2023-07-26']) // Disable specific dates from being selected.
    ->minDate(fn() => today()->startOfYear()) // Set the minimum allowed date
    ->maxDate(fn() => today()) // Set the maximum allowed date.
    ->hourIncrement(1) // Intervals of incrementing hours in a time picker
    ->minuteIncrement(10) // Intervals of minute increment in a time picker
    ->seconds(false) // Enable seconds in a time picker
    ->defaultSeconds(0) //Initial value of the seconds element, when no date is selected 
    ->defaultMinute(0) // Initial value of the minutes element, when no date is selected
    ->allowInvalidPreload() // Initially check if the selected date is valid
    ->altInputClass('sample-class') // Add a css class for the alt input format
    ->animate() // Animate transitions in the datepicker.
    ->format('Y-m-d') // Set the main date format
    ->ariaDateFormat('Y-m-d') // Aria
    ->clickOpens(true) // Open the datepicker when the input is clicked.
    ->closeOnSelect(true) // Close the datepicker once the date is selected.
    ->conjunction(',') // Applicable only for the MultiDatePicker: Separate inputs using this conjunction. The package will use this conjunction to explode the inputs to an array.
    ->inline(true) // Display the datepicker inline with the input, instead of using a popover.
    ->disableMobile(true) // Disable mobile-version of the datepicker on mobile devices.
    ->mode(\Coolsam\FilamentFlatpickr\Enums\FlatpickrMode::RANGE) // Set the mode as single, range or multiple. Alternatively, you can just use ->range() or ->multiple()
    ->monthSelectorType(\Coolsam\FilamentFlatpickr\Enums\FlatpickrMonthSelectorType::DROPDOWN)
    ->shorthandCurrentMonth(true)
    ->noCalendar(true) // use this in conjunction with `time()` to have a timePicker
    ->position(\Coolsam\FilamentFlatpickr\Enums\FlatpickrPosition::AUTO_CENTER)
    ->showMonths(1)
    ->weekNumbers(true)
    ->use24hr(true)
    ->wrap(true)
    ->timePicker() // Configure a timepicker out of the box
    ->weekPicker() // configure a week picker out of the box
    ->monthPicker() // configure a month picker out of the box
    ->rangePicker() // configure a date range picker out of the box
    ->multiplePicker() // Configure a multiple date picker out of the box
;
```

## Examples
```php
// You can also use the component as a DateTimePicker, Range Picker, Week Picker, Month Picker, TimePicker and Multiple Date Picker
\Coolsam\Flatpickr\Forms\Components\Flatpickr::make('start_time')->timePicker(),
\Coolsam\Flatpickr\Forms\Components\Flatpickr::make('week_number')->weekPicker()->format('W Y'),
\Coolsam\Flatpickr\Forms\Components\Flatpickr::make('month')->monthPicker()->format('Y-m')->displayFormat('F Y'),
\Coolsam\Flatpickr\Forms\Components\Flatpickr::make('range')->rangePicker(),
\Coolsam\Flatpickr\Forms\Components\Flatpickr::make('occupied_slots')->multiplePicker()->format('Y-m-d')->displayFormat('F j, Y'),
```

## State Types
The package supports the following state types:
- `string` or `CarbonInterface` for DateTimePicker, DatePicker, TimePicker, WeekPicker, MonthPicker
- `array` for RangePicker, MultiplePicker (an array of date strings or CarbonInterface instances)

## Screenshots
### Single Date Picker
![image](https://github.com/user-attachments/assets/015ae745-96bd-4b5a-990a-11bba852aa14)

### MultiPicker
![image](https://github.com/user-attachments/assets/524a58b7-2b4e-44aa-a578-f6913cbb7ccd)

### Date Range Picker
![image](https://github.com/user-attachments/assets/3bcac5ad-5bfc-4a33-a320-3027c1e6a086)


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/savannabits/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Savannabits](https://github.com/savannabits)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
