#!/bin/sh
[ "$SERVERSIDEUP_DEFAULT_COMMAND" = "true" ] || exit 0

script_name="dev-setup"

fail() {
    echo "🛑 ERROR ($script_name): $1" >&2
    exit 1
}

warn() {
    echo "⚠️ WARNING ($script_name): $1" >&2
}

notice() {
    echo "ℹ️ NOTICE ($script_name): $1"
}

# Usage: install_if_changed LOCK_FILE STAMP_FILE COMMAND...
install_if_changed() {
    lock=$1
    stamp=$2
    shift 2

    sum=$(sha256sum "$lock") || fail "Can't read $lock"
    sum=${sum%% *}
    [ "$(cat "$stamp" 2> /dev/null)" = "$sum" ] && return

    notice "Running $*"
    "$@" || fail "$* failed"
    echo "$sum" > "$stamp" || fail "Can't write $stamp"
}

cd "$APP_BASE_DIR" || fail "Can't open $APP_BASE_DIR"

if [ ! -f .env ]; then
    cp .env.example .env || fail "Can't create .env"
    notice "Created .env"
fi

# Install dependencies when missing or when the lock file changed
install_if_changed composer.lock vendor/.composer-lock.sha256 composer install --no-interaction
install_if_changed package-lock.json node_modules/.package-lock.sha256 npm ci --no-audit --no-fund

mkdir -p database/sqlite || fail "Can't create database/sqlite"
php artisan migrate --force || warn "Migrations failed, run them with docker compose exec app php artisan migrate"
