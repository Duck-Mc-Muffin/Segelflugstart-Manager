#!/usr/bin/env bash
# This file is a work in progress and not being used

# Read database password from docker secret file if exists
[ -f "$SEGELFLUG_DB_PASS_FILE" ] && SEGELFLUG_DB_PASS=$(< "$SEGELFLUG_DB_PASS_FILE") || echo "No password file found."

# Delete this file if this is a production environment
[ "$1" = "production" ] rm -- "$0" || echo "$0 will be kept in the working directory."