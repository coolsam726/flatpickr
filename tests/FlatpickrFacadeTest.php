<?php

use Coolsam\Flatpickr\Facades\FilamentFlatpickr;

it('resolves the flatpickr helper through the facade accessor', function () {
    expect(FilamentFlatpickr::getPackageName())->toBe('coolsam/flatpickr')
        ->and(FilamentFlatpickr::getFacadeRoot())->toBeInstanceOf(Coolsam\Flatpickr\FilamentFlatpickr::class);
});
