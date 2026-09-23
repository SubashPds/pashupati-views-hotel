Code review — 22 September 2026

Found **12 actionable failure risks: 3 high priority and 9 medium priority**. This review covered the application controllers, models, middleware, services, helpers, JavaScript, Blade templates, migrations, seeders, tests, configuration, and deployment files. Application source was not changed. Database checks used in-memory SQLite and synthetic records; browser requests were intercepted locally, and no enquiries or email were sent to the hotel.

“Reproduced” means the behavior was observed in an isolated check. “Code evidence” identifies a concrete failure path without claiming a production incident.

1. **High — Proxy configuration in `.env` is ignored during normal web startup.** 

   Location: [bootstrap/app.php:24](</home/subash/Subash/pashupati views hotel/bootstrap/app.php:24>).

   The middleware configuration reads `env('TRUSTED_PROXIES')` while the HTTP kernel is being constructed, before Laravel loads `.env`. Config caching also skips loading that file. When a deployment supplies the setting only through `.env`, the configured proxy is never trusted: HTTPS can be reported as HTTP and all visitors can share the proxy's IP for rate limits and country detection.

   **Reproduced:** with a synthetic trusted proxy and forwarded HTTPS/client-IP headers, `/up` returned 200, but trusted proxies remained `[]`, `isSecure()` was false, and `ip()` returned the proxy address. Environment variables injected directly into the process, such as Docker Compose's `env_file`, avoid this particular startup problem.

   **Fix:** put the proxy list in Laravel configuration and resolve it in middleware after bootstrap, for example through the existing `trustedproxy.proxies` configuration fallback. Verify both cached and uncached HTTP startup.

2. **High — Failed image replacements can permanently delete the working image.**

   Locations: [app/Http/Controllers/Admin/RoomController.php:62](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/RoomController.php:62>), [app/Http/Controllers/Admin/PackageController.php:53](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/PackageController.php:53>).

   Both update methods delete the old cover and preview before storing the replacement and saving the database record. A storage failure or database exception leaves the record pointing to a deleted image. Destruction paths also delete files before deleting their database rows. Most upload paths do not check storage's false return value; the public disk has `throw => false`.

   **Reproduced:** a simulated persistence failure during a room update returned 500. The database still referenced `rooms/original.png`, but that file had already been deleted.

   **Fix:** store and verify the replacement first, persist the change, then delete old files after commit. Clean up newly uploaded files if persistence fails. Apply the same ordering to deletion paths. The restaurant controller already demonstrates much of this pattern.

   **Remediated — 22 September 2026:** room, package, blog, hero-slide, promotion, gallery, and restaurant media now use `App\Services\MediaFiles`. Uploads are verified before persistence; failed transactions remove new originals and previews; old files are removed only after commit. Room/package cover and gallery changes commit together, and failed deletion keeps existing files. Cleanup errors are logged without discarding a committed replacement.

   **Regression verification:** `tests/Feature/MediaPersistenceTest.php` passes **49 tests, 211 assertions**, covering storage failures, persistence failures, partial uploads, rollback, preview cleanup, and deletion after commit. The full PHP suite has **153 passed, 6 failed, 1,514 assertions**; the six failures are the same pre-existing failures listed below.

3. **High — Rerunning the CMS seeder overwrites edited hotel content.**

   Locations: [database/seeders/CmsSeeder.php:70](</home/subash/Subash/pashupati views hotel/database/seeders/CmsSeeder.php:70>), [database/seeders/CmsSeeder.php:124](</home/subash/Subash/pashupati views hotel/database/seeders/CmsSeeder.php:124>), [database/seeders/DatabaseSeeder.php:16](</home/subash/Subash/pashupati views hotel/database/seeders/DatabaseSeeder.php:16>).

   The default database seeder always runs `CmsSeeder`. Its `updateOrCreate` calls replace existing settings, room content/prices, and other seeded records with demo values. This includes the enquiry notification email address. The separate package seeder also overwrites matching packages.

   **Reproduced:** an existing customized notification address was replaced by the seed value.

   **Fix:** make initial content insertion preserve existing records, and keep any intentional demo reset behind a separate explicit command. Preserve administrator edits when adding new default settings.

4. **Medium — Restaurant management returns 403 for every role, including superadmin.**

   Locations: [app/Support/Permissions.php:9](</home/subash/Subash/pashupati views hotel/app/Support/Permissions.php:9>), [app/Models/User.php:47](</home/subash/Subash/pashupati views hotel/app/Models/User.php:47>), [routes/web.php:136](</home/subash/Subash/pashupati views hotel/routes/web.php:136>).

   The restaurant routes exist, and their migration grants a `restaurant` permission, but `Permissions::MODULES` omits it. `hasPermission()` rejects unknown modules before checking the superadmin bypass. The roles editor cannot grant the missing module either.

   **Reproduced:** both GET and PUT `/admin/restaurant` returned 403 for an active superadmin. The existing route-permissions test also fails on this mapping.

   **Fix:** add the module to the permission definitions and verify read/write access for permitted roles. The public restaurant route is separately commented out; whether to enable it is a product decision.

5. **Medium — Policy status buttons fail, while JSON edits can silently discard changes.**

   Locations: [app/Http/Controllers/Admin/PolicyController.php:26](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/PolicyController.php:26>), [resources/views/admin/policies/index.blade.php:27](</home/subash/Subash/pashupati views hotel/resources/views/admin/policies/index.blade.php:27>).

   The status branch compares request values strictly against integer 0 or 1. Browser forms send strings, so a toggle falls through to title validation and fails. Conversely, a JSON edit containing integer `is_active: 1` enters the toggle branch even when title and description are supplied, because `only('is_active')` does not establish that this is a status-only request.

   **Reproduced:** submitting `is_active='0'` produced a missing-title error and left the policy active; a JSON edit redirected successfully but did not save its new title.

   **Fix:** use a dedicated validated status action, or explicitly distinguish status-only requests and normalize the boolean.

   **Remediated — 22 September 2026:** the update action recognizes a status-only request only when `is_active` is the sole field apart from `_token` and `_method`. It validates and normalizes the boolean in both update paths. Browser status buttons now work, JSON content edits retain their title and description changes, and edits that omit status preserve the current value. Only validated fields are saved.

   **Regression verification:** `tests/Feature/PolicyUpdateTest.php` passes **17 tests, 118 assertions**, covering browser toggles and edits, JSON integer/boolean statuses, invalid values, and incomplete edits. The full PHP suite has **170 passed, 6 failed, 1,632 assertions**; the six failures are the same pre-existing failures listed below.

6. **Medium — Duplicate package names cause a server error and orphan uploads.**

   Locations: [app/Http/Controllers/Admin/PackageController.php:25](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/PackageController.php:25>), [app/Models/Package.php:40](</home/subash/Subash/pashupati views hotel/app/Models/Package.php:40>), [database/migrations/2026_09_10_000002_create_packages_table.php:14](</home/subash/Subash/pashupati views hotel/database/migrations/2026_09_10_000002_create_packages_table.php:14>).

   Package creation uploads files before the model generates a slug. The database requires unique slugs, but the controller does not check normalized-name collisions. Creating “Weekend Stay!” after “Weekend Stay” therefore attempts the same slug.

   **Reproduced:** the second request returned 500, kept only one package row, and left uploaded media without an owning package.

   **Fix:** validate the normalized slug before uploads, allocate unique slugs when appropriate, and handle the database unique constraint to cover concurrent requests. Clean up files on failed creation.

   **Remediated — 22 September 2026:** package creation derives and checks the normalized slug before uploading files. Names that collide with an existing package, including inactive packages, receive a validation error on `name`, matching the room form's behavior. If a competing insert claims the slug after the check, the media transaction rolls back and cleans up new uploads and previews before returning the same validation error. Unrelated database constraint failures remain errors.

   **Regression verification:** `tests/Feature/PackageCreationTest.php` passes **12 tests, 105 assertions**, covering normalized duplicates, browser/JSON validation responses, a simulated competing insert, upload cleanup, and successful creation and edits. The full PHP suite has **182 passed, 6 failed, 1,737 assertions**; the six failures are the same pre-existing failures listed below.

7. **Medium — Clearing optional fields causes database constraint errors.**

   Locations: [app/Http/Controllers/Admin/ServiceController.php:24](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/ServiceController.php:24>), [app/Http/Controllers/Admin/PackageController.php:119](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/PackageController.php:119>), [app/Http/Controllers/Admin/FaqController.php:24](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/FaqController.php:24>), [app/Http/Controllers/Admin/GalleryController.php:54](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/GalleryController.php:54>).

   Validation permits blank values, which Laravel converts to null, but the destination columns are not nullable. A database default does not apply when a request explicitly inserts null.

   **Reproduced:** each of these ordinary form changes returned 500:

   | Form | Field cleared | Database constraint |
   | --- | --- | --- |
   | Service | Price label | `services.price_label` is not nullable |
   | Package | Minimum guests | `packages.min_guests` is not nullable |
   | FAQ | Sort order | `faqs.sort_order` is not nullable |
   | Gallery edit | Section | `gallery_items.section` is not nullable |

   **Fix:** align validation, form requirements, and schema. For optional values with defaults, normalize blanks before saving; make columns nullable only when null has a defined meaning.

   **Remediated — 22 September 2026:** cleared service price labels use `Price on request`, package minimum guests use `1`, FAQ sort orders use `0`, and gallery sections use `general`. The same correction covers optional service/package sort orders (`0`). Create and update actions normalize submitted nulls after validation, while omitted update fields preserve existing values. Nullable fields such as package maximum guests retain their existing meaning. Service sort order is now validated, and the forms explain the defaults.

   **Regression verification:** `tests/Feature/OptionalAdminFieldsTest.php` passes **24 tests, 208 assertions**, covering blank/whitespace/null inputs, omitted fields, explicit values including zero, and invalid inputs across the four forms. The full PHP suite has **206 passed, 6 failed, 1,945 assertions**; the six failures are the same pre-existing failures listed below.

8. **Medium — Validation accepts values that production MySQL cannot store.**

   Locations: [app/Http/Controllers/Admin/FaqController.php:25](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/FaqController.php:25>), [database/migrations/2026_09_19_100001_create_faqs_table.php:13](</home/subash/Subash/pashupati views hotel/database/migrations/2026_09_19_100001_create_faqs_table.php:13>), [app/Http/Controllers/Admin/PackageController.php:117](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/PackageController.php:117>), [database/migrations/2026_09_10_000002_create_packages_table.php:22](</home/subash/Subash/pashupati views hotel/database/migrations/2026_09_10_000002_create_packages_table.php:22>), [app/Http/Controllers/Admin/RoomController.php:106](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/Admin/RoomController.php:106>).

   FAQ questions accept 500 characters but use a default 255-character string column. Package guest counts have no upper validation bound but use signed tiny integers, whose maximum is 127. Room and package prices have no upper validation bound but use `DECIMAL(10,2)`, whose positive maximum is 99,999,999.99.

   **Validation reproduced on SQLite:** a 300-character FAQ question, 128 minimum guests, and a price of 100,000,000 all passed. **Production failure is inferred from the declared MySQL schema and strict-mode configuration:** these values exceed the columns' limits. SQLite does not enforce these same length/range restrictions, so the current test setup conceals the mismatch.

   **Fix:** align limits with the intended business values and schema, and add a small MySQL integration check for database-specific constraints.

   **Remediated — 23 September 2026:** FAQ questions are limited to 255 characters, package guest counts to 1–127, and room/package prices to 0–99,999,999.99 with at most two decimal places. The same limits apply to create and update requests, and the form controls now expose the matching bounds. Existing optional-field behavior is preserved.

   **Regression verification:** `tests/Feature/DatabaseValidationLimitsTest.php` passes **22 tests, 204 assertions**. `bash tests/mysql/validation-limits.sh` passed all **5 schema boundary checks on MySQL 8.0.46**, using the actual application migrations in disposable containers: boundary values saved, while overflow inserts and updates failed in strict mode. The full PHP suite has **228 passed, 6 failed, 2,149 assertions**; the six failures are the same pre-existing failures listed below.

9. **Medium — Keyboard submission closes the booking dialog and hides feedback.**

   Location: [resources/views/layouts/app.blade.php:465](</home/subash/Subash/pashupati views hotel/resources/views/layouts/app.blade.php:465>).

   The dialog click handler treats any click coordinates outside its rectangle as a backdrop click, without checking the event target. Keyboard activation of a submit button generates a click at coordinates 0,0. That closes the dialog even though the button is inside it.

   **Reproduced in Chrome:** pressing Enter on Send Enquiry changed the dialog from open to closed. The intercepted request returned a validation error, but its feedback was invisible inside the closed dialog. No JavaScript exception was raised.

   **Fix:** prevent clicks from dismissing the booking form unintentionally. Verify Enter, Space, pointer submission, and validation feedback.

   **Remediated — 23 September 2026:** removed the backdrop click handler so outside clicks and keyboard activation of controls inside the dialog keep it open. Submission progress, validation errors, success feedback, and entered values remain visible. The close button and Escape key still dismiss the dialog.

   **Regression verification:** `node tests/browser/booking-dialog.mjs` reproduced the Enter failure before the fix and passes afterward at **390px and 1440px**. Its **16 submission cases** cover Enter and Space on the submit button, implicit Enter from a field, and pointer submission with both validation and success responses. It also checks pending requests, focus on invalid fields, outside clicks preserving the open form and entered values, dismissal through the close button/Escape, and scroll restoration. All requests are intercepted locally. `npm run build` and the JavaScript test suite pass.

10. **Medium — Email delivery delays and failures are tied to the public enquiry request.**

    Locations: [app/Http/Controllers/HomeController.php:154](</home/subash/Subash/pashupati views hotel/app/Http/Controllers/HomeController.php:154>), [app/Services/EnquiryEmailNotifier.php:24](</home/subash/Subash/pashupati views hotel/app/Services/EnquiryEmailNotifier.php:24>), [config/mail.php:48](</home/subash/Subash/pashupati views hotel/config/mail.php:48>).

    **Code evidence:** after saving the enquiry, the controller sends each recipient's email synchronously. Settings allow 20 recipients and SMTP's default timeout is 10 seconds. Multiple slow recipients can keep the request occupied long enough to exceed upstream timeouts. Delivery exceptions are logged and discarded; no queued retry or delivery state is recorded. The enquiry remains saved, but a failed notification has no automatic recovery.

    **Fix:** queue delivery after saving, configure bounded retries and delivery visibility, and return the enquiry acknowledgement promptly. Include an idempotency strategy if retries of the public submission must not create duplicate enquiries.

11. **Medium — The locked dependencies require a newer PHP version than the project declares.**

    Locations: [composer.json:12](</home/subash/Subash/pashupati views hotel/composer.json:12>), [composer.lock:4600](</home/subash/Subash/pashupati views hotel/composer.lock:4600>).

    The project declares PHP `^8.2`, but several locked Symfony 8.1 packages require PHP `>=8.4.1`. A developer or deployment selecting PHP 8.2 or 8.3 from the declared requirement cannot install the lock file.

    **Reproduced:** a Composer install dry run under PHP 8.2 rejected five locked Symfony packages for this reason. `composer validate --no-check-publish` also reports that the lock file is out of date with `composer.json`.

    **Fix:** choose the supported PHP minimum, align the manifest and resolved dependencies, and validate installation on that minimum version. Update the lock deliberately rather than using platform-requirement bypasses.

12. **Medium — Fresh setup depends on a missing environment template.**

    Location: [composer.json:44](</home/subash/Subash/pashupati views hotel/composer.json:44>).

    **Code evidence:** `composer setup` and the post-install initialization path attempt to copy `.env.example`, but that file is absent. On a fresh checkout without `.env`, the copy fails and the following key-generation step cannot initialize the environment file. The existing local `.env` masks this.

    **Fix:** commit a sanitized environment template containing the application's required settings and document initial provisioning. Verify setup from a clean temporary checkout.

**Validation and test maintenance**

- PHP syntax: **130 files checked, no syntax errors**.
- JavaScript syntax: **21 files checked, no syntax errors**.
- `npm run build`: **passed**.
- `node --test tests/js/*.test.mjs`: **passed**.
- Existing PHP suite: **104 passed, 6 failed; 1,303 assertions** after loading SQLite from a temporary directory.
- Additional isolated diagnostics: **13 passed, 31 assertions**. These assertions confirm the current failure behavior and selected control cases; they are not fixes.
- An isolated HTTP-startup probe confirmed finding 1, and an intercepted Chrome check confirmed finding 9.

The six existing test failures comprise one application defect (restaurant permission mapping) and five stale or fragile assertions: two gallery-pagination assertions still expect 16 items while the controller uses 12; two upload assertions still expect the old 4 MB limit while validation allows 6,096 KB; and the superadmin reseeding test strictly compares raw attributes before and after database hydration, including boolean normalization and array order. The existing-account seeder returns without modifying that account.

The full MySQL/Docker deployment and real SMTP delivery were not exercised. Third-party vulnerability advisories were not audited. Findings describe inspected code and reproduced local behavior, not an exhaustive guarantee against future failures.

Temporary diagnostic scripts and outputs are in `/tmp/pashupati-code-review/`. Start remediation with findings 1–3, then the reproducible admin/form failures. Keep the current application tests and add regression coverage for each corrected failure.
