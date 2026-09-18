# Created by and updated by

All 15 application tables have nullable `created_by` and `updated_by` foreign keys to `users.id`: users, roles, site settings, rooms, room images, packages, package images, experiences, gallery items, services, testimonials, enquiries, hero slides, blogs, and promotions.

- Creating a record sets both fields to the authenticated user's ID.
- Updating a record preserves `created_by` and sets `updated_by` to the current actor. This includes visibility/status changes, reading a new enquiry, role permissions, and uploaded gallery records.
- Guest submissions and command-line/system writes have a null actor. Existing records remain null until a known user updates them; historical creators are never guessed.
- User IDs supplied in form data cannot override the audit fields.
- Authentication's remember-token rotation does not overwrite the account's last editor.
- Deleting a referenced user sets the audit reference to null and preserves the audited record.

Models share `TracksUserChanges` and expose `creator()` and `updater()` relationships. Site Settings and seeders use model saves so their changes also trigger tracking. Future application writes should use Eloquent model `create`, `save`, `update`, or `updateOrCreate`; raw SQL, bulk query updates, and `saveQuietly` bypass model events.

Laravel infrastructure tables (sessions, password-reset tokens, cache, queue jobs/batches, failed jobs, and migration history) are framework bookkeeping, not application records edited through the dashboard, and are unchanged.

Deploy with `php artisan migrate`. The migration adds nullable references without changing existing record data. This records the original creator and most recent editor; it is not a full change-history log.
