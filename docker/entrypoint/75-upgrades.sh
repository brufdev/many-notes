#!/bin/sh
[ "$SERVERSIDEUP_DEFAULT_COMMAND" = "true" ] || exit 0

php "$APP_BASE_DIR/artisan" upgrade:run
