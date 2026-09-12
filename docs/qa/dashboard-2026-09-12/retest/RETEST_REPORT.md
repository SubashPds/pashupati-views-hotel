# Dashboard QA fixes — retest report

**Date:** 12 September 2026  
**Status:** All **14 confirmed findings fixed and verified** in the isolated QA environment.

## Validation results

| Validation | Result |
|---|---|
| Main functional and UX scenarios | **47 / 47 passed** |
| Follow-up scenarios | **14 / 14 passed** |
| Automated suite | **24 tests passed, 151 assertions** |
| Production asset build | Passed |
| Uncaught JavaScript errors in main browser run | None |

The follow-up set includes two new checks: completing a Gallery metadata edit without replacing its image, and verifying the sidebar focus trap, focus restoration, and desktop resize behavior. The six new automated regression tests cover the room failures, invalid uploads, package guest limits, and Gallery metadata persistence.

## Finding closure

| Finding | Fix | Verification | Status |
|---|---|---|---|
| QA-01: blank room amenities | Nullable input becomes a clean empty array on create/update | ROOM-00, automated create/update regression | Closed |
| QA-02: duplicate room slug | Validate the normalized name before storing files; return a field error | ROOM-03, duplicate-name/file-cleanup regression | Closed |
| QA-03: room gallery validation | Validate array, every image type, and 4 MB per image before mutations | ROOM-04, invalid create/update and size regressions | Closed |
| QA-04: clipped mobile/tablet actions | Focusable, labelled horizontal table regions with scroll guidance | Four mobile lists, tablet checks; actions tested after scrolling | Closed |
| QA-05: lost field attributes | Forward input attributes, bind required state, retain decimal step | FORM-01, UX-02; room prices also support decimals | Closed |
| QA-06: contradictory guest limits | Maximum must be at least minimum; blank maximum remains supported | PACKAGE-02, create/update/unlimited regression | Closed |
| QA-07: no gallery editing | Add protected edit page for caption, badge, section, and order | GALLERY-02, GALLERY-04, persistence regression | Closed |
| QA-08: settings input lost | Preserve submitted text, textarea, rich-text, and checkbox values; retain tab/select error tab | SETTINGS-02 | Closed |
| QA-09: mobile sidebar keyboard behavior | Inert closed sidebar, expanded state, focus trap, Escape, focus return, breakpoint reset | UX-04, UX-05, UX-09, UX-10 | Closed |
| QA-10: keyboard-inaccessible uploads | Focusable visually hidden file inputs and visible wrapper focus rings | UX-03; existing preview checks | Closed |
| QA-11: missing associated labels | Stable control/label IDs, enhanced-editor label, Gallery section label | UX-07; zero unlabelled settings controls found | Closed |
| QA-12: hidden mobile save feedback | Visible content-area success status at all sizes | UX-06 | Closed |
| QA-13: active section lost | Match sidebar route families through create/edit pages | UX-01 | Closed |
| QA-14: faint upload hints | Increase hint contrast | UX-08: approximately **7.09:1**, previously 2.44:1 | Closed |

## Practical behavior changes

- A duplicate room name now produces a clear validation message. Files are not stored for that rejected request.
- A room gallery accepts JPG/JPEG, PNG, and WebP images, each up to 4 MB. A bad file rejects the entire request before room/image changes.
- On small screens, table columns can be scrolled horizontally; Edit/View/Status/Delete are reachable. Keyboard users can focus each table region.
- Gallery → Edit updates metadata while retaining the existing photo/video. Its sort order is validated.
- Form errors retain the user's attempted values. A failed package guest-range submission correctly displays those attempted values while the saved record retains its previous valid range.

## Evidence

- [47-scenario browser ledger](browser-results.json)
- [14-scenario follow-up ledger and contrast metrics](followup-results.json)
- [Automated test summary](automated-tests.txt)
- [Build output](build-output.txt)
- [Mobile room actions after scrolling](screenshots/mobile-rooms.png)
- [Mobile save confirmation](screenshots/mobile-save-feedback.png)
- [768px room table](screenshots/tablet-rooms-768.png)
- [Original QA report and findings](../QA_REPORT.md)

The original QA screenshots and failures are retained separately for comparison. Retest scripts were adjusted where the fix intentionally changed behavior: table checks scroll the new regions and verify that an action can be reached; rejected uploads expect validation errors; package checks distinguish unsaved old form input from persisted data. These are behavior checks, not removal of failing assertions.

## Environment and scope

Retested in Chrome on desktop, mobile, and tablet viewports, using read-only source mounts and a fresh synthetic SQLite database, sessions, and upload directory. The existing test bootstrap's in-memory-database guard remained enabled. No live hotel data was changed and no database migration is required for these fixes.

These results close the 14 confirmed findings. The original report's optional product enhancements remain suggestions. This is not a formal accessibility certification or an exhaustive security/browser audit. Production MySQL concurrency, physical devices, other browser engines, and live data-recovery verification remain outside the tested scope.

## Reproduce

Use the isolation setup in [the QA README](../README.md), with a new ignored runtime directory and container name such as `hotel-dashboard-qa-fixes`. Then run:

```bash
node docs/qa/dashboard-2026-09-12/retest/browser-qa.mjs
node docs/qa/dashboard-2026-09-12/retest/followup-qa.mjs
```

Run the existing suite with the same guarded SQLite/storage variables documented in that README. The synthetic fixture should be fresh, and browser scripts should run in order. Scripts write scenario results to JSON and continue after assertion failures; use the ledger rather than the process exit code to assess results. The QA container used for this retest was removed after completion.
