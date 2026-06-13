#!/usr/bin/env bash

set -euo pipefail

LARAVEL="${1}"
PHP="${2}"
FILAMENT="${3}"

case "${LARAVEL}" in
    13.*)
        TESTBENCH='^11.0'
        CARBON='^3.8.6'
        PEST='^4.4.1'
        PEST_PLUGIN_LARAVEL='^4.1'
        ;;
    12.*)
        TESTBENCH='^10.0'
        CARBON='^3.8.6'
        PEST='^3.8.6'
        PEST_PLUGIN_LARAVEL='^3.1'
        ;;
    *)
        echo "Unsupported Laravel version for smoke tests: ${LARAVEL}" >&2
        exit 1
        ;;
esac

composer require \
    "laravel/framework:${LARAVEL}" \
    "filament/forms:^${FILAMENT}" \
    "filament/support:^${FILAMENT}" \
    --no-interaction --no-update

composer require --dev \
    "orchestra/testbench:${TESTBENCH}" \
    "nesbot/carbon:${CARBON}" \
    "filament/filament:^${FILAMENT}" \
    "pestphp/pest:${PEST}" \
    "pestphp/pest-plugin-laravel:${PEST_PLUGIN_LARAVEL}" \
    --no-interaction --no-update

composer update --prefer-stable --prefer-dist --no-interaction
