<?php

namespace Coolsam\Flatpickr\Tests\Support;

use Coolsam\Flatpickr\Forms\Components\Flatpickr;

final class StatefulFlatpickr extends Flatpickr
{
    public mixed $hydratedState = null;

    public function state(mixed $state): static
    {
        $this->hydratedState = $state;

        return $this;
    }
}
