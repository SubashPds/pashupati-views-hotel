Run the MySQL-specific schema checks from the project root:

```sh
bash tests/mysql/validation-limits.sh
```

The runner needs Docker, the `mysql:8.0` image, and the application image built by
`docker compose build app` (default: `pashupativiewshotel-app:latest`). Set
`MYSQL_TEST_IMAGE` or `PHP_TEST_IMAGE` to use another compatible image.

It starts an isolated MySQL server with no published ports or persistent data,
applies the actual application migrations to an empty test database, and checks
the FAQ length, package guest counts, and room/package price limits in strict
mode. Each check verifies an accepted boundary and rejected overflow inserts and
updates. The runner removes its database container on exit.

The repository is mounted read-only; the test uses explicit connection settings
and skips the site's `.env` and cached configuration. The regular PHPUnit suite
continues to use in-memory SQLite and tests HTTP validation, including decimal
precision, optional values, and failure before media uploads.
