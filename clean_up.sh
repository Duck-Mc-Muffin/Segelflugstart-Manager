#!/usr/bin/env bash
# This script cleans up unnecessary files in a production environment
rm scripts/php-custom.ini composer.json composer.lock
rm -- "$0"