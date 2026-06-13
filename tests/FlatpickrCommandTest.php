<?php

it('runs the flatpickr console command', function () {
    $this->artisan('flatpickr')
        ->expectsOutput('All done')
        ->assertSuccessful();
});

it('runs the flatpickr install command', function () {
    $this->artisan('flatpickr:install')
        ->expectsConfirmation('Do you want to overwrite the existing package assets if any?', 'no')
        ->expectsConfirmation('Do you want to overwrite the package config file if existing?', 'no')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->assertSuccessful();
});
