# Dashboard QA evidence and reproduction

Start with [QA_REPORT.md](QA_REPORT.md). JSON files contain the final scenario ledger; screenshots contain synthetic QA data only. No production fixes are included.

## Reproduce in an isolated instance

Requirements: the local `pashupativiewshotel-app:latest` Docker image with SQLite support, Node.js, Google Chrome, and Playwright. The scripts default to this workstation's Playwright installation; set `QA_PLAYWRIGHT_MODULE` to another compatible module path if needed.

Run from the repository root. These commands create a fresh ignored test directory, mount app source read-only, and expose only a loopback test port. The QA bootstrap uses an empty environment directory and explicitly selects its dedicated SQLite database, storage and sessions. It refuses unexpected database settings.

```bash
QA_RUNTIME="$PWD/storage/framework/testing/dashboard-qa-$(date +%s)"
mkdir -p "$QA_RUNTIME/storage/framework/views" "$QA_RUNTIME/storage/framework/sessions" "$QA_RUNTIME/storage/framework/cache/data" "$QA_RUNTIME/storage/logs" "$QA_RUNTIME/uploads"
cp docs/qa/dashboard-2026-09-12/fixtures/*.php "$QA_RUNTIME/"

docker run -d --name hotel-dashboard-qa-20260912 \
  -p 127.0.0.1:9011:9011 \
  --mount "type=bind,source=$PWD,target=/var/www/html,readonly" \
  --mount "type=bind,source=$QA_RUNTIME,target=/qa" \
  --entrypoint php pashupativiewshotel-app:latest \
  -S 0.0.0.0:9011 -t /var/www/html/public /qa/router.php

docker exec hotel-dashboard-qa-20260912 php /qa/setup.php
node docs/qa/dashboard-2026-09-12/browser-qa.mjs
node docs/qa/dashboard-2026-09-12/followup-qa.mjs
```

The browser scripts intentionally exercise currently failing behavior and record `FAIL` results. They continue after individual failures; their process exit status alone is not a pass/fail summary. Read the JSON results. Run them in the order above against a fresh synthetic fixture to avoid duplicate test records affecting results. Re-running overwrites the evidence files; copy the report directory first if keeping the original evidence.

The setup creates disposable QA-only credentials in its fixture. These are unrelated to the hotel administrator's account and must not be deployed or seeded into the hotel database.

## Existing suite

```bash
docker exec \
  -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e DB_URL= \
  -e LARAVEL_STORAGE_PATH=/qa/storage \
  -e VIEW_COMPILED_PATH=/qa/storage/framework/views \
  hotel-dashboard-qa-20260912 php artisan test --do-not-cache-result
```

The repository's test bootstrap contains a guard against non-memory databases. Keep that guard and the forced test environment in place.

## End the QA session

```bash
docker rm -f hotel-dashboard-qa-20260912
```

This removes only the dedicated QA container. The ignored runtime directory remains for inspection; its synthetic database, logs, sessions and uploads are not part of the report or the live site. The original test container was stopped after this report was prepared.

## Evidence interpretation

- `browser-results.json`: 47 scenarios; 31 pass, 16 fail.
- `followup-results.json`: 12 scenarios; 7 pass, 5 fail.
- Failures grouped into 14 findings in the report, with scope and severity explained there.
- Follow-up contrast values use Canvas to normalize CSS colors to sRGB before alpha composition and contrast calculation.
- Browser scripts use a real local HTTP server with CSRF/session behavior enabled. Selected form-boundary cases submit authenticated HTTP requests instead of operating the file picker or native browser validation.
- UI error screenshots show the QA application's debug errors. This is not evidence that production debug mode is exposed.
