#!/usr/bin/env bash
set -euo pipefail

project_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)"
mysql_image="${MYSQL_TEST_IMAGE:-mysql:8.0}"
php_image="${PHP_TEST_IMAGE:-pashupativiewshotel-app:latest}"

# The database has no published ports, network access, or persistent volume.
mysql_container="$(docker run --detach --network none --tmpfs /var/lib/mysql:rw \
    --env MYSQL_ROOT_PASSWORD=validation-test-password \
    --env MYSQL_DATABASE=validation_limits_test "$mysql_image")"
trap 'docker rm --force --volumes "$mysql_container" >/dev/null 2>&1 || true' EXIT

ready=false
for ((attempt = 0; attempt < 60; attempt++)); do
    if docker exec --env MYSQL_PWD=validation-test-password "$mysql_container" \
        mysqladmin --protocol=TCP --host=127.0.0.1 --user=root ping --silent >/dev/null 2>&1; then
        ready=true
        break
    fi
    sleep 1
done
if [[ "$ready" != true ]]; then
    docker logs "$mysql_container"
    exit 1
fi

# Share only the disposable server's network namespace. The source is read-only;
# application caches and logs live in this container's temporary filesystem.
docker run --rm --read-only --tmpfs /tmp:rw --network "container:$mysql_container" \
    --mount "type=bind,source=$project_root,target=/app,readonly" --workdir /app \
    --env MYSQL_LIMITS_TEST=1 --entrypoint php "$php_image" tests/mysql/validation-limits.php
