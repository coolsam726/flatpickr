<?php

use Coolsam\Flatpickr\Enums\FlatpickrTheme;

it('returns theme asset urls for every supported theme', function () {
    foreach (FlatpickrTheme::cases() as $theme) {
        expect($theme->getAsset())->toContain("themes/{$theme->value}.css");
    }
});

it('returns an empty asset url when theme asset generation fails', function () {
    $theme = FlatpickrTheme::DEFAULT;

    app()->instance('url', new class
    {
        public function asset(string $path): string
        {
            throw new RuntimeException('asset unavailable');
        }
    });

    expect($theme->getAsset())->toBe('');
});
