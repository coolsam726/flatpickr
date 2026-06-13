@php
    use Filament\Support\Facades\FilamentView;

    $datalistOptions = $getDatalistOptions();
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $hasTime = $hasTime();
    $id = $getId();
    $isDisabled = $isDisabled();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $maxDate = $getMaxDate();
    $minDate = $getMinDate();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
    $attrs = $getFlatpickrAttributes();

@endphp

<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
        :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <link rel="stylesheet" id="pickr-theme" type="text/css" href="{{$getThemeAsset()}}">
    <x-filament::input.wrapper
            :disabled="$isDisabled"
            :inline-prefix="$isPrefixInline"
            :inline-suffix="$isSuffixInline"
            :prefix="$prefixLabel"
            :prefix-actions="$prefixActions"
            :prefix-icon="$prefixIcon"
            :prefix-icon-color="$getPrefixIconColor()"
            :suffix="$suffixLabel"
            :suffix-actions="$suffixActions"
            :suffix-icon="$suffixIcon"
            :suffix-icon-color="$getSuffixIconColor()"
            :valid="! $errors->has($statePath)"
            :attributes="\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())"
    >
        @if ($isNative())
            <x-filament::input
                    :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                        ->merge($extraAlpineAttributes, escape: false)
                        ->merge([
                            'autofocus' => $isAutofocused(),
                            'disabled' => $isDisabled,
                            'id' => $id,
                            'inlinePrefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'inlineSuffix' => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                            'list' => $datalistOptions ? $id . '-list' : null,
                            'max' => $hasTime ? $maxDate : ($maxDate ? \Carbon\Carbon::parse($maxDate)->toDateString() : null),
                            'min' => $hasTime ? $minDate : ($minDate ? \Carbon\Carbon::parse($minDate)->toDateString() : null),
                            'placeholder' => $getPlaceholder(),
                            'readonly' => $isReadOnly(),
                            'required' => $isRequired() && (! $isConcealed()),
                            'step' => $getStep(),
                            'type' => $getType(),
                            $applyStateBindingModifiers('wire:model') => $statePath,
                            'x-data' => count($extraAlpineAttributes) ? '{}' : null,
                        ], escape: false)
                "
            />
        @else
            <div
                    wire:ignore
                    @if (FilamentView::hasSpaMode())
                        {{-- format-ignore-start --}}x-load="visible || event (ax-modal-opened)"
                    {{-- format-ignore-end --}}
                    @else
                        x-load
                    @endif
                    x-load-css="[
                    @js(\Filament\Support\Facades\FilamentAsset::getStyleHref('flatpickr-styles', \Coolsam\Flatpickr\FilamentFlatpickr::getPackageName()))
                    ]"
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('flatpickr', \Coolsam\Flatpickr\FilamentFlatpickr::getPackageName()) }}"
                    x-data="flatpickrComponent($wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }}, @js($attrs))"
                    {{
                        $attributes
                            ->merge($getExtraAttributes(), escape: false)
                            ->merge($getExtraAlpineAttributes(), escape: false)
                            ->class(['fi-fo-date-time-picker'])
                    }}
            >
                <x-filament::input
                        :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                        ->merge($extraAlpineAttributes, escape: false)
                        ->merge([
                            'autofocus' => $isAutofocused(),
                            'disabled' => $isDisabled,
                            'id' => $id,
                            'x-ref' => 'input',
                            'x-model' => 'state',
                            'inlinePrefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'inlineSuffix' => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                            'max' => $hasTime ? $maxDate : ($maxDate ? \Carbon\Carbon::parse($maxDate)->toDateString() : null),
                            'min' => $hasTime ? $minDate : ($minDate ? \Carbon\Carbon::parse($minDate)->toDateString() : null),
                            'placeholder' => $getPlaceholder(),
                            'readonly' => $isReadOnly(),
                            'required' => $isRequired() && (! $isConcealed()),
                            $applyStateBindingModifiers('wire:model') => $statePath,
                            'x-data' => count($extraAlpineAttributes) ? '{}' : null,
                        ], escape: false)
                "
                />
            </div>
        @endif
    </x-filament::input.wrapper>

    @if ($datalistOptions)
        <datalist id="{{ $id }}-list">
            @foreach ($datalistOptions as $option)
                <option value="{{ $option }}" />
            @endforeach
        </datalist>
    @endif
</x-dynamic-component>