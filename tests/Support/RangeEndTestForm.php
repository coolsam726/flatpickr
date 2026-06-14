<?php

namespace Coolsam\Flatpickr\Tests\Support;

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\FormsComponent;
use Filament\Schemas\Schema;

final class RangeEndTestForm extends FormsComponent
{
    /** @var array<string, mixed> */
    public array $data = [];

    public bool $withRangeEnd = true;

    public bool $useDatetime = false;

    public function form(Schema $schema): Schema
    {
        $field = Flatpickr::make('starts_at')->rangePicker();

        if ($this->withRangeEnd) {
            $field->rangeEnd('ends_at');
        }

        if ($this->useDatetime) {
            $field->time(true)->format('Y-m-d H:i')->rangeSeparator(' to ');
        } else {
            $field->format('Y-m-d')->rangeSeparator(' to ');
        }

        return $schema
            ->components([$field])
            ->statePath('data');
    }

    /**
     * @return array{0: self, 1: Flatpickr}
     */
    public static function mount(array $data = [], bool $useDatetime = false, bool $withRangeEnd = true): array
    {
        $livewire = new self;
        $livewire->data = $data;
        $livewire->useDatetime = $useDatetime;
        $livewire->withRangeEnd = $withRangeEnd;

        $schema = $livewire->form(Schema::make($livewire));
        $schema->getComponents(withActions: false, withHidden: true);

        /** @var Flatpickr $field */
        $field = collect($schema->getFlatComponents(withActions: false, withHidden: true))
            ->first(fn ($component) => $component instanceof Flatpickr);

        return [$livewire, $field];
    }
}
