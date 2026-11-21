#!/usr/bin/env bash
# Checks a production image: start, settings, Reverb, users, PUID remap
# Usage: .github/scripts/check-image.sh IMAGE
set -u

image=${1:?Usage: check-image.sh IMAGE}
name=many-notes-check-$$
workdir=$(mktemp -d)
failed=0

pass() { echo "PASS: $1"; }
fail() { echo "FAIL: $1"; failed=1; }

# Usage: check DESCRIPTION COMMAND...
check() {
    local description=$1
    shift
    if "$@" > /dev/null 2>&1; then pass "$description"; else fail "$description"; fi
}

cleanup() {
    docker rm -f "$name" "$name-puid" > /dev/null 2>&1
    docker volume rm "$name-private" > /dev/null 2>&1
    # The remapped folder belongs to PUID, so remove it from a container
    docker run --rm --user root --entrypoint rm -v "$workdir:/check" "$image" -rf /check/private > /dev/null 2>&1
    rm -rf "$workdir"
}
trap cleanup EXIT
# Interrupted runs clean up and exit with an error
trap 'exit 130' INT
trap 'exit 143' TERM

wait_healthy() {
    local state
    for _ in $(seq 1 90); do
        state=$(docker inspect -f '{{.State.Health.Status}} {{.State.Running}}' "$1" 2> /dev/null) || return 1
        case "$state" in
            'healthy true') return 0 ;;
            'unhealthy '* | *' false') return 1 ;;
        esac
        sleep 2
    done
    return 1
}

show_logs() {
    echo "--- last log lines of $1"
    docker logs --tail 40 "$1" 2>&1
    echo "---"
}

in_app() { docker exec -u www-data "$name" "$@"; }

# Prints the given ps fields of PID 1 and its descendants, skipping docker exec and healthcheck processes
# Usage: app_processes CONTAINER FIELD...
app_processes() {
    local container=$1 fields
    shift
    fields=$(printf ',%s=' "$@")
    docker exec -u www-data "$container" ps -eo "pid=,ppid=$fields" | awk '
        { parent[$1] = $2; pid = $1; $1 = $2 = ""; sub(/^ +/, ""); info[pid] = $0 }
        END {
            for (p in info) {
                q = p
                while (q != 1 && (q in parent) && parent[q] != 0) q = parent[q]
                if (q == 1) print info[p]
            }
        }'
}

# Default settings, named volume
if docker run -d --name "$name" -v "$name-private:/var/www/html/storage/app/private" "$image" > /dev/null \
    && wait_healthy "$name"; then
    pass "starts and turns healthy on /up"
else
    fail "starts and turns healthy on /up"
    show_logs "$name"
    exit 1
fi

settings=$(in_app php artisan tinker --execute='echo json_encode([config("app.env"), config("app.debug"), config("mail.default"), config("logging.channels.stack.channels")]);')
expected='["production",false,"log",["stderr","daily"]]'
if [ "$settings" = "$expected" ]; then
    pass "production settings: $settings"
else
    fail "production settings: expected $expected, got $settings"
fi

key=$(in_app php artisan tinker --execute='echo config("broadcasting.connections.reverb.key");')
handshake=$(in_app curl -s -o /dev/null -w '%{http_code}' -m 5 --http1.1 \
    -H 'Connection: Upgrade' -H 'Upgrade: websocket' -H 'Sec-WebSocket-Version: 13' \
    -H 'Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==' "http://127.0.0.1:8080/ws/app/$key")
if [ "$handshake" = 101 ]; then
    pass "Reverb accepts a WebSocket connection through nginx"
else
    fail "Reverb accepts a WebSocket connection through nginx (got HTTP $handshake)"
fi

others=$(app_processes "$name" user | grep -vx www-data)
if [ -z "$others" ]; then
    pass "every process runs as www-data"
else
    fail "every process runs as www-data, found: $(echo "$others" | sort -u | tr '\n' ' ')"
fi

check "no new privileges flag is set" docker exec "$name" grep -q 'NoNewPrivs:[[:space:]]*1' /proc/1/status

# PUID and PGID remap, bind mount
mkdir "$workdir/private"
if docker run -d --name "$name-puid" -e PUID=1234 -e PGID=1234 \
    -v "$workdir/private:/var/www/html/storage/app/private" "$image" > /dev/null \
    && wait_healthy "$name-puid"; then
    pass "starts with PUID=1234 and PGID=1234"
    uids=$(app_processes "$name-puid" uid gid | tr -s ' ' | sort -u | tr '\n' ' ')
    if [ "$uids" = "1234 1234 " ]; then
        pass "every process runs as 1234:1234"
    else
        fail "every process runs as 1234:1234, found: $uids"
    fi
    owner=$(stat -c '%u:%g' "$workdir/private")
    if [ "$owner" = 1234:1234 ]; then
        pass "bind-mounted folder belongs to 1234:1234"
    else
        fail "bind-mounted folder belongs to 1234:1234, found $owner"
    fi
else
    fail "starts with PUID=1234 and PGID=1234"
    show_logs "$name-puid"
fi

[ "$failed" = 0 ] || show_logs "$name"
exit "$failed"
