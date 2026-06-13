#!/usr/bin/env bash

set -euo pipefail

LARAVEL="${1}"
STABILITY="${2}"

composer require "laravel/framework:${LARAVEL}" --no-interaction --no-update

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
    11.*)
        TESTBENCH='^9.0'
        CARBON='^3.8.4'
        PEST='^3.8.6'
        PEST_PLUGIN_LARAVEL='^3.1'
        ;;
    *)
        echo "Unsupported Laravel version: ${LARAVEL}" >&2
        exit 1
        ;;
esac

composer require --dev \
    "orchestra/testbench:${TESTBENCH}" \
    "nesbot/carbon:${CARBON}" \
    "pestphp/pest:${PEST}" \
    "pestphp/pest-plugin-laravel:${PEST_PLUGIN_LARAVEL}" \
    --no-interaction --no-update

composer update "--${STABILITY}" --prefer-dist --no-interaction
