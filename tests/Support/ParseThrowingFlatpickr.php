<?php

namespace Coolsam\Flatpickr\Tests\Support;

use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

final class ParseThrowingFlatpickr extends Flatpickr
{
    protected function parseToCarbon($state): ?CarbonInterface
    {
        throw new InvalidFormatException('forced for coverage');
    }
}
