<?php

use Coolsam\Flatpickr\FilamentFlatpickr;

it('returns the package name', function () {
    expect(FilamentFlatpickr::getPackageName())->toBe('coolsam/flatpickr');
});

it('coerces boolean values consistently', function (mixed $value, bool $expected) {
    expect(FilamentFlatpickr::getBool($value))->toBe($expected);
})->with([
    'true boolean' => [true, true],
    'false boolean' => [false, false],
    'string true' => ['true', true],
    'string false' => ['false', false],
    'integer one' => [1, true],
    'integer zero' => [0, false],
    'string yes' => ['yes', true],
    'string no' => ['no', false],
]);

it('coerces integer values consistently', function (mixed $value, int $expected) {
    expect(FilamentFlatpickr::getInt($value))->toBe($expected);
})->with([
    'integer' => [12, 12],
    'numeric string' => ['3', 3],
    'invalid string' => ['abc', 0],
    'null' => [null, 0],
]);
