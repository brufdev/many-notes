#!/bin/sh
script_name="generate-keys"

keys_file="$APP_BASE_DIR/storage/app/private/.keys.env"
env_file="$APP_BASE_DIR/.env"
generated=""

fail() {
    echo "❌ ERROR ($script_name): $1" >&2
    exit 1
}

# Usage: add_key NAME PREFIX OPENSSL_RAND_ARGS...
add_key() {
    name=$1
    prefix=$2
    shift 2

    grep -q "^$name=" "$keys_file" && return

    value=$(openssl rand "$@") || fail "Can't generate $name"
    echo "$name=$prefix$value" >> "$keys_file" || fail "Can't write $keys_file"
    generated="$generated $name"
}

# Generate the missing keys
[ -f "$keys_file" ] || (umask 077 && : > "$keys_file") \
    || fail "Can't create $keys_file, check that the private folder is writable"

add_key APP_KEY base64: -base64 32
add_key REVERB_APP_SECRET '' -hex 10
add_key TYPESENSE_API_KEY '' -hex 16

[ -z "$generated" ] || echo "ℹ️ NOTICE ($script_name): Generated$generated"

# Copy the keys into .env, replacing existing lines
if [ -f "$env_file" ]; then
    merged=$(awk -F= '
        NR == FNR { if ($1 != "") { order[++count] = $1; keys[$1] = $0 }; next }
        { name = $1; gsub(/[ \t]/, "", name) }
        name in keys { if (!(name in done)) print keys[name]; done[name] = 1; next }
        { print }
        END { for (i = 1; i <= count; i++) if (!(order[i] in done)) print keys[order[i]] }
    ' "$keys_file" "$env_file") || fail "Can't read $env_file"

    [ "$merged" = "$(cat "$env_file")" ] \
        || printf '%s\n' "$merged" > "$env_file" \
        || fail "Can't write $env_file"
fi
